<?php

/* FUNCTION: fetchUserImageSequence
 * DESCRIPTION: Retrieves and returns the sequence number of the next possible 
 *              imamge (i.e. the sequence of the image that would be uploaded).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchUserImageSequence($uid)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// ACQUIRE THE USER IMAGE SEQUENCE
	$query = "SELECT (MAX(user_image_sequence)+1) FROM T_USER_IMAGE WHERE UID = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid);
	$statement->execute();
	$statement->error;

	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE USER IMAGE SEQUENCE
	$user_image_sequence = 1;
	$statement->bind_result($user_image_sequence);
	$statement->fetch();
	$statement->close();

	// RETURN THE USER IMAGE SEQUENCE
	return $user_image_sequence;
}



/* FUNCTION: fetchImageEncoded
 * DESCRIPTION: Gets the image and its data by specified User Image Identifier
 * (uiid).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImageEncoded($uiid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT  uiid, uid, user_image_sequence, user_image_profile_sequence,
                    user_image_name, user_image_privacy_code, user_image_purpose_code,
                    user_image_gps_latitude, user_image_gps_longitude, user_image_upload_timestamp
            FROM    T_USER_IMAGE
            WHERE
                    uiid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uiid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence,
      $user_image_name, $user_image_privacy_code, $user_image_purpose_code,
      $user_image_gps_latitude, $user_image_gps_longitude, $user_image_upload_timestamp);
  $statement->fetch();

  $image = array
  (
      "uiid" => $uiid,
      "uid" => $uid,
      "userImageSequence" => $user_image_sequence,
      "userImageProfileSequence" => $user_image_profile_sequence,
      "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name,
      "userImageName" => $user_image_name,
      "userImagePrivacyCode" => $user_image_privacy_code,
      "userImagePurposeCode" => $user_image_purpose_code,
      "userImageGpsLatitude" => $user_image_gps_latitude,
      "userImageGpsLongitude" => $user_image_gps_longitude,
      "userImageUploadTimestamp" => $user_image_upload_timestamp
  );

  $statement->close();

  return $image;
}



/* FUNCTION: fetchImagesByEid
 * DESCRIPTION: Gets the images and their data by specified Eid.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImages($uiid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT  T_USER_IMAGE.uiid, T_USER_IMAGE.uid, user_image_sequence, user_image_profile_sequence, 
                    user_image_name, privacy_label, image_purpose_label, 
                    user_image_gps_latitude, user_image_gps_longitude, user_image_upload_timestamp
            FROM    T_USER_IMAGE
                    LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
                    LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
                    LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
            WHERE
                    uiid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uiid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
      $user_image_name, $user_image_privacy_label, $user_image_purpose_label,
      $user_image_gps_latitude, $user_image_gps_longitude, $user_image_upload_timestamp);
  $statement->fetch();
  
  $image = array
  (
    "uiid" => $uiid,
    "uid" => $uid,
    "userImageSequence" => $user_image_sequence,
    "userImageProfileSequence" => $user_image_profile_sequence, 
    "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name,
    "userImageName" => $user_image_name,
    "userImagePrivacyLabel" => $user_image_privacy_label,
    "userImagePurposeLabel" => $user_image_purpose_label,
    "userImageGpsLatitude" => $user_image_gps_latitude,
    "userImageGpsLongitude" => $user_image_gps_longitude,
    "userImageUploadTimestamp" => $user_image_upload_timestamp
  );

  $statement->close();

  return $image;
}



/* FUNCTION: fetchImagesByEid
 * DESCRIPTION: Gets the images and their data by specified Eid.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImagesByEid($eid, $euiProfileIndicator)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php'; 
	
	if ($euiProfileIndicator === true)
	  $euiProfileSequenceSubquery = " AND eui_event_profile_sequence IS NOT NULL";
	else if ($euiProfileIndicator === false)
	  $euiProfileSequenceSubquery = " AND eui_event_profile_sequence IS NULL"; 
	else 
	  $euiProfileSequenceSubquery = "";
	// EXECUTE THE QUERY
	$query = "SELECT T_USER_IMAGE.uiid, T_USER_IMAGE.uid, user_image_sequence, user_image_profile_sequence, 
	                 user_image_name, privacy_label, image_purpose_label, 
	                 user_image_gps_latitude, user_image_gps_longitude, user_image_upload_timestamp
			  FROM   T_USER_IMAGE
				  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
				  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
					 LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
			  WHERE
				  	 eid = ?" . $euiProfileSequenceSubquery;
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$uiid = -1;
	$uid = -1;
	$user_image_sequence = -1;
	$user_image_profile_sequence = -1;
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
	    $user_image_name, $user_image_privacy_label, $user_image_purpose_label,
		$user_image_gps_latitude, $user_image_gps_longitude, $user_image_upload_timestamp);

	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"uiid" => $uiid,
			"uid" => $uid,
			"userImageSequence" => $user_image_sequence,
		    "userImageProfileSequence" => $user_image_profile_sequence, 
			"userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name,
			"userImageName" => $user_image_name,
			"userImagePrivacyLabel" => $user_image_privacy_label,
			"userImagePurposeLabel" => $user_image_purpose_label,
			"userImageGpsLatitude" => $user_image_gps_latitude,
			"userImageGpsLongitude" => $user_image_gps_longitude, 
			"userImageUploadTimestamp" => $user_image_upload_timestamp
		);
		$imageData = array
		(
			"image" => $image,
		);
		array_push($imageList, $imageData);
	}

	$statement->close();

	return $imageList;
}



/* FUNCTION: fetchImagesByUidAndPurpose
 * DESCRIPTION: Fetches the data of all of the images that are of a specified
 *              uid and a specified purpose.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImagesByUidAndPurpose($uid, $image_purpose_label, $event_indicator)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	if ($event_indicator === true)
		$query = "SELECT DISTINCT T_USER_IMAGE.uiid, T_USER_IMAGE.uid, user_image_sequence, user_image_profile_sequence, 
						 user_image_name, privacy_label, image_purpose_label,
				     	 user_image_gps_latitude, user_image_gps_longitude
				  FROM   T_USER_IMAGE
					  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
					  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
				  		 INNER JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
				  WHERE  T_USER_IMAGE.uid = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	else if ($event_indicator === false)
		$query = "SELECT DISTINCT T_USER_IMAGE.uiid, T_USER_IMAGE.uid, user_image_sequence, user_image_profile_sequence, 
						 user_image_name, privacy_label, image_purpose_label,
				     	 user_image_gps_latitude, user_image_gps_longitude
				  FROM   T_USER_IMAGE
					  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
					  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
				  		 LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
				  WHERE  R_EVENT_USER_IMAGE.uiid IS NULL AND
				         T_USER_IMAGE.uid = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	else if (!$event_indicator)
		$query = "SELECT DISTINCT uiid, uid, user_image_sequence, user_image_profile_sequence, user_image_name, privacy_label, 
						 image_purpose_label, user_image_gps_latitude, user_image_gps_longitude
				  FROM   T_USER_IMAGE
					  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
					  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
				  WHERE  uid = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $uid, $image_purpose_label);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$uiid = -1;
	$uid = -1;
	$user_image_sequence = -1;
	$user_image_profile_sequence = -1;
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
	    $user_image_name, $user_image_privacy_label, $user_image_purpose_label,
		$user_image_gps_latitude, $user_image_gps_longitude);

	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"uiid" => $uiid, 
			"uid" => $uid, 
			"userImageSequence" => $user_image_sequence, 
		    "userImageProfileSequence" => $user_image_profile_sequence, 
			"userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name, 
			"userImageName" => $user_image_name, 
			"userImagePrivacyLabel" => $user_image_privacy_label,
			"userImagePurposeLabel" => $user_image_purpose_label,
			"userImageGpsLatitude" => $user_image_gps_latitude,
			"userImageGpsLongitude" => $user_image_gps_longitude
		);
		array_push($imageList, $image);
	}

	$statement->close();

	return $imageList;
}



/* FUNCTION: fetchImagesByPrivacyAndPurpose 
 * DESCRIPTION: Fetches the data of all of the images that are of a specified 
 *              privacy and a specified purpose.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImagesByPrivacyAndPurpose($imagePrivacyLabel, $imagePurposeLabel, $event_indicator)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	if ($event_indicator === true)
		$query = "SELECT T_USER_IMAGE.uiid, uid, user_image_sequence, user_image_profile_sequence, 
		                 user_image_name, privacy_label, image_purpose_label,
				     	 user_image_gps_latitude, user_image_gps_longitude
				  FROM   T_USER_IMAGE
					  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
					  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
				  		 INNER JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
				  WHERE
					  	 T_PRIVACY.privacy_label = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	else if ($event_indicator === false)
		$query = "SELECT T_USER_IMAGE.uiid, uid, user_image_sequence, user_image_profile_sequence, 
		                 user_image_name, privacy_label, image_purpose_label,
				     	 user_image_gps_latitude, user_image_gps_longitude
				  FROM   T_USER_IMAGE
					  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
					  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
				  		 LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
				  WHERE  R_EVENT_USER_IMAGE.uiid IS NULL AND
					  	 T_PRIVACY.privacy_label = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	else if (!$event_indicator)
		$query = "SELECT uiid, uid, user_image_sequence, user_image_profile_sequence, 
		                 user_image_name, privacy_label, image_purpose_label, 
					     user_image_gps_latitude, user_image_gps_longitude
				  FROM   T_USER_IMAGE
					  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code 
					  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
				  WHERE 
					  	 T_PRIVACY.privacy_label = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("ss", $imagePrivacyLabel, $imagePurposeLabel);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$uiid = -1;
	$uid = -1;
	$user_image_sequence = -1;
	$user_image_profile_sequence = -1;
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
	    $user_image_name, $user_image_privacy_label, $user_image_purpose_label,  
		$user_image_gps_latitude, $user_image_gps_longitude);
	
	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"uiid" => $uiid, 
			"uid" => $uid, 
			"userImageSequence" => $user_image_sequence, 
		    "userImageProfileSequence" => $user_image_profile_sequence, 
			"userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name, 
			"userImageName" => $user_image_name, 
			"userImagePrivacyLabel" => $user_image_privacy_label,
			"userImagePurposeLabel" => $user_image_purpose_label,
			"userImageGpsLatitude" => $user_image_gps_latitude,
			"userImageGpsLongitude" => $user_image_gps_longitude
		);
		array_push($imageList, $image);
	}

	$statement->close();

	return $imageList;
}



/* FUNCTION:    dbGetImagesFirstNProfile
 * DESCRIPTION: Gets the first N profile images for the specified UID user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetImagesFirstNProfileByUid($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
  
  // IMPORT THE NECESSARY GLOBAL VARIABLES
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $firstN = $array_gv["Image"]["ImageProfileMaxAmount"];

  // EXECUTE THE QUERY
  $query = "SELECT    user_image_profile_sequence, user_image_sequence, user_image_name 
            FROM      T_USER_IMAGE 
            WHERE     uid = ? 
                      AND user_image_profile_sequence IS NOT NULL
            ORDER BY  user_image_profile_sequence 
            LIMIT     ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $firstN);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($user_image_profile_sequence, $user_image_sequence, $user_image_name);
  
  $imageList = array();

  while($statement->fetch())
  {
    $image = array
    (
      "userImageProfileSequence" => $user_image_profile_sequence,
      "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name
    );
    array_push($imageList, $image);
  }

  $statement->close();

  return $imageList;
}



/* FUNCTION:    dbSetImage
 * DESCRIPTION: Updates the image's properties in the database and filesystem.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetImage($image)
{
    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */
  
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// FETCH THE CURRENT VALUES FOR THIS EVENT
	$imageCurrent = fetchImageEncoded($image["uiid"]);
	
	if ($image["userImageProfileSequence"] != null)
	  $imageCurrent["userImageProfileSequence"] = $image["userImageProfileSequence"];
	if ($image["userImageName"] != null)
	  $imageCurrent["userImageName"] = $image["userImageName"];
	if ($image["userImagePurposeCode"] != null)
	  $imageCurrent["userImagePurposeCode"] = $image["userImagePurposeCode"];
	if ($image["userImagePrivacyCode"] != null)
	  $imageCurrent["userImagePrivacyCode"] = $image["userImagePrivacyCode"];
	if ($image["userImageGpsLatitude"] != null)
	  $imageCurrent["userImageGpsLatitude"] = $image["userImageGpsLatitude"];
	if ($image["userImageGpsLongitude"] != null)
	  $imageCurrent["userImageGpsLongitude"] = $image["userImageGpsLongitude"];
	
	// EXECUTE THE QUERY
	$query = "UPDATE T_USER_IMAGE
			  SET   user_image_profile_sequence = ?, 
	                user_image_name = ?, 
	                user_image_purpose_code = ?, 
	                user_image_privacy_code = ?, 
	                user_image_gps_latitude = ?, 
	                user_image_gps_longitude = ?
		      WHERE uiid = ?";
		
	$statement = $conn->prepare($query);
		
	$statement->bind_param("isiiddi", $imageCurrent["userImageProfileSequence"], $imageCurrent["userImageName"], 
	    $imageCurrent["userImagePurposeCode"], $imageCurrent["userImagePrivacyCode"], 
	    $imageCurrent["userImageGpsLatitude"], $imageCurrent["userImageGpsLongitude"], $imageCurrent["uiid"]);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { return "DB ERROR: " . $error; }
		
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	if ($statement->affected_rows === 1)
		return "Image has been successfully updated.";
	else 
		return "Image has failed to update: no image or multiple images have been updated.";
	
	$statement->close();
}

?>