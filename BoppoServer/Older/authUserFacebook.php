<?php

	// 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// 2 - DECODE INCOMING JSON CONTENTS
	$_POST = json_decode(file_get_contents("php://input"), true);

	// 3 - DETERMINE USERNAME AND PASSWORD FROM JSON CONTENTS
	$facebook_uid = $_POST["facebookUid"];

	// 4 - PREPARE THE QUERY
	$query = "SELECT uid, facebook_uid, googlep_uid, username, password, 
                first_name, last_name, email, phone, privacy_label
              FROM T_USER, T_PRIVACY
		      WHERE facebook_uid = ? AND user_privacy_code = privacy_code";
	$statement = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($statement, "s", $facebook_uid);
	
	// 5 - EXECUTE THE QUERY
	mysqli_stmt_execute($statement);

	// 6 - RESET THE JSON VARIABLES
	$facebook_uid = null;

	// 7 - STORE THE QUERY RESULT IN VARIABLES
	mysqli_stmt_bind_result($statement, 
	    $uid, $facebook_uid, $googlep_uid, $username, $password, $first_name, $last_name, 
	    $email, $phone, $user_account_creation_timestaamp, $user_privacy_label);
	mysqli_stmt_fetch($statement);

	// 8 - STORE RESULTING VARIABLES IN ASSOC ARRAY
	$result = array(
		"uid" => $uid,
		"facebookUid" => $facebook_uid,
		"googlepUid" => $googlep_uid,
		"username" => $username,
		"password" => $password,
		"firstName" => $first_name,
		"lastName" => $last_name,
		"email" => $email,
	    "phone" => $phone, 
	    "userInsertTimestamp" => $user_insert_timestamp, 
        "userPrivacy" => $user_privacy_label
	);
	
	// 9 - CLOSE THE STATEMENT
	mysqli_stmt_close($statement);
	
    // 10 - RETURN JSON-ENCODED ARRAY IF USER EXISTS, STRING OTHERWISE
    if ($uid == 0) 
        echo "FACEBOOK USER DOES NOT EXIST IN THE DATABASE.";
    else
        echo json_encode($result);

?>