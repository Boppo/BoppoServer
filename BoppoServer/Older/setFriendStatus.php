<?php

    /* NOTE: User 1 is the user that sent out a friend request.
             User 2 is the user that received a friend request. */

    // 1 - ESTABLISH DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

    // 2 - DECODE INCOMING JSON CONTENTS
	$_POST = json_decode(file_get_contents("php://input"), true);

    // 3 - DETERMINE THE INVOLVED USERS
	$uid_1 = $_POST["uid1"];
	$uid_2 = $_POST["uid2"];
    //$uid_1 = 4;
    //$uid_2 = 1;
    
    // 4 - CHECK IF ALREADY FRIENDS
    // 4.1 - PREPARE THE QUERY
    $query = "SELECT uid_1, uid_2
              FROM R_USER_RELATIONSHIP
              WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND
                user_relationship_type_code = (
                    SELECT user_relationship_type_code 
                    FROM T_USER_RELATIONSHIP_TYPE
                    WHERE user_relationship_type_label = 'Friend'
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
              FROM R_USER_RELATIONSHIP
              WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND
                user_relationship_type_code = (
                    SELECT user_relationship_type_code 
                    FROM T_USER_RELATIONSHIP_TYPE 
                    WHERE user_relationship_type_label = 'Friendship Pending'
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
        // 5.5.1 - IF THE REQUEST WAS SENT BY THE SAME USER
        if ($result_uid_1 == $uid_1 && $result_uid_2 == $uid_2) {
            echo "Already sent friend request to user.";
            return;
        }
        // 5.5.2 - IF THE REQUEST WAS SENT BY OTHER USER
        else if ($result_uid_1 == $uid_2 && $result_uid_2 = $uid_1) 
        {
            // 5.5.2.1 - GET THE CODE FOR A 'FRIENDS' STATUS
            // 5.5.2.1.1 - PREPARE THE QUERY
            $query = "SELECT user_relationship_type_code
                      FROM T_USER_RELATIONSHIP_TYPE
                      WHERE user_relationship_type_label = 'Friend'";
            $statement = $conn->prepare($query);
            // 5.5.2.1.2 - EXECUTE THE QUERY
            $statement->execute();
            // 5.5.2.1.3 - CHECK FOR ERROR AND STOP IF EXISTS
            $error = $statement->error;
            if ($error != "")
            {
                echo $error;
                return;
            }
            // 5.5.2.1.4 - STORE THE QUERY RESUlT IN A VARIABE
            $statement->bind_result($status_friends);
            $statement->fetch();
            $statement->close();  // Need to close statements if variable is to be recycled
            // 5.5.2.2 - SET FRIENDSHIP STATUS TO FRIENDS
            $query = "UPDATE R_USER_RELATIONSHIP
                      SET user_relationship_type_code = ?
                      WHERE uid_1 = ? AND uid_2 = ?";
            $statement = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($statement, "iii", $status_friends, $uid_2, $uid_1);
            // 5.5.2.3 - EXECUTE THE QUERY
            mysqli_stmt_execute($statement);
            echo "Friend request accepted.";
            return;
        }
    }
    
    // 6 - SEND FRIEND REQUEST
    // 6.1 - GET THE CODE FOR A SENT REQUEST
    // 6.1.1 - PREPARE THE QUERY
    $query = "SELECT user_relationship_type_code
              FROM T_USER_RELATIONSHIP_TYPE
              WHERE user_relationship_type_label = 'Friendship Pending'";
    $statement = $conn->prepare($query);
    // 6.1.2 - EXECUTE THE QUERY
    $statement->execute();
    // 6.1.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = $statement->error;
    if ($error != "")
    {
        echo $error;
        return;
    }
    // 6.1.4 - STORE THE QUERY RESUlT IN A VARIABE
    $statement->bind_result($request_sent);
    $statement->fetch();
    $statement->close();  // Need to close statements if variable is to be recycled
    // 6.2 - PREPARE THE QUERY
    $query = "INSERT INTO R_USER_RELATIONSHIP (uid_1, uid_2, user_relationship_type_code) 
              VALUES (?, ?, ?)";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "iii", $uid_1, $uid_2, $request_sent);
    // 6.3 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    
    echo "Friendship Pending request sent successfully.";
    return;
    
?>