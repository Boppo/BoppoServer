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
?>