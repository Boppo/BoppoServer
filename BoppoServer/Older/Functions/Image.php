<?php

$function = $_GET['function'];

if ($function == 'deleteImage')
    deleteImage();
elseif ($function == 'uploadImage')
    uploadImage();



/* FUNCTION: deleteImage
 * DESCRIPTION: Deletes the specified image of the specified user
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function deleteImage()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);

    // 2 - DETERMINE boppo USER ID FROM THE JSON DECODED STRING ARRAY
    $uid                 = $json_decoded["uid"];
    $user_image_sequence = $json_decoded["userImageSequence"];
    ////$uid = 1;
    ////$user_image_sequence = 106;
    
    // 3 - GET THE NAME OF THE IMAGE
    // 3.1 - PREPARE THE QUERY
    $query = "SELECT user_image_name
              FROM T_USER_IMAGE
              WHERE uid = ? AND user_image_sequence = ?";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "ii", $uid, $user_image_sequence);
    // 3.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = mysqli_stmt_error($statement);
    if ($error != "") {
        echo $error;
        return; }
    // 3.4 - STORE THE QUERY RESUlT IN A VARIABE
    mysqli_stmt_bind_result($statement, $user_image_name);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled

    // 4 - PREPARE THE QUERY
    $query = "DELETE FROM T_USER_IMAGE WHERE uid = ? AND user_image_sequence = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("ii", $uid, $user_image_sequence);

    // 5 - EXECUTE THE QUERY
    $statement->execute();

    // 6 - RETURN RESULTING ERROR IF THERE IS ONE, OTHERWISE A SUCCESS MESSAGE, THEN CLOSE STATEMENT
    $error = $statement->error;
    if ($error != "")
        echo $error;
    else
        echo "User image deleted successfully.";
    
    // 7 - IF THE FILE NAME DOES NOT END WITH AN EXTENSION, PREFIX IT WITH .JPG
    if (strpos($user_image_name, ".") == false)
        $user_image_name = $user_image_name . ".jpg";
            
    // 7 - DELETE THE FILE AND ITS WRAPPER FOLDER FROM THE DISK
    if (file_exists("/var/www/html/boppo/Uploads/$uid/$user_image_sequence/$user_image_name"))
        unlink("/var/www/html/boppo/Uploads/$uid/$user_image_sequence/$user_image_name");
    if (is_dir("/var/www/html/boppo/Uploads/$uid/$user_image_sequence"))
        rmdir("/var/www/html/boppo/Uploads/$uid/$user_image_sequence");
        
    $statement->close();  // Need to close statements if variable is to be recycled
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
 


/* FUNCTION: uploadImage
 * DESCRIPTION: Uploads an image with an input name of an input purpose for the 
 * input user encoded in the input string.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/**** 
 **** DEPRECATED
 ****
 **
function uploadImage()
{
    // 1 - ESTABLISH DATABASE CONNECTION
    require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
    
    // 2 - DECODE INCOMING JSON CONTENTS
    $json_decoded = json_decode(file_get_contents("php://input"), true);
    
    // 3 - DETERMINE FILE USER (OWNER), FILE NAME, AND ENCODED
    //     IMAGE FROM JSON CONTENTS
    $uid = $json_decoded["uid"];
    $user_image_name = $json_decoded["userImageName"];
    $user_image_purpose_label = $json_decoded["userImagePurposeLabel"];
    $user_image_privacy_label = $json_decoded["userImagePrivacyLabel"];
    $user_image_gps_latitude = $json_decoded["userImageGpsLatitude"];
    $user_image_gps_longitude = $json_decoded["userImageGpsLongitude"];
    $user_image = $json_decoded["userImage"];
    
    
    $error = "";
    
    // 4 - GET THE CODE FOR THE PURPOSE OF THE IMAGE
    // 4.1 - PREPARE THE QUERY
    $query = "SELECT image_purpose_code 
              FROM T_IMAGE_PURPOSE 
              WHERE image_purpose_label = ?";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "s", $user_image_purpose_label);
    // 4.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 4.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = mysqli_stmt_error($statement);
    if ($error != "") 
    {
        echo $error;
        return; 
    }
    // 4.4 - STORE THE QUERY RESUlT IN A VARIABE
    mysqli_stmt_bind_result($statement, $user_image_purpose_code);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 4.5 - INITIALIZE THE SEQUENCE IF THIS IS THE FIRST FILE FOR THE USER
    if ($user_image_purpose_code == "") 
    {
        echo "PURPOSE DOES NOT EXIST IN THE DATABASE.";
        return;
    }    
        
    // 5 - GET THE CODE FOR THE PRIVACY OF THE IMAGE
    // 5.1 - PREPARE THE QUERY
    $query = "SELECT privacy_code 
              FROM T_PRIVACY 
              WHERE privacy_label = ?";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "s", $user_image_privacy_label);
    // 5.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 5.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = mysqli_stmt_error($statement);
    if ($error != "") 
    {
        echo $error;
        return; 
    }
    // 5.4 - STORE THE QUERY RESUlT IN A VARIABE
    mysqli_stmt_bind_result($statement, $user_image_privacy_code);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 5.5 - INITIALIZE THE SEQUENCE IF THIS IS THE FIRST FILE FOR THE USER
    if ($user_image_privacy_code == "") 
    {
        echo "PRIVACY DOES NOT EXIST IN THE DATABASE.";
        return;
    }
    
    // 6 - GET THE NEXT SEQUENCE NUMBER OF THE FILE ID
    // 6.1 - PREPARE THE QUERY
    $query = "SELECT (MAX(user_image_sequence)+1) FROM T_USER_IMAGE WHERE UID = ?";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "i", $uid);
    // 6.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    // 6.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = mysqli_stmt_error($statement);
    if ($error != "") 
    {
        echo $error;
        return; 
    }
    // 6.4 - STORE THE QUERY RESUlT IN A VARIABE
    mysqli_stmt_bind_result($statement, $user_image_sequence);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 6.5 - INITIALIZE THE SEQUENCE IF THIS IS THE FIRST FILE FOR THE USER
    if ($user_image_sequence == "") {
        $user_image_sequence = 1;
    }
   
    

    // 7 - STORE THE UID, IID (IMAGE ID), AND FILE NAME
    // 7.1 - PREPARE THE QUERY
    $query = "INSERT INTO T_USER_IMAGE (uid, user_image_sequence, user_image_name, user_image_purpose_code, user_image_privacy_code, 
                user_image_gps_latitude, user_image_gps_longitude)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "iisiidd", 
        $uid, $user_image_sequence, $user_image_name, $user_image_purpose_code, $user_image_privacy_code, 
        $user_image_gps_latitude, $user_image_gps_longitude);
    // 7.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    $error = mysqli_stmt_error($statement);
    // 7.3 - CHECK FOR ERROR AND STOP IF EXISTS
    if ($error != "") 
    {
        echo $error;
        return; 
    }

    // 8 - DECODE THE BINARY-ENCODED IMAGE
    $decodedUserImage = base64_decode("$user_image");
    // 9 - CREATE FOLDERS FOR UPLOADED IMAGE
    if (!file_exists("/var/www/boppo/Uploads/" . $uid))
        mkdir("/var/www/boppo/Uploads/" . $uid, 0777, true);
    if (!file_exists("/var/www/boppo/Uploads/" . $uid . "/" . $user_image_sequence))
        mkdir("/var/www/boppo/Uploads/" . $uid . "/" . $user_image_sequence, 0777, true);
    // 10 - STORE UPLOADED IMAGE IN DIRECTORY DETERMINED BY PATH, UID, AND IID
    file_put_contents("/var/www/boppo/Uploads/" . $uid . "/" .
        $user_image_sequence . "/" . $user_image_name . ".jpg", $decodedUserImage);

    echo $user_image_sequence;
    return;
}
*/
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>