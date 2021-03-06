<?php

$function = $_GET['function'];

if ($function == "blockUser")
	blockUser();
if ($function == "unblockUser")
	unblockUser();
if ($function == "getFriendshipStatus")
	getFriendshipStatus();
if ($function == "getFriendshipStatusRequestSentUsers")
	getFriendshipStatusRequestSentUsers();
if ($function == "getFriendshipStatusRequestReceivedUsers")
	getFriendshipStatusRequestReceivedUsers();
if ($function == "rejectFriend")
	rejectFriend();
if ($function == "cancelFriend")
	cancelFriend();
if ($function == "unFriend")
	unFriend();

	
	
/* FUNCTION:    blockUser
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: This method attempts to let User 1 block User 2. The resulting 
 *              string will either state that the block was successful, that User 2
 *              is already blocked, or that User 2 already blocker User 1.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function blockUser()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// OBTAIN THE CURRENT RELATIONSHIP BETWEEN USER 1 AND USER 2
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/FriendshipStatus.php';
	$user_relationship_type_label = dbGetFriendshipStatus($uid_1, $uid_2);
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	// ALSO CHECK FOR WHETHER OR NOT EITHER USER IS ALREADY BLOCKED, RETURN IF BLOCKED
	if ((strpos($user_relationship_type_label, "DB ERROR: ") !== false) ||
		(strpos($user_relationship_type_label, "Blocked") !== false))
	{
		echo $user_relationship_type_label;
		return;
	}
		
	// CHECK WHETHER THE USERS ARE BLOCKING EACH OTHER, BLOCK THE OTHER IF NOT
	if (strpos($user_relationship_type_label, "Blocked") === false)
	{
		// EXECUTE A QUERY TO CALL A STORED PROCEDURE TO BLOCK THE OTHER USER
		$query = "CALL sp_blockUser(?, ?)"; 
		// NOTE: DO NOT EVER USE THE ABOVE SP IF ONE OF THE TWO USERS ALREADY BLOCKED THE OTHER!
		$statement = $conn->prepare($query);
		$statement->bind_param("ii", $uid_1, $uid_2);
		$statement->execute();
		
		// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
		$error = $statement->error;
		if ($error != "") { echo "DB ERROR: " . $error; return; }
		
		echo "This user has successfully blocked the other user.";
		return;
	}

	// RETURN A SUCCESS MESSAGE
	echo "<empty>";
	return;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    unblockUser
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: This method attempts to let User 1 unblock User 2. The resulting
 *              string will either state that the unblock was successful, that User 2
 *              is already unblocked, or that User 2 is the one blocking User 1.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function unblockUser()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// OBTAIN THE CURRENT RELATIONSHIP BETWEEN USER 1 AND USER 2
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/FriendshipStatus.php';
	$user_relationship_type_label = dbGetFriendshipStatus($uid_1, $uid_2);
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	// ALSO CHECK IF THE USER TO BE UNBLOCKED (UID2) IS ALREADY BLOCKING THIS USER (UID1), RETURN IF BLOCKED
	// AND ALSO CHECK IF NEITHER OF THE USERS IS BLOCKING EACH OTHER
	if ((strpos($user_relationship_type_label, "DB ERROR: ") !== false) ||
		(strpos($user_relationship_type_label, "blocked") === false) || 
		(strpos($user_relationship_type_label, "This user is already blocked by the other user.") !== false))
	{
		echo $user_relationship_type_label;
		return;
	}

	// CHECK WHETHER THE USER ATTEMPTING TO UNBLOCK (UID1) IS CURRENTLY BLOCKING THE OTHER USER (UID2)
	if (strpos($user_relationship_type_label, "This user already blocked the other user.") !== false)
	{	
		// EXECUTE A QUERY TO CALL A STORED PROCEDURE TO UNBLOCK THE OTHER USER
		$query = "DELETE 
				  FROM R_USER_RELATIONSHIP 
				  WHERE uid_1 = ? AND uid_2 = ? 
				    AND user_relationship_type_code = (
				      SELECT user_relationship_type_code 
				      FROM T_USER_RELATIONSHIP_TYPE 
				      WHERE user_relationship_type_label = 'Blocked')";

		$statement = $conn->prepare($query);
		$statement->bind_param("ii", $uid_1, $uid_2);
		$statement->execute();

		// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
		$error = $statement->error;
		if ($error != "") { echo "DB ERROR: " . $error; return; }
		
		if ($statement->affected_rows === 1)
		{
			echo "User has been successfully unblocked.";
			return;
		}
		else if ($statement->affected_rows === 0)
		{
			echo "User could not be unblocked because he is currently not being blocked by this user.";
			return;
		}
		else
		{
			echo "QUERY FLAWED: Please contact the database administrator because multiple friendships were rejected!";
			return;
		}
	}

	// RETURN A SUCCESS MESSAGE
	echo "QUERY FLAWED: Please contact the database administrator because an unexpected case occurred.";
	return;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    getFriendshipStatusRequestSentUsers
 * NOTE:        User 1 is the user that sent the friendship status request.
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has sent the specified type of request to.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getFriendshipStatus()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// OBTAIN THE CURRENT RELATIONSHIP BETWEEN USER 1 AND USER 2
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Older/getFriendStatus.php';
	$friendship_status = fetchFriendshipStatus($uid_1, $uid_2);

	// RETURN THE FRIENDSHIP STATUS
	echo $friendship_status;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    getFriendshipStatusRequestSentUsers
 * NOTE:        User 1 is the user that sent the friendship status request.
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has sent the specified type of request to. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getFriendshipStatusRequestSentUsers()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$user_relationship_type_label = $json_decoded["userRelationshipTypeLabel"];

	// OBTAIN THE CURRENT RELATIONSHIP BETWEEN USER 1 AND USER 2
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/FriendshipStatus.php';
	$users = fetchFriendshipStatusRequestSentUsers($uid_1, $user_relationship_type_label);
	
	// RETURN THE USERS
    echo json_encode($users);
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    getFriendshipStatusRequestReceivedUsers
 * NOTE:        User 2 is the user that received the friendship status request).
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has received the specified type of request from.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getFriendshipStatusRequestReceivedUsers()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_2 = $json_decoded["uid2"];
	$user_relationship_type_label = $json_decoded["userRelationshipTypeLabel"];

	// OBTAIN THE CURRENT RELATIONSHIP BETWEEN USER 1 AND USER 2
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/FriendshipStatus.php';
	$users = fetchFriendshipStatusRequestReceivedUsers($uid_2, $user_relationship_type_label);

	// RETURN THE USERS
	echo json_encode($users);
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    rejectFriend
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: This method attempts to let User 1 reject a friend request received
 *              from User 2. The resulting string will either state that the reject
 *              was successful or a different message otherwise.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function rejectFriend()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// PREPARE THE QUERY
	$query = "DELETE 
			  FROM R_USER_RELATIONSHIP 
			  WHERE uid_2 = ? AND uid_1 = ? AND user_relationship_type_code = 
			   (SELECT user_relationship_type_code 
			    FROM T_USER_RELATIONSHIP_TYPE 
			    WHERE user_relationship_type_label = 'Friendship Pending')";
	$statement = $conn->prepare($query);
	$statement->bind_param("ii", $uid_1, $uid_2);
	$statement->execute();
	
	if ($statement->affected_rows === 1)
	{
		echo "Friendship request has been successfully rejected.";
		return;
	}
	else if ($statement->affected_rows === 0)
	{
		echo "Friendship request could not be rejected because it does not exist.";
		return;
	}
	else
	{
		echo "QUERY FLAWED: Please contact the database administrator because multiple friendships were rejected!";
		return;
	}
	
	$statement->close();
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    cancelFriend
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: This method attempts to let User 1 cancel a friend request that 
 *              (s)he sent to User 2. The resulting string will either state that 
 *              the cancel was successful or a different message otherwise.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function cancelFriend()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// PREPARE THE QUERY
	$query = "DELETE
			  FROM R_USER_RELATIONSHIP
			  WHERE uid_1 = ? AND uid_2 = ? AND user_relationship_type_code =
			   (SELECT user_relationship_type_code
			    FROM T_USER_RELATIONSHIP_TYPE
			    WHERE user_relationship_type_label = 'Friendship Pending')";
	$statement = $conn->prepare($query);
	$statement->bind_param("ii", $uid_1, $uid_2);
	$statement->execute();

	if ($statement->affected_rows === 1)
	{
		echo "Friendship request has been successfully canceled.";
		return;
	}
	else if ($statement->affected_rows === 0)
	{
		echo "Friendship request could not be rejected because it does not exist.";
		return;
	}
	else
	{
		echo "QUERY FLAWED: Please contact the database administrator because multiple friendships were rejected!";
		return;
	}

	$statement->close();
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    unFriend
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: This method attempts to let User 1 unfriend a friend whom User 2 
 *              is. The resulting string will either state that
 *              the cancel was successful or a different message otherwise.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function unFriend()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// PREPARE THE QUERY
	$query = "DELETE
			  FROM R_USER_RELATIONSHIP
			  WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND user_relationship_type_code =
			   (SELECT user_relationship_type_code
			    FROM T_USER_RELATIONSHIP_TYPE
			    WHERE user_relationship_type_label = 'Friend')";
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid_1, $uid_2, $uid_1, $uid_2);
	$statement->execute();

	if ($statement->affected_rows === 1)
	{
		echo "Friend has been successfully unfriended.";
		return;
	}
	else if ($statement->affected_rows === 0)
	{
		echo "Unfriend request failed because such a friendship does not exist.";
		return;
	}
	else
	{
		echo "QUERY FLAWED: Please contact the database administrator because multiple friendships were rejected!";
		return;
	}

	$statement->close();
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>