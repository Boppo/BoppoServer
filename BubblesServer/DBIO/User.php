<?php

/* FUNCTION: fetchUserEncoded
 * DESCRIPTION: Gets the image and its data by specified User Identifier (uid).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchUserEncoded($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT  uid, first_name, last_name, email, phone, user_account_privacy_code
            FROM    T_USER
            WHERE   uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($uid, $first_name, $last_name, $email, $phone, $user_account_privacy_code);
  $statement->fetch();

  $user = array
  (
      "uid" => $uid, 
      "firstName" => $first_name, 
      "lastName" => $last_name, 
      "email" => $email, 
      "phone" => $phone, 
      "userAccountPrivacyCode" => $user_account_privacy_code
  );

  $statement->close();

  return $user;
}



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



/* FUNCTION:    dbSetUser
 * DESCRIPTION: Updates the user's properties in the database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetUser($user)
{
    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */
  
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// FETCH THE CURRENT VALUES FOR THIS EVENT
	$userCurrent = fetchUserEncoded($user["uid"]);
	
	if ($user["firstName"] != null)
	  $userCurrent["firstName"] = $user["firstName"];
	if ($user["lastName"] != null)
	  $userCurrent["lastName"] = $user["lastName"];
	if ($user["email"] != null)
	  $userCurrent["email"] = $user["email"];
	if ($user["phone"] != null)
	  $userCurrent["phone"] = $user["phone"];
	if ($user["userAccountPrivacyCode"] != null)
	  $userCurrent["userAccountPrivacyCode"] = $user["userAccountPrivacyCode"];
	
	// EXECUTE THE QUERY
	$query = "UPDATE T_USER
			  SET    first_name = ?, 
	                 last_name = ?, 
	                 email = ?, 
	                 phone = ?, 
	                 user_account_privacy_code = ?
	          WHERE  uid = ?";
		
	$statement = $conn->prepare($query);
		
	$statement->bind_param("ssssii", $userCurrent["firstName"], $userCurrent["lastName"], 
	    $userCurrent["email"], $userCurrent["phone"], $userCurrent["userAccountPrivacyCode"], 
	    $userCurrent["uid"]);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { return "DB ERROR: " . $error; }
		
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	if ($statement->affected_rows === 1)
		return "User has been successfully updated.";
	else 
		return "User has failed to update: no user or multiple users have been updated.";
	
	$statement->close();
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */





?>