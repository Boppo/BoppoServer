<?php

function fetchEventUserData($eid, $uid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT eid, uid, event_user_type_label, event_user_invite_status_type_label, 
			  		 event_user_invite_status_action_timestamp 
			  FROM   T_EVENT_USER
  			  		 LEFT JOIN T_EVENT_USER_TYPE ON T_EVENT_USER.event_user_type_code = 
					   T_EVENT_USER_TYPE.event_user_type_code
  					 LEFT JOIN T_EVENT_USER_INVITE_STATUS_TYPE ON T_EVENT_USER.event_user_invite_status_type_code = 
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
		$event_user_invite_status_type_label, $event_user_invite_status_action_timestamp);
	$statement->fetch();

	$eventUser = array
	(
		"eid" => $eid,
		"uid" => $uid,
		"eventUserTypeLabel" => $event_user_type_label,
		"eventUserInviteStatusTypeLabel" => $event_user_invite_status_type_label,
		"eventUserInviteStatusActionTimestamp" => $event_user_invite_status_action_timestamp
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

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT 
				  T_USER.uid, facebook_uid, googlep_uid, username, NULL, first_name, last_name, 
				  email, privacy_label, user_account_creation_timestamp, user_comment_count, 
				  event_user_type_label, event_user_invite_status_type_label, 
				  event_user_invite_status_action_timestamp
			  FROM 
				  T_USER
				  LEFT JOIN T_PRIVACY ON T_USER.user_account_privacy_code = T_PRIVACY.privacy_code 
				  LEFT JOIN T_EVENT_USER ON T_USER.uid = T_EVENT_USER.uid 
				  LEFT JOIN T_EVENT_USER_TYPE ON 
				    T_EVENT_USER.event_user_type_code = T_EVENT_USER_TYPE.event_user_type_code 
				  LEFT JOIN T_EVENT_USER_INVITE_STATUS_TYPE ON 
				    T_EVENT_USER.event_user_invite_status_type_code = 
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
		$first_name, $last_name, $email, $user_account_privacy_label, $user_account_creation_timestamp, 
		$user_comment_count, $event_user_type_label, $event_user_invite_status_type_label, 
		$event_user_invite_status_action_timestamp);

	$userList = array();

	while($statement->fetch())
	{
		$eventUser = array(
			"eventUserTypeLabel" => $event_user_type_label, 
			"eventUserInviteStatusTypeLabel" => $event_user_invite_status_type_label,
			"eventUserInviteStatusActionTimestamp" => $event_user_invite_status_action_timestamp
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
			"userAccountPrivacyLabel" => $user_account_privacy_label, 
			"userAccountCreationTimestamp" => $user_account_creation_timestamp, 
			"userCommentCount" => $user_comment_count, 
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
?>