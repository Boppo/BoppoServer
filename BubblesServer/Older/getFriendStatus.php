<?php

    /* NOTE: User 1 is the user that sent out a friend request.
             User 2 is the user that received a friend request. */

    // 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

    // 2 - DECODE INCOMING JSON CONTENTS
	$_POST = json_decode(file_get_contents("php://input"), true);

    // 3 - DETERMINE THE INVOLVED USERS
	$uid_1 = $_POST["uid1"];
	$uid_2 = $_POST["uid2"];
    // $uid1 = 1;
    // $uid2 = 4;
    
    // 4 - CHECK IF ALREADY FRIENDS
    // 4.1 - PREPARE THE QUERY
    $query = "SELECT uid_1, uid_2
              FROM R_FRIENDSHIP_STATUS
              WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND
                friendship_status_type_code = (
                    SELECT friendship_status_type_code 
                    FROM T_FRIENDSHIP_STATUS_TYPE 
                    WHERE friendship_status_type_label = 'Friends'
                )";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "iiii", $uid_1, $uid_2, $uid_1, $uid_2);
    // 4.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 4.3 - RESET RESULT VARIABLES
    $result_uid_1 = -1;
    $result_uid_2 = -1;
    // 4.4 - STORE THE QUERY RESULT IN VARIABLES
    mysqli_stmt_bind_result($statement, $result_uid_1, $result_uid_2);
    mysqli_stmt_fetch($statement); 
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 4.5 - RETURN MESSAGE IF FRIENDSHIP EXISTS
    if ($result_uid_1 != -1 && $result_uid_2 != -1) {
        echo "Already friends with user.";
        return;
    }
        
    // 5 - CHECK IF REQUEST WAS SENT
    // 5.1 - PREPARE THE QUERY
    $query = "SELECT uid_1, uid_2
              FROM R_FRIENDSHIP_STATUS
              WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND
                friendship_status_type_code = (
                    SELECT friendship_status_type_code 
                    FROM T_FRIENDSHIP_STATUS_TYPE 
                    WHERE friendship_status_type_label = 'Request Sent'
                )";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "iiii", $uid_1, $uid_2, $uid_1, $uid_2);
    // 5.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 5.3 - RESET RESULT VARIABLES
    $result_uid_1 = -1;
    $result_uid_2 = -1;
    // 5.4 - STORE THE QUERY RESULT IN VARIABLES
    mysqli_stmt_bind_result($statement, $result_uid_1, $result_uid_2);
    mysqli_stmt_fetch($statement); 
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 5.5 - IF THE REQUEST WAS SENT
    if ($result_uid_1 != 0 && $result_uid_2 != 0) {
        // 5.5.1 - IF THE REQUEST WAS SENT BY THE SAME USER (LOGGED IN USER)
        if ($result_uid_1 == $uid_1 && $result_uid_2 == $uid_2) {
            echo "Already sent friend request to user.";
            return;
        }
        // 5.5.2 - IF THE REQUEST WAS SENT BY THE OTHER USER
        else if ($result_uid_1 == $uid_2 && $result_uid_2 == $uid_1) {
            echo "User is awaiting confirmation for friend request.";
            return;
        }
    }
    
    // 6 - CHECK IF USER IS BEING BLOCKED
    // 6.1 - PREPARE THE QUERY
    $query = "SELECT uid_1, uid_2
              FROM R_FRIENDSHIP_STATUS
              WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND
                friendship_status_type_code = (
                    SELECT friendship_status_type_code
                    FROM T_FRIENDSHIP_STATUS_TYPE
                    WHERE friendship_status_type_label = 'Blocked'
                )";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "iiii", $uid_1, $uid_2, $uid_1, $uid_2);
    // 6.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 6.3 - RESET RESULT VARIABLES
    $result_uid_1 = -1;
    $result_uid_2 = -1;
    // 6.4 - STORE THE QUERY RESULT IN VARIABLES
    mysqli_stmt_bind_result($statement, $result_uid_1, $result_uid_2);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 6.5 - IF USER IS BEING BLOCKED
    if ($result_uid_1 != 0 && $result_uid_2 != 0) {
        // 6.5.1 - IF THE OTHER USER IS BEING BLOCKED
        if ($result_uid_1 == $uid_1 && $result_uid_2 == $uid_2) {
            echo "User is currently being blocked.";
            return;
        }
        else if ($result_uid_1 == $uid_2 && $result_uid_2 == $uid_1) {
            echo "Currently being blocked by user.";
            return;
        }
    }
    
    echo "Not friends.";
    return;
    
?>