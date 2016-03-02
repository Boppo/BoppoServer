<?php

$function = $_GET['function'];

if ($function == "eventCreate")
	eventCreate();

	
	
/* FUNCTION: getUserData
 * DESCRIPTION: Retrieves and returns all of the user's information.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function eventCreate()
{
	require '../../DBConnect/dbConnect.php';
	// 1 - DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);

	// 2 - DETERMINE BUBBLES USER ID FROM JSON DECODED STRING ARRAY
	$uid = $json_decoded["uid"];

	// 3 - GET THE USER DATA
	// 3.1 - PREPARE THE QUERY
	$query = "SELECT uid, facebook_uid, googlep_uid, username, password,
                  first_name, last_name, email, user_account_creation_timestamp, privacy_label
              FROM T_USER, T_PRIVACY
              WHERE uid = ? AND user_account_privacy_code = privacy_code";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid);
	// 3.2 - EXECUTE THE QUERY
	$statement->execute();

	// 3.3 - CHECK FOR ERROR, PROCEED IF THERE WAS NO ERROR
	$error = $statement->error;
	if ($error != "") {
		echo "BACK-END ERROR: " . $error;
		return;
	}
	else {

		// 3.4 - STORE THE QUERY RESULT IN VARIABLES
		$statement->bind_result($uid, $facebook_uid, $googlep_uid, $username, $password,
				$first_name, $last_name, $email, $user_account_creation_timestamp, $user_account_privacy_label);
		$statement->fetch();

		// 3.5 - STORE THE QUERY RESULT IN AN ARRAY
		$data = array(
				"uid" => $uid,
				"facebookUid" => $facebook_uid,
				"googlepUid" => $googlep_uid,
				"username" => $username,
				"password" => $password,
				"firstName" => $first_name,
				"lastName" => $last_name,
				"email" => $email,
				"userAccountCreationTimestamp" => $user_account_creation_timestamp,
				"userAccountPrivacy" => $user_account_privacy_label
		);

		// 3.6 - RETURN THE JSON-ENCODED ARRAY QUERY RESULT
		echo json_encode($data);

		$statement->close();  // Need to close statements if variable is to be recycled
	}
}
	
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */