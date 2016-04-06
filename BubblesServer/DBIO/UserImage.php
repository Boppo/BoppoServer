<?php

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
	$query = "SELECT uid, uiid, user_image_name, privacy_label, image_purpose_label, 
				     user_image_eid, user_image_gps_latitude, user_image_gps_longitude
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
	$user_image_uid = -1;
	$user_image_gps_latitude  = -1.0;
	$user_image_gps_longitude = -1.0;
	$statement->bind_result($uid, $uiid, $user_image_name, $user_image_privacy_label,
		$user_image_purpose_label, $user_image_eid, 
		$user_image_gps_latitude, $user_image_gps_longitude);
	
	$imageList = array();

	while($statement->fetch())
	{
		$image = array
		(
			"userImagePath" => $uid . "/" . $uiid . "/" . $user_image_name,
			"userImagePrivacyLabel" => $user_image_privacy_label,
			"userImagePurposeLabel" => $user_image_purpose_label,
			"userImageEid" => $user_image_eid, 
			"userImageGpsLatitude" => $user_image_gps_latitude, 
			"userImageGpsLongitude" => $user_image_gps_longitude
		);
		array_push($imageList, $image);
	}

	$statement->close();

	return $imageList;
}
?>