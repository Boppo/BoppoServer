<?php

    // 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

    // 2 - DECODE INCOMING JSON CONTENTS
	$_POST = json_decode(file_get_contents("php://input"), true);

    // 3 - DETERMINE USERNAME, PASSWORD, FIRST NAME, LAST NAME,
    //     AND EMAIL FROM JSON CONTENTS
	$facebook_uid = $_POST["facebookUid"];
	$first_name   = $_POST["firstName"];
	$last_name    = $_POST["lastName"];
	$email        = $_POST["email"];

	// 4 - PREPARE THE QUERY
	$query = "INSERT INTO T_USER (facebook_uid, first_name, last_name, email) 
		      VALUES (?, ?, ?, ?)";
	$statement = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($statement, "ssss", $facebook_uid, $first_name, $last_name, $email);

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