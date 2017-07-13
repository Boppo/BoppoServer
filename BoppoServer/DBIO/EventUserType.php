<?php

/* FUNCTION: fetchEventUserTypeCode
 * DESCRIPTION: Retrieves and returns the code representing a type of an event user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchEventUserTypeCode($event_user_type_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	
	// ACQUIRE THE INVITE TYPE CODE
	$query = "SELECT event_user_type_code 
			  FROM T_EVENT_USER_TYPE
			  WHERE event_user_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $event_user_type_label);
	$statement->execute();
	$statement->error;
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE INVITE TYPE CODE
	$invite_type_code = -1;
	$statement->bind_result($event_user_type_code);
	$statement->fetch();
	$statement->close();
	
	// RETURN THE INVITE TYPE CODE
	return $event_user_type_code;
}



/* FUNCTION: fetchEventUserTypeLabel
 * DESCRIPTION: Retrieves and returns the label representing a type of an event user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchEventUserTypeLabel($event_user_type_code)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

	// ACQUIRE THE INVITE TYPE LABEL
	$query = "SELECT event_user_type_label
			  FROM R_EVENT_USER_TYPE
			  WHERE event_user_type_code = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $event_user_type_code);
	$statement->execute();
	$statement->error;

	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE INVITE TYPE LABEL
	$invite_type_label = "";
	$statement->bind_result($event_user_type_label);
	$statement->fetch();
	$statement->close();

	// RETURN THE INVITE TYPE LABEL
	return $event_user_type_label;
}
?>