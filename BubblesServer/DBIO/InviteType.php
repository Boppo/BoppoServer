<?php

/* FUNCTION: fetchInviteTypeCode
 * DESCRIPTION: Retrieves and returns the code representing a type of an invite.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchInviteTypeCode($invite_type_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// ACQUIRE THE INVITE TYPE LABEL
	$query = "SELECT invite_type_code 
			  FROM T_INVITE_TYPE
			  WHERE invite_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $invite_type_label);
	$statement->execute();
	$statement->error;
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE INVITE TYPE CODE
	$invite_type_code = null;
	$statement->bind_result($invite_type_code);
	$statement->fetch();
	$statement->close();
	
	// RETURN THE INVITE TYPE CODE
	return $invite_type_code;
}
?>