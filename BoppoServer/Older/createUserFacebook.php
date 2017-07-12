<?php

    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */

    // 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

    // 2 - DECODE INCOMING JSON CONTENTS
	$_POST = json_decode(file_get_contents("php://input"), true);

    // 3 - DETERMINE USERNAME, PASSWORD, FIRST NAME, LAST NAME,
    //     AND EMAIL FROM JSON CONTENTS
	$facebook_uid = $_POST["facebookUid"];
	$first_name   = $_POST["firstName"];
	$last_name    = $_POST["lastName"];
	$email        = $_POST["email"];
	$phone        = $_POST["phone"];

	// 4 - PREPARE THE QUERY
	$query = "INSERT INTO T_USER (facebook_uid, first_name, last_name, email, phone) 
		      VALUES (?, ?, ?, ?, ?)";
	$statement = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($statement, "sssss", $facebook_uid, $first_name, $last_name, $email, $phone);

	// 5 - EXECUTE THE QUERY
	mysqli_stmt_execute($statement);

	// 6 - STORE THE QUERY ERROR, IF ANY, IN A VARIABLE
	$error = mysqli_stmt_error($statement);

	// 7 - RETURN RESULTING ERROR, IF ANY, AND CLOSE STATEMENT
	if ($error != "")
	    echo $error;
	else
	    echo "USER CREATED SUCCESSFULLY.";

	mysqli_stmt_close($statement);

?>