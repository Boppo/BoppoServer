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
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// ACQUIRE THE USER IMAGE SEQUENCE
	$query = "SELECT (MAX(user_image_sequence)+1) FROM T_USER_IMAGE WHERE UID = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid);
	$statement->execute();
	$statement->error;

	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	//echo "DEFAULT USER_IMAGE_SEQUENCE: " . $user_image_sequence . "<br>";
	$statement->bind_result($user_image_sequence);
	$statement->fetch();
	$statement->close();
	// DEFAULT AND ASSIGN THE USER IMAGE SEQUENCE
	if (!$user_image_sequence || $user_image_sequence == null)
	  $user_image_sequence = 1;
	//echo "FINAL USER_IMAGE_SEQUENCE: " . $user_image_sequence . "<br>";

	// RETURN THE USER IMAGE SEQUENCE
	return $user_image_sequence;
	//return "Success.";
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
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT  uiid, uid, user_image_sequence, user_image_profile_sequence,
                    user_image_name, user_image_privacy_code, 
                    user_image_gps_latitude, user_image_gps_longitude, 
                    user_image_insert_timestamp, user_image_update_timestamp
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
      $user_image_name, $user_image_privacy_code, 
      $user_image_gps_latitude, $user_image_gps_longitude, 
      $user_image_insert_timestamp, $user_image_update_timestamp);
  $statement->fetch();

  $image = array
  (
      "uiid" => $uiid,
      "uid" => $uid,
      "userImageSequence" => $user_image_sequence,
      "userImageProfileSequence" => $user_image_profile_sequence,
      "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name, 
      "userImageThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name, 
      "userImageName" => $user_image_name,
      "userImagePrivacyCode" => $user_image_privacy_code,
      "userImageGpsLatitude" => $user_image_gps_latitude,
      "userImageGpsLongitude" => $user_image_gps_longitude,
      "userImageInsertTimestamp" => $user_image_insert_timestamp, 
      "userImageUpdateTimestamp" => $user_image_update_timestamp
  );

  $statement->close();

  return $image;
}



/* FUNCTION: fetchImages
 * DESCRIPTION: Gets the images and their data by specified uiid.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImages($uiid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT  T_USER_IMAGE.uiid, T_USER_IMAGE.uid, user_image_sequence, user_image_profile_sequence, 
                    user_image_name, privacy_label,  
                    user_image_gps_latitude, user_image_gps_longitude, user_image_upload_timestamp
            FROM    T_USER_IMAGE
                    LEFT JOIN T_PRIVACY ON T_USER_IMAGE.user_image_privacy_code = T_PRIVACY.privacy_code
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
      $user_image_name, $user_image_privacy_label,
      $user_image_gps_latitude, $user_image_gps_longitude, $user_image_upload_timestamp);
  $statement->fetch();
  
  $image = array
  (
    "uiid" => $uiid,
    "uid" => $uid,
    "userImageSequence" => $user_image_sequence,
    "userImageProfileSequence" => $user_image_profile_sequence, 
    "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name,
    "userImageThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name,
    "userImageName" => $user_image_name,
    "userImagePrivacyLabel" => $user_image_privacy_label,
    "userImageGpsLatitude" => $user_image_gps_latitude,
    "userImageGpsLongitude" => $user_image_gps_longitude,
    "userImageUploadTimestamp" => $user_image_upload_timestamp
  );

  $statement->close();

  return $image;
}



/* FUNCTION:    dbGetImagesByEid
 * DESCRIPTION: Gets the images and their data by specified Eid, and optionally
 *              filters them to eventProfile or non-eventProfile images.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetImagesByEid($eid, $event_profile_indicator)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php'; 
	
	$subquery = "";
	if ($event_profile_indicator === true)
	  $subquery = " AND eui_event_profile_sequence IS NOT NULL ";
	else if ($event_profile_indicator === false)
	  $subquery = " AND eui_event_profile_sequence IS NULL "; 
	else if (!$event_profile_indicator) {} // Do Nothing
	
	// EXECUTE THE QUERY
	$query = "SELECT   T_USER_IMAGE.uiid, uid, user_image_sequence, user_image_profile_sequence, 
					   user_image_name, user_image_gps_latitude, user_image_gps_longitude, 
	                   user_image_view_count, user_image_like_count, user_image_dislike_count, 
	                   user_image_comment_count, user_image_insert_timestamp, user_image_update_timestamp, 
	                   eui_event_profile_sequence, eui_insert_timestamp, eui_update_timestamp
			  FROM     T_USER_IMAGE
					   LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
			  WHERE
				  	   eid = ?" . $subquery . 
	         "ORDER BY eui_insert_timestamp DESC";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// BIND THE RESULTING VARIABLES
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
	    $user_image_name, $user_image_gps_latitude, $user_image_gps_longitude, 
	    $user_image_view_count, $user_image_like_count, $user_image_dislike_count, 
	    $user_image_comment_count, $user_image_insert_timestamp, $user_image_update_timestamp, 
	    $eui_event_profile_sequence, $eui_insert_timestamp, $eui_update_timestamp);

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
	      "userImageThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name,
	      "userImageName" => $user_image_name,
	      "userImageGpsLatitude" => $user_image_gps_latitude,
	      "userImageGpsLongitude" => $user_image_gps_longitude,
	      "userImageViewCount" => $user_image_view_count,
	      "userImageLikeCount" => $user_image_like_count,
	      "userImageDislikeCount" => $user_image_dislike_count,
	      "userImageCommentCount" => $user_image_comment_count,
	      "userImageInsertTimestamp" => $user_image_insert_timestamp,
	      "userImageUpdateTimestamp" => $user_image_update_timestamp,
	      "euiEventProfileSequence" => $eui_event_profile_sequence, 
	      "euiInsertTimestamp" => $eui_insert_timestamp, 
	      "euiUpdateTimestamp" => $eui_update_timestamp
	  );
	  array_push($imageList, $image);
	}
	$images = array(
	    "images" => $imageList
	);
	
	$statement->close();
	
	return $images;
}



/* FUNCTION:    dbGetImagesByUid
 * DESCRIPTION: Gets the images and their data by specified Uid, and optionally
 *              filters them to userProfile or non-userProfile images.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetImagesByUid($uid, $user_profile_indicator)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	$subquery = "";
	if ($user_profile_indicator === true)
	  $subquery = " AND user_image_profile_sequence IS NOT NULL ";
    else if ($user_profile_indicator === false)
      $subquery = " AND user_image_profile_sequence IS NULL ";
    else if (!$user_profile_indicator) {} // Do Nothing 

	// EXECUTE THE QUERY
	$query = "SELECT uiid, uid, user_image_sequence, user_image_profile_sequence, 
					 user_image_name, user_image_gps_latitude, user_image_gps_longitude, 
	                 user_image_view_count, user_image_like_count, user_image_dislike_count, 
	                 user_image_comment_count, user_image_insert_timestamp, user_image_update_timestamp 
			  FROM   T_USER_IMAGE
			  WHERE  uid = ?" . $subquery . 
	         "ORDER BY user_image_insert_timestamp DESC";
	
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// BIND THE RESULTING VARIABLES
	$statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
	    $user_image_name, $user_image_gps_latitude, $user_image_gps_longitude, 
	    $user_image_view_count, $user_image_like_count, $user_image_dislike_count, 
	    $user_image_comment_count, $user_image_insert_timestamp, $user_image_update_timestamp);

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
		    "userImageThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name,
			"userImageName" => $user_image_name, 
			"userImageGpsLatitude" => $user_image_gps_latitude,
			"userImageGpsLongitude" => $user_image_gps_longitude, 
		    "userImageViewCount" => $user_image_view_count, 
		    "userImageLikeCount" => $user_image_like_count, 
		    "userImageDislikeCount" => $user_image_dislike_count, 
		    "userImageCommentCount" => $user_image_comment_count, 
		    "userImageInsertTimestamp" => $user_image_insert_timestamp, 
		    "userImageUpdateTimestamp" => $user_image_update_timestamp 
		);
		array_push($imageList, $image);
	}
	$images = array(
	    "images" => $imageList
	);

	$statement->close();

	return $images;
}



/* FUNCTION:    dbGetImagesFirstNProfileByUid
 * DESCRIPTION: Gets the first N profile images for the specified UID user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetImagesFirstNProfileByUid($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
  
  // IMPORT THE NECESSARY GLOBAL VARIABLES
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $firstN = $array_gv["UserImage"]["UserImageProfileMaxAmount"];

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
      "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name, 
      "userImageThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name,
    );
    array_push($imageList, $image);
  }

  $statement->close();

  return $imageList;
}



/* FUNCTION:    dbGetImagesFirstNEventProfileByEid
 * DESCRIPTION: Gets the first N profile images for the specified UID user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetImagesFirstNEventProfileByEid($eid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // IMPORT THE NECESSARY GLOBAL VARIABLES
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $firstN = $array_gv["UserImage"]["EventUserImageEventProfileMaxAmount"];

  // EXECUTE THE QUERY
  $query = "SELECT   uid, user_image_sequence, user_image_name, eui_event_profile_sequence
            FROM     T_USER_IMAGE
                     LEFT JOIN R_EVENT_USER_IMAGE ON T_USER_IMAGE.uiid = R_EVENT_USER_IMAGE.uiid
            WHERE    eid = ?
                     AND eui_event_profile_sequence IS NOT NULL
            ORDER BY eui_event_profile_sequence
            LIMIT    ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $eid, $firstN);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($uid, $user_image_sequence, $user_image_name, $eui_event_profile_sequence);

  $imageList = array();

  while($statement->fetch())
  {
    $image = array
    (
        "euiEventProfileSequence" => $eui_event_profile_sequence,
        "euiPath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name, 
        "euiThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name,
    );
    array_push($imageList, $image);
  }

  $statement->close();

  return $imageList;
}



/* FUNCTION:    dbGetImagesLatestNByUid
 * DESCRIPTION: Gets the latest N images for the specified UID user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetImagesLatestNByUid($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
  
  // IMPORT THE NECESSARY GLOBAL VARIABLES
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $latestN = $array_gv["MaxAmount"]["UserProfileUploadedImageMaxAmount"];

  // EXECUTE THE QUERY
  $query = "SELECT   uiid, uid, user_image_sequence, user_image_profile_sequence,
              	     user_image_name, user_image_gps_latitude, user_image_gps_longitude,
                     user_image_view_count, user_image_like_count, user_image_dislike_count,
                     user_image_comment_count, user_image_insert_timestamp, user_image_update_timestamp
            FROM     T_USER_IMAGE
            WHERE    uid = ? 
            ORDER BY user_image_insert_timestamp DESC
            LIMIT    ?";

  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $latestN);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return formatJsonResponseError($error); }

  // BIND THE RESULTING VARIABLES
  $statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence,
      $user_image_name, $user_image_gps_latitude, $user_image_gps_longitude,
      $user_image_view_count, $user_image_like_count, $user_image_dislike_count,
      $user_image_comment_count, $user_image_insert_timestamp, $user_image_update_timestamp);

  $images = array();

  while($statement->fetch())
  {
    $image = array
    (
        "uiid" => $uiid,
        "uid" => $uid,
        "userImageSequence" => $user_image_sequence,
        "userImageProfileSequence" => $user_image_profile_sequence,
        "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name,
        "userImageThumbnailPath" => $uid . "/" . $user_image_sequence . "/TMB " . $user_image_name,
        "userImageName" => $user_image_name,
        "userImageGpsLatitude" => $user_image_gps_latitude,
        "userImageGpsLongitude" => $user_image_gps_longitude,
        "userImageViewCount" => $user_image_view_count,
        "userImageLikeCount" => $user_image_like_count,
        "userImageDislikeCount" => $user_image_dislike_count,
        "userImageCommentCount" => $user_image_comment_count,
        "userImageInsertTimestamp" => $user_image_insert_timestamp,
        "userImageUpdateTimestamp" => $user_image_update_timestamp
    );
    array_push($images, $image);
  }
  $parent = array
  (
      "images" => $images
  );

  $statement->close();

  return $parent;
}



/* FUNCTION:    dbSetImage
 * DESCRIPTION: Updates the image's properties in the database and filesystem.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetImage($image, $set_or_not)
{
    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */
  
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE CURRENT VALUES FOR THIS EVENT
	$imageCurrent = fetchImageEncoded($image["uiid"]);
		
	if ($set_or_not["userImageProfileSequence"] === true)
	  $imageCurrent["userImageProfileSequence"] = $image["userImageProfileSequence"];
	if ($set_or_not["userImageName"] === true)
	  $imageCurrent["userImageName"] = $image["userImageName"];
	if ($set_or_not["userImagePrivacyCode"] === true)
	  $imageCurrent["userImagePrivacyCode"] = $image["userImagePrivacyCode"];
	if ($set_or_not["userImageGpsLatitude"] === true)
	  $imageCurrent["userImageGpsLatitude"] = $image["userImageGpsLatitude"];
	if ($set_or_not["userImageGpsLongitude"] === true)
	  $imageCurrent["userImageGpsLongitude"] = $image["userImageGpsLongitude"];
	
	// EXECUTE THE QUERY
	$query = "UPDATE T_USER_IMAGE
			  SET   user_image_profile_sequence = ?, 
	                user_image_name = ?, 
	                user_image_privacy_code = ?, 
	                user_image_gps_latitude = ?, 
	                user_image_gps_longitude = ?
		      WHERE uiid = ?";
	
	/*
	echo "<br>QUERY: <br>";
	echo ($query);
	echo "<br>VALUES: <br>";
	var_dump($imageCurrent);
	*/
		
	$statement = $conn->prepare($query);
		
	$statement->bind_param("isiddi", $imageCurrent["userImageProfileSequence"], $imageCurrent["userImageName"], 
	    $imageCurrent["userImagePrivacyCode"], 
	    $imageCurrent["userImageGpsLatitude"], $imageCurrent["userImageGpsLongitude"], $imageCurrent["uiid"]);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { return "DB ERROR: " . $error; }
		
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	if ($statement->affected_rows === 0)
	  return "Image has failed to update: no image has been updated, possibly because the input data is not new.";
	if ($statement->affected_rows === 1)
      return "Image has been successfully updated.";
	else
      return "Image has failed to update: no image or multiple images have been updated.";
	
	$statement->close();
}



/* FUNCTION:    dbGetCountImages
 * DESCRIPTION: Retrieves and returns the count of images that the user with the
 *              specified uid has..
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetCountImages($uid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT COUNT(*) AS countImages 
            FROM   T_USER_IMAGE 
            WHERE  uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { return formatJsonResponseError($error); }
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->num_rows === 0) {
    return formatJsonResponseError("Contact the database administrator about the dbGetCountImages PHP method.");
  }

  $statement->bind_result($countImages);
  $statement->fetch();
  $statement->close();

  // RETURN THE REQUESTED VALUE(S)
  return $countImages;
}

?>