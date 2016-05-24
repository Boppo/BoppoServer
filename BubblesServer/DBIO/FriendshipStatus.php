<?php

/* FUNCTION:    getFriendshipStatus
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: Retrieves the label that represents the current status between
 *              User 1 and User 2.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetFriendshipStatus($uid_1, $uid_2)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT uid_1, uid_2, user_relationship_type_label
			  FROM R_USER_RELATIONSHIP, T_USER_RELATIONSHIP_TYPE 
			  WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND 
  				R_USER_RELATIONSHIP.user_relationship_type_code = 
				T_USER_RELATIONSHIP_TYPE.user_relationship_type_code";
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid_1, $uid_2, $uid_1, $uid_2);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE FRIENDSHIP STATUS TYPE LABEL
	$returned_uid_1 = -1;
	$returned_uid_2 = -1;
	$user_relationship_type_label = "";
	$statement->bind_result($returned_uid_1, $returned_uid_2, $user_relationship_type_label);
	$statement->fetch();
	$statement->close();
	
	// IF FRIENDSHIP STATUS TYPE LABEL IS FRIENDSHIP PENDING OR BLOCK, IDENTIFY WHO PERFORMED THE ACTION
	if ($uid_1 === $returned_uid_1 && $uid_2 === $returned_uid_2)
	{
		if ($user_relationship_type_label === "Friendship Pending")
			$user_relationship_type_label = "Friendship Pending request sent by this user.";
		if ($user_relationship_type_label === "Blocked")
			$user_relationship_type_label = "This user already blocked the other user.";
	}
	if ($uid_1 === $returned_uid_2 && $uid_2 === $returned_uid_1)
	{
		if ($user_relationship_type_label === "Friendship Pending")
			$user_relationship_type_label = "Friendship Pending request sent to this user.";
		if ($user_relationship_type_label === "Blocked")
			$user_relationship_type_label = "This user is already blocked by the other user.";
	}
	
	// IF THE TWO USERS DO NOT HAVE A RELATIONSHIP CURRENTLY
	if (strlen($user_relationship_type_label) === 0)
		$user_relationship_type_label = "Not friends.";
	
	// RETURN THE FRIENDSHIP STATUS TYPE LABEL
	return $user_relationship_type_label;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    fetchFriendshipStatusRequestSentUsers
 * NOTE:        User 1 is the user that sent the friendship status request.
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has sent the specified type of request to. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchFriendshipStatusRequestSentUsers($uid_1, $user_relationship_type_label)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	$query = "SELECT uid, username, first_name, last_name 
			  FROM R_USER_RELATIONSHIP 
			    LEFT JOIN T_USER_RELATIONSHIP_TYPE ON 
			      T_USER_RELATIONSHIP_TYPE .user_relationship_type_code = R_USER_RELATIONSHIP.user_relationship_type_code 
			    LEFT JOIN T_USER ON R_USER_RELATIONSHIP.uid_2 = T_USER.uid
			  WHERE uid_1 = ? AND user_relationship_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $uid_1, $user_relationship_type_label);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// ASSIGN THE USER VARIABLES
	$statement->bind_result($uid, $username, $first_name, $last_name);

	$users = array();
	
	while($statement->fetch())
	{
		$user = array
		(
			"uid" => $uid, 
			"username" => $username, 
			"firstName" => $first_name, 
			"lastName" => $last_name
		);
		array_push($users, $user);
	}
	
	$statement->close();
	
	// RETURN THE LIST OF USERS
	return $users;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    fetchFriendshipStatusRequestReceivedUsers
 * NOTE:        User 2 is the user that received the friendship status request).
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has received the specified type of request from.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchFriendshipStatusRequestReceivedUsers($uid_2, $user_relationship_type_label)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	$query = "SELECT uid, username, first_name, last_name
			  FROM R_USER_RELATIONSHIP
			    LEFT JOIN T_USER_RELATIONSHIP_TYPE ON
			      T_USER_RELATIONSHIP_TYPE .user_relationship_type_code = R_USER_RELATIONSHIP.user_relationship_type_code
			    LEFT JOIN T_USER ON R_USER_RELATIONSHIP.uid_1 = T_USER.uid
			  WHERE uid_2 = ? AND user_relationship_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $uid_2, $user_relationship_type_label);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// ASSIGN THE USER VARIABLES
	$statement->bind_result($uid, $username, $first_name, $last_name);

	$users = array();

	while($statement->fetch())
	{
		$user = array
		(
			"uid" => $uid,
			"username" => $username,
			"firstName" => $first_name,
			"lastName" => $last_name
		);
		array_push($users, $user);
	}

	$statement->close();

	// RETURN THE LIST OF USERS
	return $users;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    isFriend
 * NOTE:        User 1 is THIS user (i.e. the logged in user), User 2 is the OTHER.
 * DESCRIPTION: This method returns "true" if the two specified users are friends
 *              and "false" otherwise.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function isFriend($uid_1, $uid_2)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	$query = "SELECT 1
			  FROM R_USER_RELATIONSHIP
			  WHERE user_relationship_type_code = (
  				SELECT user_relationship_type_code 
  				FROM T_USER_RELATIONSHIP_TYPE 
  				WHERE user_relationship_type_label = 'Friend')
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
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>