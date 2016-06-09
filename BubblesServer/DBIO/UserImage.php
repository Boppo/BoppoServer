<?php

/* FUNCTION: addImageToEvent
 * DESCRIPTION: Adds the image with the specified uiid to the event with the
 *              specified eid for the user with the specified uid.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function addImageToEvent($eid, $uid, $uiid)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// ACQUIRE THE USER IMAGE SEQUENCE
	$query = "INSERT INTO R_EVENT_USER_IMAGE (eid, uid, uiid)
			  VALUES (?, ?, ?)";
	$statement = $conn->prepare($query);
	$statement->bind_param("iii", $eid, $uid, $uiid);
	$statement->execute();
	$statement->error;

	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	$statement->close();

	// RETURN THE USER IMAGE SEQUENCE
	return "Success";
}



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



/* FUNCTION: fetchImagesByEid
 * DESCRIPTION: Gets the images and their data by specified Eid.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImagesByEid($eid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT T_USER_IMAGE.uiid, T_USER_IMAGE.uid, user_image_sequence, user_image_name, 
					 privacy_label, image_purpose_label, user_image_gps_latitude, user_image_gps_longitude, 
					 user_image_upload_timestamp
			  FROM   T_USER_IMAGE
				  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
				  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
					 LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
			  WHERE
				  	 eid = ?";
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
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_name,
		$user_image_privacy_label, $user_image_purpose_label,
		$user_image_gps_latitude, $user_image_gps_longitude, $user_image_upload_timestamp);

	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"uiid" => $uiid,
			"uid" => $uid,
			"userImageSequence" => $user_image_sequence,
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
function fetchImagesByUidAndPurpose($uid, $imagePurposeLabel)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT uiid, uid, user_image_sequence, user_image_name, privacy_label, image_purpose_label,
				     user_image_gps_latitude, user_image_gps_longitude
			  FROM   T_USER_IMAGE
				  	 LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
				  	 LEFT JOIN T_IMAGE_PURPOSE ON T_USER_IMAGE.user_image_purpose_code = T_IMAGE_PURPOSE.image_purpose_code
			  WHERE
				  	 uid = ? AND T_IMAGE_PURPOSE.image_purpose_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $uid, $imagePurposeLabel);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$uiid = -1;
	$uid = -1;
	$user_image_sequence = -1;
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_name, 
		$user_image_privacy_label, $user_image_purpose_label,
		$user_image_gps_latitude, $user_image_gps_longitude);

	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"uiid" => $uiid, 
			"uid" => $uid, 
			"userImageSequence" => $user_image_sequence, 
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
function fetchImagesByPrivacyAndPurpose($imagePrivacyLabel, $imagePurposeLabel)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT uiid, uid, user_image_sequence, user_image_name, privacy_label, image_purpose_label, 
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
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_name, 
		$user_image_privacy_label, $user_image_purpose_label,  
		$user_image_gps_latitude, $user_image_gps_longitude);
	
	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"uiid" => $uiid, 
			"uid" => $uid, 
			"userImageSequence" => $user_image_sequence, 
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
?>