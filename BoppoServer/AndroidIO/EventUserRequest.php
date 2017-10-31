<?php

$function = $_GET['function'];

if ($function == "addUserToEvent")
	addUserToEvent();
if ($function == "getEventUserData")
	getEventUserData();
if ($function == "getEventUsersData")
	getEventUsersData();
if ($function == "setEventUser")
    setEventUser();

/* FUNCTION: addUserToEvent
 * DESCRIPTION: Adds a user to an event in the corresponding database table.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function addUserToEvent()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	
	// IMPORT REQUIRED FUNCTIONS
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';
	
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$eid = $json_decoded["eid"];
	$inviter_uid = $json_decoded["inviterUid"];
	$invitee_uid = $json_decoded["inviteeUid"];
	
	/** ----> FETCH ALL OF THE REQUIRED DATA FIRST <---- **/
	
	// FETCH THE DATA ABOUT THE EVENT
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Event.php';
	$event = dbGetEventData($eid);
	// FETCH THE DATA ABOUT THE INVITER EVENT USER
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUser.php';
	$inviter_user = dbGetEventUserData($eid, $inviter_uid);
	// FETCH THE DATA ABOUT THE INVITEE EVENT USER
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUser.php';
	$invitee_user = dbGetEventUserData($eid, $invitee_uid);
	// FETCH THE DATA THAT IS THE CODE REPRESENTING THE INVITER EVENT USER TYPE
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUserType.php';
	$inviter_event_user_type_code = fetchEventUserTypeCode($inviter_user["eventUserTypeLabel"]);
	// FETCH THE DATA REPRESENTING WHETHER THE INVITEE IS A FRIEND OF THE INVITER
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/FriendshipStatus.php';
	$isFriend = isFriend($inviter_uid, $invitee_uid);
	// FETCH THE DATA REPRESENTING THE MINIMUM USER TYPE CODE THAT CAN REINVITE A USER TO AN EVENT
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_user_reinvite_user_type_code = $array_gv["Permission"]["EventUserReinviteUserTypeCode"];
	// FETCH THE DATA REPRESENTING THE REINVITE WAIT DURATION BEFORE THE USER THAT ONCE LEFT MAY BE REINVITED
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_user_reinvite_wait_duration_unit 
		= $array_gv["Duration"]["EventUserReinviteWaitDuration"]["DatetimeUnit"];
	$event_user_reinvite_wait_duration_value  
		= $array_gv["Duration"]["EventUserReinviteWaitDuration"]["DatetimeValue"];
	
	/** <---- HANDLE ALL THE SCENARIOS WHERE AN ADD USER TO EVENT SHOULD BE REJECTED ----> **/
		
	if (strcmp($event_user_reinvite_wait_duration_unit, "Day") !== 0)
	{
		//echo $event_user_reinvite_wait_duration_unit . "<br>";
		//var_dump($event_user_reinvite_wait_duration_unit);
		echo formatResponseError("The event user reinvite wait duration unit is set incorrectly. 
		    Please contact the server administrator regarding this issue.");
		return;
	}
		
	if (!is_array($inviter_user)) 
	{
		if (strcmp($inviter_user, "User was and is not a member of the event.") === 0) 
		{
			echo json_encode(formatResponseError("The inviter is not a member of the event."));
			return; 
		}
		else 
		{
			echo json_encode(formatResponseError("The inviter is not a member of the event."));
			return;
		}
	}
	else if (strcmp($inviter_user["eventUserInviteStatusTypeLabel"], "Joined") !== 0)
	{
		echo json_encode(formatResponseError("The inviter is not a member of the event."));
		return;
	}
	else if (!is_array($invitee_user))
	{
		if (strcmp($invitee_user, "User was and is not a member of the event.") === 0)
		{
			// EXECUTE A QUERY TO CALL A STORED PROCEDURE TO ADD THE USER TO THE EVENT
			$query = "CALL sp_addUserToEvent(?, ?)";
			$statement = $conn->prepare($query);
			$statement->bind_param("ii", $eid, $invitee_uid);
			$statement->execute();
			
			// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
			$error = $statement->error;
			if ($error != "") { echo json_encode(formatResponseError("DB ERROR: " . $error)); return; }
			
			// IF THE USER WAS SUCCESSFULLY ADDED TO THE EVENT, SUBSCRIBE TO THE TOPIC IN FIREBASE AND RETURN RESPONSES
			$responses = array();
			array_push($responses, formatResponseSuccess("The user has been successfully invited to the event."));
			$deviceFrids = dbGetUserDeviceFrids($invitee_uid);
			if (contains(json_encode($deviceFrids), "responseType"))
			{
			  array_push($responses, $deviceFrids);
			}
			else 
			{
              array_push($responses, $deviceFrids);
              $subscribeDevicesToEventResponse = subscribeDevicesToEvent($deviceFrids, $eid);
              array_push($responses, $subscribeDevicesToEventResponse);
			}
			
			echo json_encode($responses);
			return;
		}
		else
		{
			echo json_encode(formatResponseError("Could not add user to event for unknown reason 1."));
			return;
		}
	}
	else 
	{
		if (strcmp($invitee_user["eventUserInviteStatusTypeLabel"], "Joined") === 0)
		{
			echo json_encode(formatResponseError("User is already a member of the event."));
			return;
		}
		else if (!$isFriend)
		{
			echo json_encode(formatResponseError("User is not a friend of the inviter; only friends may be invited."));
			return;
		}
		else if (strcmp($invitee_user["eventUserInviteStatusTypeLabel"], "Left") === 0)
		{
			$date_recorded = new DateTime($invitee_user["eventUserInviteStatusUpsertTimestamp"]);
			$date_current  = new DateTime(date('Y-m-d H:i:s'));
			$date_diff     = date_diff($date_recorded, $date_current)->format('%a');
			if (intval($date_diff) >= $event_user_reinvite_wait_duration_value)
			{
				// EXECUTE A QUERY TO CALL A STORED PROCEDURE TO ADD THE USER TO THE EVENT
				$query = "CALL sp_addUserToEvent(?, ?)";
				$statement = $conn->prepare($query);
				$statement->bind_param("ii", $eid, $invitee_uid);
				$statement->execute();
				
				// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
				$error = $statement->error;
				if ($error != "") { echo "DB ERROR: " . $error; return; }
				
				// IF THE USER WAS SUCCESSFULLY ADDED TO THE EVENT, SUBSCRIBE TO THE TOPIC IN FIREBASE AND RETURN RESPONSES
				$responses = array();
				array_push($responses, formatResponseSuccess("The user has been successfully invited to the event."));
				$deviceFrids = dbGetUserDeviceFrids($invitee_uid);
				if (contains(json_encode($deviceFrids), "responseType"))
				{
				  array_push($responses, $deviceFrids);
				}
				else
				{
				  array_push($responses, $deviceFrids);
				  $subscribeDevicesToEventResponse = subscribeDevicesToEvent($deviceFrids, $eid);
				  array_push($responses, $subscribeDevicesToEventResponse);
				}
				
				echo json_encode($responses);
				return;
			}
			else
			{
				echo formatResponseError("User recently left the event. " . 
				    ($event_user_reinvite_wait_duration_value - $date_diff) . " more " . 
				    strtolower($event_user_reinvite_wait_duration_unit) . "s must pass.");
				return; 
			}
		}
		else if (strcmp($invitee_user["eventUserInviteStatusTypeLabel"], "Removed") === 0)
		{
			if ($inviter_event_user_type_code <= $event_user_reinvite_user_type_code)
			{
				// EXECUTE A QUERY TO CALL A STORED PROCEDURE TO ADD THE USER TO THE EVENT
				$query = "CALL sp_addUserToEvent(?, ?)";
				$statement = $conn->prepare($query);
				$statement->bind_param("ii", $eid, $invitee_uid);
				$statement->execute();
				
				// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
				$error = $statement->error;
				if ($error != "") { echo "DB ERROR: " . $error; return; }
				
				// IF THE USER WAS SUCCESSFULLY ADDED TO THE EVENT, SUBSCRIBE TO THE TOPIC IN FIREBASE AND RETURN RESPONSES
				$responses = array();
				array_push($responses, formatResponseSuccess("The user has been successfully invited to the event."));
				$deviceFrids = dbGetUserDeviceFrids($invitee_uid);
				if (contains(json_encode($deviceFrids), "responseType"))
				{
				  array_push($responses, $deviceFrids);
				}
				else
				{
				  array_push($responses, $deviceFrids);
				  $subscribeDevicesToEventResponse = subscribeDevicesToEvent($deviceFrids, $eid);
				  array_push($responses, $subscribeDevicesToEventResponse);
				}
				
				echo json_encode($responses);
				return;
			}
			else 
			{
				echo json_encode(formatResponseError("The inviter must be of higher importance to reinvite the invitee to the event."));
				return;
			}
		}
	}
	
	return;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    getEventUserData
 * DESCRIPTION: Gets the data of the relationship between the event and the user
 * 				for the specified eid (event Identifier) and uid (User Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getEventUserData()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $eid = $json_decoded["eid"];
  $uid = $json_decoded["uid"];

  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUser.php';
  $user = dbGetEventUserData($eid, $uid);

  // RETURN THE USERS AND THEIR USER AND EVENT USER DATA
  echo json_encode($user);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getEventUsersData
 * DESCRIPTION: Gets the event user and user data for all of the users that are
 *              a part of the specified event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getEventUsersData()
{
	// THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	// END. //

	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$eid = $json_decoded["eid"];
	$event_user_invite_status_type_label = $json_decoded["eventUserInviteStatusTypeLabel"];

	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUser.php';
	$users = dbGetEventUsersData($eid, $event_user_invite_status_type_label);

	// RETURN THE USERS AND THEIR USER AND EVENT USER DATA
	echo json_encode($users);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    setEventUser
 * DESCRIPTION: Updates the input Event User data for the input User of the input
 *              Event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function setEventUser()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //
  
  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';

  // ESTABLISH DATABASE CONNECTION //
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // DECODE INCOMING JSON CONTENTS //
  $json_decoded = json_decode(file_get_contents("php://input"), true);

  $eid = $json_decoded["eid"];
  $uid = $json_decoded["uid"];
  $event_user_type_label = $json_decoded["eventUserTypeLabel"];
  $event_user_invite_status_type_label = $json_decoded["eventUserInviteStatusTypeLabel"];
  $set_or_not = $json_decoded["setOrNot"];
  
  $responses = array();

  // MAKE SURE THAT VALID IDENTIFIER(S) WERE PROVIDED
  if ($eid <= 0) {
    array_push($responses, json_encode(formatResponseError("Incorrect event user identifier specified.")));
    echo json_encode($responses); 
    return; }
  if ($uid <= 0) {
    array_push($responses, json_encode(formatResponseError("Incorrect user identifier specified.")));
    echo json_encode($responses); 
    return; }
  
  // ENCODE THE EVENT USER TYPE LABEL
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUserType.php';
  $event_user_type_code = fetchEventUserTypeCode($event_user_type_label);
  $set_or_not["eventUserTypeCode"] = $set_or_not["eventUserTypeLabel"];
  unset($set_or_not["eventUserTypeLabel"]);
  if ($json_decoded["eventUserTypeLabel"] != null && $event_user_type_code == null) {
    array_push($responses, json_encode(formatResponseError("Incorrect event user type label specified.")));
    echo json_encode($responses); 
    return; }

  // ENCODE THE EVENT USER INVITE STATUS TYPE LABEL
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ReferenceData.php';
  $event_user_invite_status_type_code = 
    dbGetEventUserInviteStatusTypeCode($event_user_invite_status_type_label);
  $set_or_not["eventUserInviteStatusTypeCode"] = $set_or_not["eventUserInviteStatusTypeLabel"];
  unset($set_or_not["eventUserInviteStatusTypeLabel"]);
  if ($json_decoded["eventUserInviteStatusTypeLabel"] != null && $event_user_invite_status_type_code == null) {
    array_push($responses, json_encode(formatResponseError("Incorrect event user invite status type label specified.")));
    echo json_encode($responses); 
    return; }

  // SEND THE NEW VALUES IN AN EVENT OBJECT TO THE CORRESPONDING DBIO METHOD
  $eventUser = array
  (
      "eid" => $eid,
      "uid" => $uid,
      "eventUserTypeCode" => $event_user_type_code,
      "eventUserInviteStatusTypeCode" => $event_user_invite_status_type_code
  );
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUser.php';
  $setEventUserResponse = dbSetEventUser($eventUser, $set_or_not);
  array_push($responses, $setEventUserResponse);
  
  // IF THE USER WAS SUCCESSFULLY ADDED TO THE EVENT, SUBSCRIBE TO THE TOPIC IN FIREBASE AND RETURN RESPONSES
  if (contains(json_encode($setEventUserResponse), "responseType") && contains(json_encode($setEventUserResponse), "Success")) 
  {
    $deviceFrids = dbGetUserDeviceFrids($uid); 
    array_push($responses, $deviceFrids);
    
    if ($event_user_invite_status_type_label == "Joined" && !contains(json_encode($setEventUserResponse), "responseType")) 
    {
      $subscribeDevicesToEventResponse = subscribeDevicesToEvent($deviceFrids, $eid);
      array_push($responses, $subscribeDevicesToEventResponse); 
    }
  }
  echo json_encode($responses);
  return;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>