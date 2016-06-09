<?php

$function = $_GET['function'];

if ($function == "addImagesToEvent")
	addImagesToEvent();
if ($function == 'getImagesByEid')
	getImagesByEid();
if ($function == 'getImagesByUidAndPurpose')
	getImagesByUidAndPurpose();
if ($function == 'getImagesByPrivacyAndPurpose')
	getImagesByPrivacyAndPurpose();
if ($function == 'getImageProfileMaxAmount')
	getImageProfileMaxAmount();
if ($function == 'uploadImage')
	uploadImage();

	
	
/* FUNCTION: addImagesToEvent
 * DESCRIPTION: Adds the images with the uiids in the specified list to the
 *              specified event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function addImagesToEvent()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// DECODE JSON STRING //
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES //
	$eid   = $json_decoded["eid"];
	$uid   = $json_decoded["uid"];
	$uiids = $json_decoded["uiids"];

	// FOR EVERY USER IMAGE IDENTIFIER (UIID), ADD IT IMAGE TO THE EVENT //
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

	$responses = array();
	foreach($uiids as $uiid)
	{
		$response = addImageToEvent($eid, $uid, $uiid);
		array_push($responses, $response);
	}

	echo json_encode($responses);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getImagesByEid
 * DESCRIPTION: Gets the images and their data by specified Eid.
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

	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
	$images = fetchImagesByEid($eid);

	// RETURN THE EVENT ID
	echo json_encode($images);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

	

/* FUNCTION: getImagesByUidAndPurpose
 * DESCRIPTION: Gets the images and their data by specified Uid and Purpose.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImagesByUidAndPurpose()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid                 = $json_decoded["uid"];
	$image_purpose_label = $json_decoded["imagePurposeLabel"];
	
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
	$images = fetchImagesByUidAndPurpose($uid, $image_purpose_label);

	// RETURN THE EVENT ID
	echo json_encode($images);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getImagesByPrivacyAndPurpose
 * DESCRIPTION: Gets the images and their data by specified privacy and purpose.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImagesByPrivacyAndPurpose()
{
	// THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	// END. //

	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$image_privacy_label = $json_decoded["imagePrivacyLabel"];
	$image_purpose_label = $json_decoded["imagePurposeLabel"];
	
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
	$images = fetchImagesByPrivacyAndPurpose($image_privacy_label, $image_purpose_label);

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
	$user_image_name = $json_decoded["userImageName"];
	$user_image_purpose_label = $json_decoded["userImagePurposeLabel"];
	$user_image_privacy_label = $json_decoded["userImagePrivacyLabel"];
	$user_image_gps_latitude = $json_decoded["userImageGpsLatitude"];
	$user_image_gps_longitude = $json_decoded["userImageGpsLongitude"];
	$user_image = $json_decoded["userImage"];
	
	// ENCODE THE LABELS INTO CODES AND GET THE NEXT USER IMAGE SEQUENCE NUMBER //
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Privacy.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/ImagePurpose.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
	$user_image_purpose_code = fetchImagePurposeCode($user_image_purpose_label); 
	$user_image_privacy_code = fetchPrivacyCode($user_image_privacy_label);
	$user_image_sequence = fetchUserImageSequence($uid);
	
	// UPLOAD THE IMAGE TO THE DATABASE // 
	$query = "INSERT INTO T_USER_IMAGE (uid, user_image_sequence, user_image_name, 
			    user_image_purpose_code, user_image_privacy_code,
                user_image_gps_latitude, user_image_gps_longitude)
              VALUES (?, ?, ?, ?, ?, ?, ?)";
	$statement = $conn->prepare($query);
	$statement->bind_param("iisssdd", $uid, $user_image_sequence, $user_image_name, $user_image_purpose_code, 
		$user_image_privacy_code, $user_image_gps_latitude, $user_image_gps_longitude);
	$statement->execute();
	$statement->error;
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS //
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DECODE THE BINARY-ENCODED IMAGE AND CREATE FOLDER & FILE STRUCTURES FOR IT //
	$decodedUserImage = base64_decode("$user_image");
	if (!file_exists("/var/www/Bubbles/Uploads/" . $uid))
		mkdir("/var/www/Bubbles/Uploads/" . $uid, 0777, true);
	if (!file_exists("/var/www/Bubbles/Uploads/" . $uid . "/" . $user_image_sequence))
		mkdir("/var/www/Bubbles/Uploads/" . $uid . "/" . $user_image_sequence, 0777, true);
	file_put_contents("/var/www/Bubbles/Uploads/" . $uid . "/" .
		$user_image_sequence . "/" . $user_image_name . ".jpg", $decodedUserImage);
	
	echo $user_image_sequence; 
	
	return;
} 
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>