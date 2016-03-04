<?php

$function = $_GET['function'];

if ($function == 'deleteImage')
    deleteImage();
elseif ($function == 'uploadImage')
    uploadImage();
elseif ($function == 'getImages')
    getImages();
elseif ($function == 'setUserImagePurpose')
    setUserImagePurpose();
    


/* FUNCTION: deleteImage
 * DESCRIPTION: Deletes the specified image of the specified user
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function deleteImage()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);

    // 2 - DETERMINE BUBBLES USER ID, AND USER IMAGE PURPOSE LABEL
    //     FROM THE JSON DECODED STRING ARRAY
    $uid  = $json_decoded["uid"];
    $uiid = $json_decoded["uiid"];
    
    // 3 - GET THE NAME OF THE IMAGE
    // 3.1 - PREPARE THE QUERY
    $query = "SELECT user_image_name
              FROM T_USER_IMAGE
              WHERE uid = ? AND uiid = ?";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "ii", $uid, $uiid);
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
    $query = "DELETE FROM T_USER_IMAGE WHERE uid = ? AND uiid = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("ii", $uid, $uiid);

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
    if (file_exists("/var/www/Bubbles/Uploads/$uid/$uiid/$user_image_name"))
        unlink("/var/www/Bubbles/Uploads/$uid/$uiid/$user_image_name");
    if (is_dir("/var/www/Bubbles/Uploads/$uid/$uiid"))
        rmdir("/var/www/Bubbles/Uploads/$uid/$uiid");
        
    $statement->close();  // Need to close statements if variable is to be recycled
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getImagePaths
 * DESCRIPTION: Returns all of the image relative paths for the provided user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImages()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    //     THIS WILL GIVE THE LOGGED-IN USER'S ID
    $json_decoded = json_decode(file_get_contents("php://input"), true);
    $uid = $json_decoded["uid"];
    $image_purpose_label = $json_decoded["imagePurposeLabel"];
    
    // 2 - GET THE CODE FOR THE PURPOSE OF THE IMAGE
    // 2.1 - PREPARE THE QUERY
    $query = "SELECT image_purpose_code 
              FROM T_IMAGE_PURPOSE 
              WHERE image_purpose_label = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("s", $image_purpose_label);
    // 2.2 - EXECUTE THE QUERY
    $statement->execute();
    // 2.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = $statement->error;
    if ($error != "") {
        echo $error;
        return; }
    // 2.4 - STORE THE QUERY RESUlT IN A VARIABE
    $statement->bind_result($user_image_purpose_code);
    $statement->fetch();
    $statement->close();  // Need to close statements if variable is to be recycled
    
    // 3 - CHECK IF THE USER PROVIDED IS VALID
    // 3.1 - END IF PROVIDED UID IS NOT A NUMBER
    if (!is_numeric($uid))
    {
        echo "UID IS NOT A NUMBER.";
        return;
    }
    // 3.2 - END IF PROVIDED UID IS NOT A USER
    $uid_user = -1;
    $query = "SELECT uid
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
    
    $images = array();
        
    // 4 - PERFORM THE RIGHT ACTION GIVEN THE IMAGE PURPOSE
    // 4.1 - IF THE PURPOSE CODE EXISTS
    if ($user_image_purpose_code != "") {
        // 4.1.1 - CHECK TABLE T_IMAGE FOR THE DATA TO CREATE THE RELATIVE PATHS
        $uid_user                 = -1;
        $uiid                     = -1;
        $user_image_name          = "";
        $user_image_privacy_label = "";
        $user_image_purpose_label = "";
        $user_image_gps_latitude  = -1.0;
        $user_image_gps_longitude = -1.0;
        $query = "SELECT uid, uiid, user_image_name, privacy_label, image_purpose_label, 
                    user_image_gps_latitude, user_image_gps_longitude
                  FROM T_USER_IMAGE, T_PRIVACY, T_IMAGE_PURPOSE
                  WHERE 
                    uid = ? AND
                    user_image_purpose_code = ? AND 
                    T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code AND
                    T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code";
        $statement = $conn->prepare($query);
        $statement->bind_param("is", $uid, $user_image_purpose_code);
        $statement->execute();
        $statement->bind_result($uid_user, $uiid, $user_image_name, $user_image_privacy_label, $user_image_purpose_label,
            $user_image_gps_latitude, $user_image_gps_longitude);
        while ($statement->fetch()) 
        {
            $image = array(
                "userImagePath" => $uid_user . "/" . $uiid . "/" . $user_image_name,
                "userImagePrivacyLabel" => $user_image_privacy_label,
                "userImagePurposeLabel" => $user_image_purpose_label, 
                "userImageGpsLatitude"  => $user_image_gps_latitude, 
                "userImageGpsLongitude" => $user_image_gps_longitude
            );
            array_push($images, $image);
        }
        $statement->close();
    }
    // 4.2 - IF THE PURPOSE IS "All" 
    elseif ($image_purpose_label == "All") {
        // 4.1.1 - CHECK TABLE T_IMAGE FOR THE DATA TO CREATE THE RELATIVE PATHS
        $uid_user                 = -1;
        $uiid                     = -1;
        $user_image_name          = "";
        $user_image_privacy_label = "";
        $user_image_purpose_label = "";
        $user_image_gps_latitude  = -1.0;
        $user_image_gps_longitude = -1.0;
        $query = "SELECT uid, uiid, user_image_name, privacy_label, image_purpose_label, 
                    user_image_gps_latitude, user_image_gps_longitude
                  FROM T_USER_IMAGE, T_PRIVACY, T_IMAGE_PURPOSE
                  WHERE 
                    uid = ? AND
                    T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code AND
                    T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code";
        $statement = $conn->prepare($query);
        $statement->bind_param("i", $uid);
        $statement->execute();
        $statement->bind_result($uid_user, $uiid, $user_image_name, $user_image_privacy_label, $user_image_purpose_label,
            $user_image_gps_latitude, $user_image_gps_longitude);
        while ($statement->fetch()) 
        {
            $image = array(
                "userImagePath" => $uid_user . "/" . $uiid . "/" . $user_image_name,
                "userImagePrivacyLabel" => $user_image_privacy_label,
                "userImagePurposeLabel" => $user_image_purpose_label, 
                "userImageGpsLatitude"  => $user_image_gps_latitude, 
                "userImageGpsLongitude" => $user_image_gps_longitude
            );
            array_push($images, $image);
        }
        $statement->close();
    }
    // 4.3 - IF THE PURPOSE DOES NOT EXIST
    else {
        echo "PURPOSE DOES NOT EXIST IN THE DATABASE.";
        return;
    }

    // 5 - ENCODE THE DATA INTO JSON STRING AND RETURN
    echo json_encode($images);
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
function uploadImage()
{
    // 1 - ESTABLISH DATABASE CONNECTION
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    
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
    $query = "SELECT (MAX(UIID)+1) FROM T_USER_IMAGE WHERE UID = ?";
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
    mysqli_stmt_bind_result($statement, $uiid);
    mysqli_stmt_fetch($statement);
    mysqli_stmt_close($statement);  // Need to close statements if variable is to be recycled
    // 6.5 - INITIALIZE THE SEQUENCE IF THIS IS THE FIRST FILE FOR THE USER
    if ($uiid == "") {
        $uiid = 1;
    }
    
    
    // 7 - CREATE COORDINATES IN THE GEOLOCATION TABLE BEFORE INSERTING THE IMAGE
    // 7.1 - PREPARE THE QUERY
    $query = "INSERT IGNORE INTO T_GEOLOCATION (gps_latitude, gps_longitude)
			  VALUES (?, ?)";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "dd", $user_image_gps_latitude, $user_image_gps_longitude);
    // 7.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    $error = mysqli_stmt_error($statement);
    // 7.3 - CHECK FOR ERROR AND STOP IF EXISTS
    if ($error != "")
    {
    	echo $error;
    	return;
    }
    

    // 8 - STORE THE UID, IID (IMAGE ID), AND FILE NAME
    // 8.1 - PREPARE THE QUERY
    $query = "INSERT INTO T_USER_IMAGE (uid, uiid, user_image_name, user_image_purpose_code, user_image_privacy_code, 
                user_image_gps_latitude, user_image_gps_longitude)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
    $statement = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($statement, "iisiidd", 
        $uid, $uiid, $user_image_name, $user_image_purpose_code, $user_image_privacy_code, 
        $user_image_gps_latitude, $user_image_gps_longitude);
    // 8.2 - EXECUTE THE QUERY
    mysqli_stmt_execute($statement);
    $error = mysqli_stmt_error($statement);
    // 8.3 - CHECK FOR ERROR AND STOP IF EXISTS
    if ($error != "") 
    {
        echo $error;
        return; 
    }

    // 9 - DECODE THE BINARY-ENCODED IMAGE
    $decodedUserImage = base64_decode("$user_image");
    // 10 - CREATE FOLDERS FOR UPLOADED IMAGE
    if (!file_exists("/var/www/Bubbles/Uploads/" . $uid))
        mkdir("/var/www/Bubbles/Uploads/" . $uid, 0777, true);
    if (!file_exists("/var/www/Bubbles/Uploads/" . $uid . "/" . $uiid))
        mkdir("/var/www/Bubbles/Uploads/" . $uid . "/" . $uiid, 0777, true);
    // 11 - STORE UPLOADED IMAGE IN DIRECTORY DETERMINED BY PATH, UID, AND IID
    file_put_contents("/var/www/Bubbles/Uploads/" . $uid . "/" .
        $uiid . "/" . $user_image_name . ".jpg", $decodedUserImage);

    echo $uiid;
    return;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: setUserImagePurpse
 * DESCRIPTION: Sets the purpose of the user's image to the specified purpose
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function setUserImagePurpose()
{
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    // 1 - DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);

    // 2 - DETERMINE BUBBLES USER ID, USER IMAGE ID, AND USER IMAGE PURPOSE LABEL 
    //     FROM THE JSON DECODED STRING ARRAY
    $uid                      = $json_decoded["uid"];
    $uiid                     = $json_decoded["uiid"];
    $user_image_purpose_label = $json_decoded["userImagePurposeLabel"];

    // 3 - GET THE CODE FOR THE PRIVACY LABEL
    $user_image_purpose_code = -1;
    // 3.1 - PREPARE THE QUERY
    $query = "SELECT image_purpose_code
              FROM T_IMAGE_PURPOSE
              WHERE image_purpose_label = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("s", $user_image_purpose_label);
    // 3.2 - EXECUTE THE QUERY
    $statement->execute();
    // 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
    $error = $statement->error;
    if ($error != "") {
        echo $error;
        return; }
        // 3.4 - STORE THE QUERY RESUlT IN A VARIABLE
        $statement->bind_result($user_image_purpose_code);
        $statement->fetch();
        $statement->close();  // Need to close statements if variable is to be recycled
        // 3.5 - CHECK IF VALUE EXISTS AND STOP IF IT DOESN'T
        if ($user_image_purpose_code == -1) {
            echo "Purpose label is not valid.";
            return; 
        }

        // 4 - PREPARE THE QUERY
        $query = "UPDATE T_USER_IMAGE SET user_image_purpose_code = ? 
                  WHERE uid = ? AND uiid = ?";
        $statement = $conn->prepare($query);
        $statement->bind_param("iii", $user_image_purpose_code, $uid, $uiid);

        // 5 - EXECUTE THE QUERY
        $statement->execute();

        // 6 - RETURN RESULTING ERROR IF THERE IS ONE, OTHERWISE A SUCCESS MESSAGE, THEN CLOSE STATEMENT
        $error = $statement->error;
        if ($error != "")
            echo $error;
        else
            echo "User image updated successfully.";

        $statement->close();  // Need to close statements if variable is to be recycled
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>