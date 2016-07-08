<?php

/* FUNCTION:    dbSetObjectLikeOrDislike
 * DESCRIPTION: Sets the specified object in the specified table to be liked or 
 *              disliked (as specified) by the specified user.
 *              In other words, this may set an Event with ID ## to be liked or 
 *              disliked by the user with ID #####.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetObjectLikeOrDislike($uid, $object_type_code, $oid, $user_like_indicator)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "CALL sp_setObjectLikeOrDislike(?, ?, ?, ?)"; 
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid, $object_type_code, $oid, $user_like_indicator);
	$statement->execute();
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { return "DB ERROR: " . $error; }

	return "User has successfully liked or disliked the object.";

	$statement->close();
}

?>