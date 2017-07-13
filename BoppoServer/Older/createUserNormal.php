<?php

    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */

    // 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

    // 2 - DECODE INCOMING JSON CONTENTS
	$_POST = json_decode(file_get_contents("php://input"), true);

    // 3 - DETERMINE USERNAME, PASSWORD, FIRST NAME, LAST NAME,
    //     AND EMAIL FROM JSON CONTENTS
	$username   = $_POST["username"];
	$password   = $_POST["password"];
	$first_name = $_POST["firstName"];
	$last_name  = $_POST["lastName"];
	$email      = $_POST["email"];
	$phone      = $_POST["phone"];

	// 4 - PREPARE THE QUERY
	$query = "INSERT INTO T_USER (username, password, first_name, last_name, email, phone) 
		      VALUES (?, ?, ?, ?, ?, ?)";
	$statement = mysqli_prepare($conn, $query);
	mysqli_stmt_bind_param($statement, "ssssss", $username, $password, $first_name, $last_name, $email, $phone);

	// 5 - EXECUTE THE QUERY AND GET BACK THE INSERT ID AND/OR ERROR
	mysqli_stmt_execute($statement);
    $insert_id = mysqli_insert_id($conn); 
    $error = mysqli_stmt_error($statement);
    
	// 6 - IF AN ERROR EXISTS, PRINT IT AND RETURN
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// 7 - RETURN THE AUTO INCREMENT VALUE INSERTED BY THE QUERY
	echo "Success: " . $insert_id;

	mysqli_stmt_close($statement);

?>
