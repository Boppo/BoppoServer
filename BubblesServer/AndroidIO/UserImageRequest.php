<?php

$function = $_GET['function'];

if ($function == 'getImagesByEid')
  getImagesByEid();
if ($function == 'getImagesByUid')
  getImagesByUid();
if ($function == 'getImageProfileMaxAmount')
  getImageProfileMaxAmount();
if ($function == 'setImage')
  setImage();
if ($function == 'uploadImage')
  uploadImage();

  
  


/* FUNCTION:    getImagesByEid
 * DESCRIPTION: Gets the images and their data by specified Eid, and optionally
 *              filters them to eventProfile or non-eventProfile images.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImagesByEid()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $eid = $json_decoded["eid"];
  $eventProfileIndicator = $json_decoded["eventProfileIndicator"];

  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
  $images = dbGetImagesByEid($eid, $eventProfileIndicator); 

  // RETURN THE EVENT ID
  echo json_encode($images);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

  

/* FUNCTION:    getImagesByUid
 * DESCRIPTION: Gets the images and their data by specified Uid, and optionally
 *              filters them to userProfile or non-userProfile images.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImagesByUid()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $uid                    = $json_decoded["uid"];
  $user_profile_indicator = $json_decoded["userProfileIndicator"];
  
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
  $images = dbGetImagesByUid($uid, $user_profile_indicator);

  // RETURN THE EVENT ID
  echo json_encode($images);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

  
  
/* FUNCTION:    getImageProfileMaxAmount
 * DESCRIPTION: Gets the integer that represents the maximum amount of allowed
 *              profile images. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImageProfileMaxAmount()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // FETCH THE DATA REPRESENTING THE MAXIMUM AMOUNT OF ALLOWED PROFILE IMAGES.
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $image_profile_max_amount = $array_gv["Image"]["ImageProfileMaxAmount"];
  
  // RETURN THE EVENT ID
    echo $image_profile_max_amount;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: setImage
 * DESCRIPTION: Updates the image's properties in the database and filesystem.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function setImage()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

  // ESTABLISH DATABASE CONNECTION //
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // DECODE INCOMING JSON CONTENTS //
  $json_decoded = json_decode(file_get_contents("php://input"), true);

  $uiid                        = $json_decoded["uiid"];
  $user_image_profile_sequence = $json_decoded["userImageProfileSequence"];
  $user_image_name             = $json_decoded["userImageName"];
  $user_image_privacy_label    = $json_decoded["userImagePrivacyLabel"];
  $user_image_gps_latitude     = $json_decoded["userImageGpsLatitude"];
  $user_image_gps_longitude    = $json_decoded["userImageGpsLongitude"];
  
  $set_or_not = $json_decoded["setOrNot"];
    
  // MAKE SURE THAT A VALID USER IMAGE IDENTIFIER WAS PROVIDED
  if ($uiid <= 0) {
    echo "ERROR: Incorrect user image identifier specified.";
    return; }
    
  // ENCODE THE PRIVACY LABEL
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Privacy.php';
  $set_or_not["userImagePrivacyCode"] = $set_or_not["userImagePrivacyLabel"];
  unset($set_or_not["userImagePrivacyLabel"]);
  $user_image_privacy_code = fetchPrivacyCode($user_image_privacy_label);
  if (!($json_decoded["userImagePrivacyLabel"] == null || $user_image_privacy_code != null)) {
    echo "ERROR: Incorrect user image privacy specified.";
    return; }

  // SEND THE NEW VALUES IN AN EVENT OBJECT TO THE CORRESPONDING DBIO METHOD
  $image = array
  (
    "uiid" => $uiid,
    "userImageProfileSequence" => $user_image_profile_sequence,
    "userImageName" => $user_image_name,
    "userImagePrivacyCode" => $user_image_privacy_code,
    "userImageGpsLatitude" => $user_image_gps_latitude,
    "userImageGpsLongitude" => $user_image_gps_longitude
  );
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
  $response = dbSetImage($image, $set_or_not);
  
  echo $response;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: uploadImage
 * DESCRIPTION: Uploads an image into the database and filesystem.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function uploadImage()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //
  
  // ESTABLISH DATABASE CONNECTION //
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // DECODE INCOMING JSON CONTENTS //
  $json_decoded = json_decode(file_get_contents("php://input"), true);

  $uid = $json_decoded["uid"];
  $user_image_profile_sequence = $json_decoded["userImageProfileSequence"];
  $user_image_name = $json_decoded["userImageName"];
  $user_image_privacy_label = $json_decoded["userImagePrivacyLabel"];
  $user_image_gps_latitude = $json_decoded["userImageGpsLatitude"];
  $user_image_gps_longitude = $json_decoded["userImageGpsLongitude"];
  $user_image = $json_decoded["userImage"];
  
  // ENCODE THE LABELS INTO CODES AND GET THE NEXT USER IMAGE SEQUENCE NUMBER //
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Privacy.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
  $user_image_privacy_code = fetchPrivacyCode($user_image_privacy_label);
  $user_image_sequence = fetchUserImageSequence($uid);
  
  // UPLOAD THE IMAGE TO THE DATABASE // 
  //echo "USER_IMAGE_SEQUENCE: " . $user_image_sequence; 
  $query = "INSERT INTO T_USER_IMAGE (uid, user_image_profile_sequence, user_image_sequence, user_image_name, 
              user_image_privacy_code, user_image_gps_latitude, user_image_gps_longitude)
            VALUES (?, ?, ?, ?, ?, ?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("iiissdd", $uid, $user_image_profile_sequence, $user_image_sequence, 
    $user_image_name, $user_image_privacy_code, 
    $user_image_gps_latitude, $user_image_gps_longitude);
  $statement->execute();
  $statement->error;
  
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS //
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }
  
  // DECODE THE BINARY-ENCODED IMAGE AND CREATE FOLDER & FILE STRUCTURES FOR IT //
  $decodedUserImage = base64_decode("$user_image");
  if (!file_exists("/var/www/html/Bubbles/Uploads/" . $uid))
    mkdir("/var/www/html/Bubbles/Uploads/" . $uid, 0777, true);
  if (!file_exists("/var/www/html/Bubbles/Uploads/" . $uid . "/" . $user_image_sequence))
    mkdir("/var/www/html/Bubbles/Uploads/" . $uid . "/" . $user_image_sequence, 0777, true);
  file_put_contents("/var/www/html/Bubbles/Uploads/" . $uid . "/" .
    $user_image_sequence . "/" . $user_image_name, $decodedUserImage);
  
  echo "Success: " . $conn->insert_id; 
  
  return;
} 
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>