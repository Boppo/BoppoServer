<?php

$function = $_GET['function'];

if ($function == "getFriends")
	getFriends();



/* FUNCTION: getFriends
 * DESCRIPTION: Returns all of the friends of a particular user
 * -------------------------------------------------------------------------------- 
 * ================================================================================ 
 * -------------------------------------------------------------------------------- */
function getFriends() 
{
	require '../../DBConnect/dbConnect.php';
	// 1 - DECODE JSON STRING
	//     THIS WILL GIVE THE LOGGED-IN USER'S ID
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	$uid = $json_decoded["uid"];
	
	// 2 - END IF PROVIDED UID IS NOT A NUMBER
	if (!is_numeric($uid)) 
	{
		echo "UID IS NOT A NUMBER.";
		return;
	}
	// 3 - END IF PROVIDED UID IS NOT A USER
	$uid_user = -1;
	$query =   "SELECT uid 
	            FROM T_USER 
	            WHERE uid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid);
	$statement->execute();
	$statement->bind_result($uid_user);
	$statement->fetch();
	if ($uid_user == -1)
	{
	    echo "USER WITH PROVIDED UID DOES NOT EXIST.";
	    return;
	}
	$statement->close();
	
	$friends = array();
	
	// 4 - GET THE CODE FOR A 'FRIENDS' STATUS
	// 4.1 - PREPARE THE QUERY
	$query = "SELECT friendship_status_type_code
              FROM T_FRIENDSHIP_STATUS_TYPE
              WHERE friendship_status_type_label = 'Friends'";
	$statement = $conn->prepare($query);
	// 4.2 - EXECUTE THE QUERY
	$statement->execute();
	// 4.3 - CHECK FOR ERROR AND STOP IF EXISTS
	$error = $statement->error;
	if ($error != "")
	{
	    echo $error;
	    return;
	}
	// 4.4 - STORE THE QUERY RESUlT IN A VARIABE
	$statement->bind_result($status_friends);
	$statement->fetch();
	$statement->close();  // Need to close statements if variable is to be recycled
	
	// 5 - CHECK TABLE R_FRIEND COLUMN UID_1 FOR FRIENDS
	$uid_friend = -1;
	$first_name = "";
	$last_name  = "";
	$query =   "SELECT uid, first_name, last_name  
                FROM R_FRIENDSHIP_STATUS, T_USER 
                WHERE 
	               uid_1 = uid AND 
                   uid_2 = ? AND 
	               friendship_status_type_code = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("ii", $uid, $status_friends);
	$statement->execute();
	$statement->bind_result($uid_friend, $first_name, $last_name);
	while ($statement->fetch()) {
	    $temp = array(
	        "uid" => $uid_friend, 
            "firstName" => $first_name, 
            "lastName" => $last_name
	    );
		array_push($friends, $temp);
	}
	$statement->close();
	
	// 6 - CHECK TABLE R_FRIEND COLUMN UID_2 FOR FRIENDS
	$uid_friend = -1;
	$first_name = "";
	$last_name  = "";
	$query =   "SELECT uid, first_name, last_name  
                FROM R_FRIENDSHIP_STATUS, T_USER 
                WHERE 
	               uid_2 = uid AND
                   uid_1 = ? AND
   	               friendship_status_type_code = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("ii", $uid, $status_friends);
	$statement->execute();
	$statement->bind_result($uid_friend, $first_name, $last_name);
	while ($statement->fetch()) {
	    $temp = array(
	        "uid" => $uid_friend, 
            "firstName" => $first_name, 
            "lastName" => $last_name
	    );
		array_push($friends, $temp);
	}
	$statement->close();
	
	// 7 - ENCODE THE DATA INTO JSON STRING AND RETURN
	echo json_encode($friends);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>