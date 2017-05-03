<?php

    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */

    // 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

    // 2 - DECODE INCOMING JSON CONTENTS
    $_POST = json_decode(file_get_contents("php://input"), true);

    // 3 - DETERMINE USERNAME AND PASSWORD FROM JSON CONTENTS
    $username = $_POST["username"];
    $password = $_POST["password"];

    // 4 - PREPARE THE QUERY
    $query = "SELECT uid, facebook_uid, googlep_uid, username, password, 
                first_name, last_name, email, phone, 
    			user_insert_timestamp, privacy_label
              FROM T_USER, T_PRIVACY
              WHERE username = ? AND password = ? AND user_privacy_code = privacy_code";
    
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "ss", $username, $password);
    
    // 5 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);

    // 6 - RESET THE JSON VARIABLES
    $username = null;
    $password = null;

    // 7 - STORE THE QUERY RESULT IN VARIABLES
    mysqli_stmt_bind_result($statement, 
        $uid, $facebook_uid, $googlep_uid, $username, $password, $first_name, $last_name, 
        $email, $phone, $user_insert_timestamp, $user_privacy_label);
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
        echo "USERNAME AND PASSWORD COMBINATION IS INCORRECT.";
    else
        echo json_encode($result);

?>