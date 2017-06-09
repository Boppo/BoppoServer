<?php

/* FUNCTION:    dbGetEventUserDataEncoded
 * DESCRIPTION: Gets the Event User data for the input user of the input event, 
 *              keeping the reference data encoded. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventUserEncoded($eid, $uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT eid, uid, event_user_type_code, event_user_invite_status_type_code,
			  	   event_user_invite_status_upsert_timestamp
			FROM   R_EVENT_USER
			WHERE  eid = ? AND uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $eid, $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // RETURN MESSAGE INSTEAD IF NO ROW EXISTS (I.E. USER WAS NEVER A MEMBER OF EVENT)
  if ($statement->num_rows === 0) { return "User was and is not a member of the event."; }

  // ASSIGN THE EVENT VARIABLES
  $statement->bind_result($eid, $uid, $event_user_type_code,
      $event_user_invite_status_type_code, $event_user_invite_status_upsert_timestamp);
  $statement->fetch();

  $eventUser = array
  (
      "eid" => $eid,
      "uid" => $uid,
      "eventUserTypeCode" => $event_user_type_code,
      "eventUserInviteStatusTypeCode" => $event_user_invite_status_type_code,
      "eventUserInviteStatusUpsertTimestamp" => $event_user_invite_status_upsert_timestamp
  );

  $statement->close();

  return $eventUser;
}



/* FUNCTION:    dbGetEventUserData
 * DESCRIPTION: Gets the data of the relationship between the event and the user
 * 				for the specified eid (event Identifier) and uid (User Identifier).

 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventUserData($eid, $uid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT eid, uid, event_user_type_label, event_user_invite_status_type_label, 
			  		 event_user_invite_status_upsert_timestamp 
			  FROM   R_EVENT_USER
  			  		 LEFT JOIN T_EVENT_USER_TYPE ON R_EVENT_USER.event_user_type_code = 
					   T_EVENT_USER_TYPE.event_user_type_code
  					 LEFT JOIN T_EVENT_USER_INVITE_STATUS_TYPE ON R_EVENT_USER.event_user_invite_status_type_code = 
					   T_EVENT_USER_INVITE_STATUS_TYPE.event_user_invite_status_type_code
			  WHERE  eid = ? AND uid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("ii", $eid, $uid);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// RETURN MESSAGE INSTEAD IF NO ROW EXISTS (I.E. USER WAS NEVER A MEMBER OF EVENT)
	if ($statement->num_rows === 0) { return "User was and is not a member of the event."; }

	// ASSIGN THE EVENT VARIABLES
	$statement->bind_result($eid, $uid, $event_user_type_label, 
		$event_user_invite_status_type_label, $event_user_invite_status_upsert_timestamp);
	$statement->fetch();

	$eventUser = array
	(
		"eid" => $eid,
		"uid" => $uid,
		"eventUserTypeLabel" => $event_user_type_label,
		"eventUserInviteStatusTypeLabel" => $event_user_invite_status_type_label,
		"eventUserInviteStatusUpsertTimestamp" => $event_user_invite_status_upsert_timestamp
	);

	$statement->close();

	return $eventUser;
}



/* FUNCTION: dbGetEventUsersData
 * DESCRIPTION: Gets the event user and user data for all of the users that are
 *              a part of the specified event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventUsersData($eid, $event_user_invite_status_type_label)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// EXECUTE THE QUERY
	$query = "SELECT 
				  T_USER.uid, facebook_uid, googlep_uid, username, NULL, first_name, last_name, 
				  email, privacy_label, user_insert_timestamp, user_comment_count, 
				  event_user_type_label, event_user_invite_status_type_label, 
				  event_user_invite_status_upsert_timestamp
			  FROM 
				  T_USER
				  LEFT JOIN T_PRIVACY ON T_USER.user_privacy_code = T_PRIVACY.privacy_code 
				  LEFT JOIN R_EVENT_USER ON T_USER.uid = R_EVENT_USER.uid 
				  LEFT JOIN T_EVENT_USER_TYPE ON 
				    R_EVENT_USER.event_user_type_code = T_EVENT_USER_TYPE.event_user_type_code 
				  LEFT JOIN T_EVENT_USER_INVITE_STATUS_TYPE ON 
				    R_EVENT_USER.event_user_invite_status_type_code = 
				    T_EVENT_USER_INVITE_STATUS_TYPE.event_user_invite_status_type_code
			  WHERE 
				  event_user_invite_status_type_label = ? AND eid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("si", $event_user_invite_status_type_label, $eid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// ASSIGN THE EVENT VARIABLES
	$statement->bind_result($uid, $facebook_uid, $googlep_uid, $username, $password, 
		$first_name, $last_name, $email, $user_privacy_label, $user_insert_timestamp, 
		$user_comment_count, $event_user_type_label, $event_user_invite_status_type_label, 
		$event_user_invite_status_upsert_timestamp);

	$userList = array();

	while($statement->fetch())
	{
	  $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
	   
      $eventUser = array(
        "eventUserTypeLabel" => $event_user_type_label, 
        "eventUserInviteStatusTypeLabel" => $event_user_invite_status_type_label,
        "eventUserInviteStatusUpsertTimestamp" => $event_user_invite_status_upsert_timestamp
      );
      $user = array
      (
        "uid" => $uid, 
        "facebookUid" => $facebook_uid, 
        "googlepUid" => $googlep_uid, 
        "username" => $username, 
        "password" => $password, 
        "firstName" => $first_name, 
        "lastName" => $last_name, 
        "email" => $email, 
        "userPrivacyLabel" => $user_privacy_label, 
        "userInsertTimestamp" => $user_insert_timestamp, 
        "userCommentCount" => $user_comment_count, 
        "userProfileImages" => $user_profile_images, 
        "eventUserData" => $eventUser        
      );
      $userData = array(
        "user" => $user
      );
      array_push($userList, $userData);
	}

	$statement->close();

	return $userList;
}



/* FUNCTION:    dbSetEventUser
 * DESCRIPTION: Updates the input Event User data for the input User of the input
 *              Event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetEventUser($eventUser, $set_or_not)
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // FETCH THE CURRENT VALUES FOR THIS EVENT
  $eventUserCurrent = dbGetEventUserEncoded($eventUser["eid"], $eventUser["uid"]);

  if ($set_or_not["eventUserTypeCode"] === true)
    $eventUserCurrent["eventUserTypeCode"] = $eventUser["eventUserTypeCode"];
  if ($set_or_not["eventUserInviteStatusTypeCode"] === true)
    $eventUserCurrent["eventUserInviteStatusTypeCode"] = $eventUser["eventUserInviteStatusTypeCode"];

  // EXECUTE THE QUERY
  $query = "UPDATE R_EVENT_USER
            SET    event_user_type_code = ?,
                   event_user_invite_status_type_code = ?
            WHERE  eid = ? AND uid = ?";

  $statement = $conn->prepare($query);

  $statement->bind_param("iiii", 
      $eventUserCurrent["eventUserTypeCode"], $eventUserCurrent["eventUserInviteStatusTypeCode"],
      $eventUserCurrent["eid"], $eventUserCurrent["uid"]);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return "DB ERROR: " . $error; }

  // RETURN A SUCCESS CONFIRMATION MESSAGE
  if ($statement->affected_rows === 0)
    return "Event user has failed to update: no event user has been updated, possibly because the input data is not new.";
  if ($statement->affected_rows === 1)
    return "Event user has been successfully updated.";
  else
    return "Event user has failed to update: no event user or multiple event users have been updated.";

  $statement->close();
}
?>