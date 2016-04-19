<?php

/* FUNCTION: getUserFriendRequestUsers
 * DESCRIPTION: Retrieves and returns all of the users that sent friend requests
 * 				to the specified (logged in) user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getUserFriendRequestUsers()
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	$query = "SELECT user_relationship_type_code
			  FROM T_USER_RELATIONSHIP_TYPE
			  WHERE user_relationship_type_label = 'Friendship Pending'";
	$statement = $conn->prepare($query);
	// 3.2 - EXECUTE THE QUERY
	$statement->execute();
	// 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
	$error = $statement->error;
	if ($error != "") {
		echo "MYSQL ERROR: " . $error;
		return; }
		// 3.4 - STORE THE QUERY RESULT IN A VARIABLE
		$statement->bind_result($user_relationship_type_code);
		$statement->fetch();
		$statement->close(); 	// Need to close statements if variable is to be recycled
		// 3.5 - CHECK IF VALUE EXISTS AND STOP IF IT DOESN'T
		if ($user_relationship_type_code == -1) {
			echo "FRIENDSHIP STATUS TYPE LABEL IS NOT VALID.";
			return;
		}

		// 4 - PREPARE THE QUERY
		$query = "SELECT uid_1
				  FROM R_USER_RELATIONSHIP
				  WHERE uid_2 = ?
					AND user_relationship_type_code = ?";
		$statement = $conn->prepare($query);
		$statement->bind_param("ii", $uid, $user_relationship_type_code);

		// 5 - EXECUTE THE QUERY
		$statement->execute();

		// 6 - RETURN RESULTING ERROR IF THERE IS ONE, OTHERWISE A LIST OF UIDs, THEN CLOSE STATEMENT
		$error = $statement->error;
		if ($error != "") {
			echo "MYSQL ERROR: " . $error;
			return; }
			else {

				// 7 - STORE THE RESULTING VARIABLES IN AN INDEX ARRAY
				$statement->bind_result($uid_2);
				$data = array();
				while ($statement->fetch())
					array_push($data, $uid_2);

					// 8 - RETURN JSON-ENCODED ARRAY AND CLOSE STATEMENT
					echo json_encode($data);
			}

			$statement->close(); 	// Need to close statements if variable is to be recycled
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>