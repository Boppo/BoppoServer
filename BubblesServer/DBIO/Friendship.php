<?php

/* FUNCTION:    getFriendshipStatus
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: Retrieves the label that represents the current status between
 *              User 1 and User 2.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getFriendshipStatus($uid_1, $uid_2)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT uid_1, uid_2, friendship_status_type_label
			  FROM R_FRIENDSHIP_STATUS, T_FRIENDSHIP_STATUS_TYPE 
			  WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND 
  				R_FRIENDSHIP_STATUS.friendship_status_type_code = 
				T_FRIENDSHIP_STATUS_TYPE.friendship_status_type_code";
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid_1, $uid_2, $uid_1, $uid_2);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE FRIENDSHIP STATUS TYPE LABEL
	$returned_uid_1 = -1;
	$returned_uid_2 = -1;
	$friendship_status_type_label = "";
	$statement->bind_result($returned_uid_1, $returned_uid_2, $friendship_status_type_label);
	$statement->fetch();
	$statement->close();
	
	// IF FRIENDSHIP STATUS TYPE LABEL IS A SENT FRIEND REQUEST, IDENTIFY WHO SENT IT
	if ($uid_1 === $returned_uid_1 && $uid_2 === $returned_uid_2)
	{
		if ($friendship_status_type_label === "Request Sent")
			$friendship_status_type_label = "Request sent by this user.";
		if ($friendship_status_type_label === "Blocked")
			$friendship_status_type_label = "This user already blocked the other user.";
	}
	if ($uid_1 === $returned_uid_2 && $uid_2 === $returned_uid_1)
	{
		if ($friendship_status_type_label === "Request Sent")
			$friendship_status_type_label = "Request sent to this user.";
		if ($friendship_status_type_label === "Blocked")
			$friendship_status_type_label = "This user is already blocked by the other user.";
	}
	
	// RETURN THE FRIENDSHIP STATUS TYPE LABEL
	return $friendship_status_type_label;
}

function isFriend($uid_1, $uid_2)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	$query = "SELECT 1
			  FROM R_FRIENDSHIP_STATUS
			  WHERE friendship_status_type_code = (
  				SELECT friendship_status_type_code 
  				FROM T_FRIENDSHIP_STATUS_TYPE 
  				WHERE friendship_status_type_label = 'Friends')
  				AND (
    			  (uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? and uid_1 = ?)
  				)";
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid_1, $uid_2, $uid_1, $uid_2);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	
	// ADD USER TO EVENT IF USER HAS NOT YET BEEN ADDED
	if ($statement->num_rows === 0) 
		return "true";
	else if ($statement->num_rows > 0)
		return "false";
	
	$statement->close();
}
?>