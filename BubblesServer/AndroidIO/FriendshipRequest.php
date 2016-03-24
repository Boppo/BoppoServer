<?php

$function = $_GET['function'];

if ($function == "blockUser")
	blockUser();

	
	
/* FUNCTION:    blockUser
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: This method attempts to let User 1 block User 2. The resulting 
 *              string will either state that the block was successful, that Iser 2
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
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid_1 = $json_decoded["uid1"];
	$uid_2 = $json_decoded["uid2"];

	// OBTAIN THE CURRENT RELATIONSHIP BETWEEN USER 1 AND USER 2
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Friendship.php';
	$friendship_status_type_label = getFriendshipStatus($uid_1, $uid_2);
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	// ALSO CHECK FOR WHETHER OR NOT EITHER USER IS ALREADY BLOCKED, RETURN IF BLOCKED
	if ((strpos($friendship_status_type_label, "DB ERROR: ") !== false) ||
		(strpos($friendship_status_type_label, "blocked") !== false))
	{
		echo $friendship_status_type_label;
		return;
	}
		
	// CHECK WHETHER THE USERS ARE BLOCKING EACH OTHER, BLOCK THE OTHER IF NOT
	if (strpos($friendship_status_type_label, "blocked") === false)
	{
		// EXECUTE A QUERY TO CALL A STORED PROCEDURE TO BLOCK THE OTHER USER
		$query = "CALL sp_blockUser(?, ?)"; 
		// NOTE: DO NOT EVER USE THE ABOVE SP IF ONE OF THE TWO USERS ALREADY BLOCKED THE OTHER!
		$statement = $conn->prepare($query);
		$statement->bind_param("ii", $uid_1, $uid_2);
		$statement->execute();
		$statement->error;
		
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
?>