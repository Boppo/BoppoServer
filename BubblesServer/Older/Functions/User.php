<?php

$function = $_GET['function'];

if ($function == "syncUserFacebook")
    syncUserFacebook();
if ($function == "setUserAccountPrivacyLabel")
    setUserAccountPrivacyLabel();
if ($function == "changeEmail")
    changeEmail();
if ($function == "changePassword")
    changePassword();
if ($function == "getUserData")
    getUserData();
if ($function == "getUserFriendRequestUsers")
	getUserFriendRequestUsers();
    


/* FUNCTION: changePassword
 * DESCRIPTION: Sets the user's e-mail to the specified value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function changeEmail()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);

    // 2 - DETERMINE BUBBLES USER ID AND EMAIL FROM JSON DECODED STRING ARRAY
    $uid       = $json_decoded["uid"];
    $new_email = $json_decoded["newEmail"];
    
    // 3 - GET ALL E-MAILS USED BY ALL THE USERS
    // 3.1 - PREPARE THE QUERY
    $query = "SELECT DISTINCT email
              FROM T_USER";
    $statement = $conn->prepare($query);
    // 3.2 - EXECUTE THE QUERY
    $statement->execute();
    // 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = $statement->error;
    if ($error != "") {
        echo $error;
        return; }
    // 3.4 - COMPARE ALL USED E-MAILS FROM THE RESULTING QUERY TO NEW E-MAIL
    $statement->bind_result($email);
    while(mysqli_stmt_fetch($statement)) {
        if ($email == $new_email) {
            echo "E-mail is already in use.";
            return;
        }
    }
    $statement->close();  // Need to close statements if variable is to be recycled

    // 4 - UPDATE THE E-MAIL IF GOTTEN THIS FAR
    // 4.1 - PREPARE THE QUERY
    $query = "UPDATE T_USER
              SET email = ?
              WHERE uid = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("si", $new_email, $uid);
    // 4.2 - EXECUTE THE QUERY
    $statement->execute();
    // 4.3 - CHECK FOR ERROR, RETURN IT IF EXISTS, RETURN SUCCESS MESSAGE OTHERWISE
    $error = $statement->error;
    if ($error != "")
        echo $error;
    else
        echo "E-mail changed successfully.";

    $statement->close();  // Need to close statements if variable is to be recycled
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
    
    

/* FUNCTION: changePassword
 * DESCRIPTION: Sets the user's password to the specified value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function changePassword()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);

    // 2 - DETERMINE BUBBLES USER ID AND PASSWORD FROM JSON DECODED STRING ARRAY
    $uid          = $json_decoded["uid"];
    $new_password = $json_decoded["newPassword"];

    // 3.1 - PREPARE THE QUERY
    $query = "UPDATE T_USER
              SET password = ?
              WHERE uid = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("si", $new_password, $uid);
    // 3.2 - EXECUTE THE QUERY
    $statement->execute();
    // 3.3 - CHECK FOR ERROR, RETURN IT IF EXISTS, RETURN SUCCESS MESSAGE OTHERWISE
    $error = $statement->error;
    if ($error != "")
        echo $error;
    else
        echo "Password changed successfully.";

    $statement->close();  // Need to close statements if variable is to be recycled
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
    


/* FUNCTION: syncUserFacebook
 * DESCRIPTION: Connects a Facebook User ID to a Bubbles User.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function syncUserFacebook()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);
    
    // 2 - DETERMINE BUBBLES USER ID AND FACEBOOK USER ID FROM JSON DECODED STRING ARRAY
    $uid          = $json_decoded["uid"];
    $facebook_uid = $json_decoded["facebookUid"];//
            
    // 3 - PREPARE THE QUERY
    $query = "UPDATE T_USER SET facebook_uid = ? WHERE uid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("si", $facebook_uid, $uid);
    
    // 4 - EXECUTE THE QUERY
	$statement->execute();
	
    // 5 - RETURN RESULTING ERROR IF THERE IS ONE, OTHERWISE A SUCCESS MESSAGE, THEN CLOSE STATEMENT
	$error = $statement->error;
    if ($error != "")
        echo $error;
    else 
        echo "User updated successfully.";
        
    $statement->close();
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
 


/* FUNCTION: setUserAccountPrivacyLabel
 * DESCRIPTION: Sets the user's account privacy label to the specified value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function setUserAccountPrivacyLabel()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);

    // 2 - DETERMINE BUBBLES USER ID AND USER ACCOUNT PRIVACY LABEL FROM JSON DECODED STRING ARRAY
    $uid                        = $json_decoded["uid"];
    $user_account_privacy_label = $json_decoded["userAccountPrivacyLabel"];
    
    // 3 - GET THE CODE FOR THE PRIVACY LABEL
    $user_account_privacy_code = -1;
    // 3.1 - PREPARE THE QUERY
    $query = "SELECT privacy_code
              FROM T_PRIVACY
              WHERE privacy_label = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("s", $user_account_privacy_label);
    // 3.2 - EXECUTE THE QUERY
    $statement->execute();
    // 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = $statement->error;
    if ($error != "") {
        echo $error;
        return; }
    // 3.4 - STORE THE QUERY RESUlT IN A VARIABLE
    $statement->bind_result($user_account_privacy_code);
    $statement->fetch();
    $statement->close();  // Need to close statements if variable is to be recycled
    // 3.5 - CHECK IF VALUE EXISTS AND STOP IF IT DOESN'T
    if ($user_account_privacy_code == -1) {
        echo "PRIVACY LABEL IS NOT VALID.";
        return; }

    // 4 - PREPARE THE QUERY
    $query = "UPDATE T_USER SET user_account_privacy_code = ? WHERE uid = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("ii", $user_account_privacy_code, $uid);

    // 5 - EXECUTE THE QUERY
    $statement->execute();

    // 7 - RETURN RESULTING ERROR IF THERE IS ONE, OTHERWISE A SUCCESS MESSAGE, THEN CLOSE STATEMENT
    $error = $statement->error;
    if ($error != "")
        echo $error;
    else
        echo "User updated successfully.";

    $statement->close();  // Need to close statements if variable is to be recycled
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getUserData
 * DESCRIPTION: Retrieves and returns all of the user's information.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getUserData()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
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



/* FUNCTION: getUserFriendRequestUsers
 * DESCRIPTION: Retrieves and returns all of the users that sent friend requests
 * 				to the specified (logged in) user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getUserFriendRequestUsers()
{
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	// 1 - DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	
	// 2 - DETERMINE BUBBLES USER ID FROM THE JSON DECODED STRING ARRAY
	$uid = $json_decoded["uid"];
	
	// 3 - GET THE CODE FOR A SENT REQUEST
	$friendship_status_type_code = -1;
		// 3.1 - PREPARE THE QUERY
		$query = "SELECT friendship_status_type_code
				  FROM T_FRIENDSHIP_STATUS_TYPE
				  WHERE friendship_status_type_label = 'Request Sent'";
		$statement = $conn->prepare($query);
		// 3.2 - EXECUTE THE QUERY 
		$statement->execute();
		// 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
		$error = $statement->error;
		if ($error != "") {
			echo "MYSQL ERROR: " . $error;
			return; }
		// 3.4 - STORE THE QUERY RESULT IN A VARIABLE
		$statement->bind_result($friendship_status_type_code);
		$statement->fetch();
		$statement->close(); 	// Need to close statements if variable is to be recycled
		// 3.5 - CHECK IF VALUE EXISTS AND STOP IF IT DOESN'T
		if ($friendship_status_type_code == -1) {
			echo "FRIENDSHIP STATUS TYPE LABEL IS NOT VALID.";
			return;
		}
		
	// 4 - PREPARE THE QUERY
	$query = "SELECT uid_1
			  FROM R_FRIENDSHIP_STATUS
			  WHERE uid_2 = ? 
				AND friendship_status_type_code = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("ii", $uid, $friendship_status_type_code);
	
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

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>