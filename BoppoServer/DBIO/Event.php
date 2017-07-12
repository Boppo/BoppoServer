<?php

/* FUNCTION:    dbGetEventData
 * DESCRIPTION: Gets the data of an entire event for the specified eid
 *              (Event Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventData($eid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, T_USER.uid, username, first_name, last_name,  
	                 user_image_sequence, user_image_name, 
		       		 event_name, event_category_label, event_type_label, 
	                 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
	                 event_description_text, event_start_datetime, event_end_datetime, 
	                 event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
	                 LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
	                   AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
	                 LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code 
                     LEFT JOIN T_USER_IMAGE ON T_USER.uid = T_USER_IMAGE.uid
			  WHERE  eid = ? 
                     AND user_image_profile_sequence = 0 
			  ORDER BY event_name";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	// CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
	if ($statement->num_rows === 0) { echo "No such event exists."; return; }
	
	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
		$event_host_first_name, $event_host_last_name, 
	    $user_image_sequence, $user_image_name,
	    $event_name, $event_category_label, $event_type_label, 
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_description_text, 
	    $event_start_datetime, $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);
	$statement->fetch();
	
	$user_profile_images = dbGetImagesFirstNProfileByUid($event_host_uid);
	$event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	$event_address = dbGetAddressByEid($eid);
	
	$eventHost = array
	(
      "uid" => $event_host_uid,
      "username" => $event_host_username,
      "firstName" => $event_host_first_name,
      "lastName" => $event_host_last_name, 
      "userProfileImages" => $user_profile_images
	);
	$event = array
	(
      "eid" => $eid, 
      "eventName" => $event_name, 
      "eventCategoryLabel" => $event_category_label, 
      "eventTypeLabel" => $event_type_label, 
      "eventInviteTypeLabel" => $event_invite_type_label,
      "eventPrivacyLabel" => $event_privacy_label,
      "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
      "eventDescriptionText" => $event_description_text, 
      "eventStartDatetime" => $event_start_datetime,
      "eventEndDatetime" => $event_end_datetime,
      "eventGpsLatitude" => $event_gps_latitude,
      "eventGpsLongitude" => $event_gps_longitude,
      "eventLikeCount" => $event_like_count,
      "eventDislikeCount" => $event_dislike_count,
      "eventViewCount" => $event_view_count, 
      "eventHost" => $eventHost, 
      "eventProfileImages" => $event_profile_images, 
      "eventAddress" => $event_address
	);
	
	$statement->close();
	
	return $event;
}



/* FUNCTION:    dbGetEventDataByRadius
 * DESCRIPTION: Gets the data of an entire event for all events whose coordinates 
 *              are within the specified radius of the specified coordinates.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByRadius($longitude, $latitude, $radius)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name,
	       		   event_category_label, event_type_label, 
                   event_invite_type_code, event_privacy_code, event_image_upload_allowed_indicator,
                   event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
                   event_like_count, event_dislike_count, event_view_count,
                   (((acos(sin((? * pi() / 180)) * sin((event_gps_latitude * pi() / 180))
                     + cos((? * pi() / 180))
                     * cos((event_gps_latitude * pi() / 180))
                     * cos(((? - event_gps_longitude) * pi() / 180)))) * 180 / pi()) * 60 * 1.1515) as distance
            FROM   T_EVENT
                   LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                   LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                     AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                   LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
            HAVING distance <= ?
            ORDER BY distance DESC";
  $statement = $conn->prepare($query);
  $statement->bind_param("dddd", $latitude, $latitude, $longitude, $radius);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }
  if ($statement->num_rows === 0) { echo "No such event exists."; return; }
  

  // DEFAULT AND ASSIGN THE EVENT VARIABLES
  /*
   $event_start_datetime = "";
   $event_end_datetime   = "";
   $event_gps_latitude   = -1.0;
   $event_gps_longitude  = -1.0;
   */
  $statement->bind_result($eid, $event_host_uid, $event_host_username,
      $event_host_first_name, $event_host_last_name, $event_name,
      $event_category_label, $event_type_label,
      $event_invite_type_label, $event_privacy_label,
      $event_image_upload_allowed_indicator, $event_start_datetime,
      $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
      $event_like_count, $event_dislike_count, $event_view_count, $distance);

  $events = array();

  while($statement->fetch())
  {
    $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
    $event_address = dbGetAddressByEid($eid);
    
    $eventHost = array
    (
      "uid" => $event_host_uid,
      "username" => $event_host_username,
      "firstName" => $event_host_first_name,
      "lastName" => $event_host_last_name
    );
    $event = array
    (
      "eid" => $eid,
      "eventName" => $event_name, 
      "eventCategoryLabel" => $event_category_label,
      "eventTypeLabel" => $event_type_label,
      "eventInviteTypeLabel" => $event_invite_type_label,
      "eventPrivacyLabel" => $event_privacy_label,
      "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
      "eventStartDatetime" => $event_start_datetime,
      "eventEndDatetime" => $event_end_datetime,
      "eventGpsLatitude" => $event_gps_latitude,
      "eventGpsLongitude" => $event_gps_longitude,
      "eventLikeCount" => $event_like_count,
      "eventDislikeCount" => $event_dislike_count,
      "eventViewCount" => $event_view_count,
      "distanceFromLocation" => $distance,
      "eventHost" => $eventHost, 
      "eventProfileImages" => $event_profile_images, 
      "eventAddress" => $event_address
    );
    array_push($events, $event);
  }
  $statement->close();

  $eventList = array
  (
      "events" => $events
  );

  return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByRadius
 * DESCRIPTION: Gets the data of an entire live event for all events whose 
 *              coordinates are within the specified radius of the specified 
 *              coordinates. An event whose start-date-to-end-date time period is 
 *              no more than the configured amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByRadius($longitude, $latitude, $radius)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit 
		= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
		= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name,
		       		 event_category_label, event_type_label, 
      			     event_invite_type_code, event_privacy_code, event_image_upload_allowed_indicator,
      			     event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
      			     event_like_count, event_dislike_count, event_view_count, 
      			     (((acos(sin((? * pi() / 180)) * sin((event_gps_latitude * pi() / 180)) 
      			       + cos((? * pi() / 180)) 
      			       * cos((event_gps_latitude * pi() / 180)) 
      			  	   * cos(((? - event_gps_longitude) * pi() / 180)))) * 180 / pi()) * 60 * 1.1515) as distance
			  FROM   T_EVENT
	       	         LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  HAVING distance <= ? 
			         AND TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			         CURRENT_TIMESTAMP, event_start_datetime) < ? 
			         AND TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			         event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY distance DESC";
	$statement = $conn->prepare($query);
	$statement->bind_param("ddddii", $latitude, $latitude, $longitude, $radius, 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
        $event_host_first_name, $event_host_last_name, $event_name,
        $event_category_label, $event_type_label,
        $event_invite_type_label, $event_privacy_label,
        $event_image_upload_allowed_indicator, $event_start_datetime,
        $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
        $event_like_count, $event_dislike_count, $event_view_count, $distance);
	
	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name,
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count, 
        "distanceFromLocation" => $distance, 
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetEventDataEncoded
 * DESCRIPTION: Gets the data of an entire event for the specified eid
 *              (Event Identifier) with all of the labels encoded (i.e. those that
 *              are stored in reference/lookup tables).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataEncoded($eid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, event_host_uid, event_name,
	                 event_category_code, event_type_code, event_invite_type_code, event_privacy_code, 
	                 event_image_upload_allowed_indicator, event_description_text, event_aid, 
			         event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
			         event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
			  WHERE  eid = ?
			  ORDER BY event_name";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$statement->bind_result($eid, $event_host_uid, $event_name,
	    $event_category_code, $event_type_code, $event_invite_type_code, $event_privacy_code,
		$event_image_upload_allowed_indicator, $event_description_text, $event_aid, 
	    $event_start_datetime, $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);
	$statement->fetch();
	
	$event = array
	(
      "eid" => $eid, 
      "eventHostUid" => $event_host_uid,
      "eventName" => $event_name, 
      "eventCategoryCode" => $event_category_code, 
      "eventTypeCode" => $event_type_code, 
      "eventInviteTypeCode" => $event_invite_type_code,
      "eventPrivacyCode" => $event_privacy_code,
      "eventImageUploadAllowedIndicator" => $event_image_upload_allowed_indicator,
      "eventDescriptionText" => $event_description_text, 
      "eventAid" => $event_aid, 
      "eventStartDatetime" => $event_start_datetime,
      "eventEndDatetime" => $event_end_datetime,
      "eventGpsLatitude" => $event_gps_latitude,
      "eventGpsLongitude" => $event_gps_longitude,
      "eventLikeCount" => $event_like_count,
      "eventDislikeCount" => $event_dislike_count,
      "eventViewCount" => $event_view_count 
	);

	$statement->close();

	return $event;
}



/* FUNCTION:    dbGetEventDataByMember
 * DESCRIPTION: Gets the data of an entire event for the specified eid
 *              (Event Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByMember($uid)
{
	// IMPORT REQUIRED METHODS
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Privacy.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
    
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// GET THE EVENT USER DATA
	$eventUser = fetchUserEncoded($uid); 
	$userPrivacyLabel = fetchPrivacyCode($eventUser["userPrivacyCode"]); 
	unset($eventUser["userPrivacyCode"]);
	$eventUser["userPrivacyLabel"] = $userPrivacyLabel; 
	
	// EXECUTE THE QUERY
	$query = "SELECT T_EVENT.eid, event_name,
	                 event_host_uid, eh.username AS event_host_username,
                     eh.first_name AS event_host_first_name, eh.last_name AS event_host_last_name, 
	                 event_category_label, event_type_label,
	                 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
                     event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
                     event_like_count, event_dislike_count, event_view_count 
              FROM   T_EVENT
              	     LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
                     LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
                     LEFT JOIN R_EVENT_USER ON T_EVENT.eid = R_EVENT_USER.eid
                     LEFT JOIN T_USER eh ON T_EVENT.event_host_uid = eh.uid
                     LEFT JOIN T_USER eu ON R_EVENT_USER.uid = eu.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
              WHERE  R_EVENT_USER.uid = ? 
              ORDER  BY event_name";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";
	
	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_name,
	    $event_host_uid, $event_host_username, $event_host_first_name, $event_host_last_name, 
	    $event_category_label, $event_type_label,
	    $event_invite_type_label, $event_privacy_label,
	    $event_image_upload_allowed_indicator, $event_start_datetime,
	    $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
	    $event_like_count, $event_dislike_count, $event_view_count);
	
	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
        "events" => $events, 
	    "eventUser" => $eventUser
	);

	return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByMember
 * DESCRIPTION: Gets the data of an entire live event for all of the events of 
 *              which the specified uid (User Identifier) is a member. An event 
 *              whose start-date-to-end-date time period is no more than the 
 *              configured amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByMember($uid)
{
    // IMPORT REQUIRED METHODS
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Privacy.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
    require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
    
    // IMPORT THE DATABASE CONNECTION
    require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
    
    // GET THE EVENT USER DATA
    $eventUser = fetchUserEncoded($uid);
    $userPrivacyLabel = fetchPrivacyCode($eventUser["userAccountPrivacyCode"]);
    unset($eventUser["userPrivacyCode"]);
    $eventUser["userPrivacyLabel"] = $userAccountPrivacyLabel;
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// EXECUTE THE QUERY
	$query = "SELECT T_EVENT.eid, event_name,
	                 event_host_uid, eh.username AS event_host_username,
                     eh.first_name AS event_host_first_name, eh.last_name AS event_host_last_name, 
                     event_category_label, event_type_label,
	                 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
                     event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
                     event_like_count, event_dislike_count, event_view_count 
              FROM   T_EVENT
              	     LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
                     LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
                     LEFT JOIN R_EVENT_USER ON T_EVENT.eid = R_EVENT_USER.eid
                     LEFT JOIN T_USER eh ON T_EVENT.event_host_uid = eh.uid
                     LEFT JOIN T_USER eu ON R_EVENT_USER.uid = eu.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
              WHERE  R_EVENT_USER.uid = ? 
  			  HAVING TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   CURRENT_TIMESTAMP, event_start_datetime) < ? AND 
			    	 TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY event_name";
	$statement = $conn->prepare($query);
	$statement->bind_param("iii", $uid, 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_name,
	    $event_host_uid, $event_host_username, $event_host_first_name, $event_host_last_name, 
	    $event_category_label, $event_type_label,
	    $event_invite_type_label, $event_privacy_label,
	    $event_image_upload_allowed_indicator, $event_start_datetime,
	    $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
	    $event_like_count, $event_dislike_count, $event_view_count);
	
	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events,
	    "eventUser" => $eventUser
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetEventDataByMember
 * DESCRIPTION: Gets the data of an entire event for all of the events of which 
 *  			the specified uid (User Identifier) is a member.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByName($event_name)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// MAKE THE EVENT NAME SEARCH BE A PARTIAL MATCH SEARCH
	$event_name = "%".$event_name."%";

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name,
	                 event_category_label, event_type_label,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  WHERE  event_name LIKE ? 
			  ORDER BY event_name";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $event_name);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
        $event_host_first_name, $event_host_last_name, $event_name, 
        $event_category_label, $event_type_label,
        $event_invite_type_label, $event_privacy_label,
        $event_image_upload_allowed_indicator, $event_start_datetime,
        $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
        $event_like_count, $event_dislike_count, $event_view_count);

	$events = array();

	while($statement->fetch())
	{
        $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
        $event_address = dbGetAddressByEid($eid);
	  
        $eventHost = array
        (
          "uid" => $event_host_uid,
          "username" => $event_host_username,
          "firstName" => $event_host_first_name,
          "lastName" => $event_host_last_name
        );
		$event = array
		(
          "eid" => $eid,
          "eventName" => $event_name, 
          "eventCategoryLabel" => $event_category_label,
          "eventTypeLabel" => $event_type_label,
          "eventInviteTypeLabel" => $event_invite_type_label,
          "eventPrivacyLabel" => $event_privacy_label,
          "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
          "eventStartDatetime" => $event_start_datetime,
          "eventEndDatetime" => $event_end_datetime,
          "eventGpsLatitude" => $event_gps_latitude,
          "eventGpsLongitude" => $event_gps_longitude,
          "eventLikeCount" => $event_like_count,
          "eventDislikeCount" => $event_dislike_count,
          "eventViewCount" => $event_view_count, 
          "eventHost" => $eventHost, 
          "eventProfileImages" => $event_profile_images, 
          "eventAddress" => $event_address
		);
		array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByName
 * DESCRIPTION: Gets the data of an entire live event for all of the events whose 
 *              names match the specified event name. An event whose 
 *              start-date-to-end-date time period is no more than the configured 
 *              amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByName($event_name)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// MAKE THE EVENT NAME SEARCH BE A PARTIAL MATCH SEARCH
	$event_name = "%".$event_name."%";

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name,
                     event_category_label, event_type_label, 
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  WHERE  event_name LIKE ?
  			  HAVING TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   CURRENT_TIMESTAMP, event_start_datetime) < ? AND 
			    	 TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY event_name";
	$statement = $conn->prepare($query);
	$statement->bind_param("sii", $event_name, 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
		$event_host_first_name, $event_host_last_name, $event_name, 
	    $event_category_label, $event_type_label, 
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetEventDataByTopNViews
 * DESCRIPTION: Gets the data of an entire event for all of the events that have 
 *              the top N count of views, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNViews($top_n_views)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  ORDER BY event_view_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n_views);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0; 
	 $event_gps_longitude  = -1.0; 
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
        $event_host_first_name, $event_host_last_name, $event_name, 
        $event_category_label, $event_type_label,
        $event_invite_type_label, $event_privacy_label,
        $event_image_upload_allowed_indicator, $event_start_datetime,
        $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
        $event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByTopNViews
 * DESCRIPTION: Gets the data of an entire live event for all of the events that 
 *              have the top N count of views, where N is the input value. An event 
 *              whose start-date-to-end-date time period is no more than the 
 *              configured amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByTopNViews($top_n_views)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name,
	                 event_category_label, event_type_label,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
  			  HAVING TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   CURRENT_TIMESTAMP, event_start_datetime) < ? AND 
			    	 TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY event_view_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("iii", 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value, 
		$top_n_views);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
        $event_host_first_name, $event_host_last_name, $event_name, 
        $event_category_label, $event_type_label,
        $event_invite_type_label, $event_privacy_label,
        $event_image_upload_allowed_indicator, $event_start_datetime,
        $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
        $event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetEventDataByTopNLikes
 * DESCRIPTION: Gets the data of an entire event for all of the events that have
 *              the top N count of likes, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNLikes($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  ORDER BY event_like_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
        $event_host_first_name, $event_host_last_name, $event_name, 
        $event_category_label, $event_type_label, 
        $event_invite_type_label, $event_privacy_label,
        $event_image_upload_allowed_indicator, $event_start_datetime,
        $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
        $event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByTopNLikes
 * DESCRIPTION: Gets the data of an entire live event for all of the events that 
 *              have the top N count of likes, where N is the input value. An event 
 *              whose start-date-to-end-date time period is no more than the 
 *              configured amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByTopNLikes($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
  			  HAVING TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   CURRENT_TIMESTAMP, event_start_datetime) < ? AND 
			    	 TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY event_like_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("iii", 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value,
		$top_n);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
		$event_host_first_name, $event_host_last_name, $event_name, 
	    $event_category_label, $event_type_label,
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION: dbGetEventDataByTopNDislikes
 * DESCRIPTION: Gets the data of an entire event for all of the events that have
 *              the top N count of dislikes, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNDislikes($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  ORDER BY event_dislike_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists."; 

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
        $event_host_first_name, $event_host_last_name, $event_name, 
        $event_category_label, $event_type_label,
        $event_invite_type_label, $event_privacy_label,
        $event_image_upload_allowed_indicator, $event_start_datetime,
        $event_end_datetime, $event_gps_latitude, $event_gps_longitude,
        $event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByTopNDislikes
 * DESCRIPTION: Gets the data of an entire live event for all of the events that 
 *              have the top N count of dislikes, where N is the input value. An 
 *              event whose start-date-to-end-date time period is no more than the 
 *              configured amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByTopNDislikes($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label, 
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
  			  HAVING TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   CURRENT_TIMESTAMP, event_start_datetime) < ? AND 
			    	 TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY event_dislike_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("iii", 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value, 
		$top_n);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
		$event_host_first_name, $event_host_last_name, $event_name, 
	    $event_category_label, $event_type_label,
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetEventDataByTopNRatings
 * DESCRIPTION: Gets the data of an entire event for all of the events that have
 *              the top N count of dislikes, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNRatings($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label, 
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count,
					 (event_like_count / (event_like_count + event_dislike_count)) AS event_rating_ratio
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
			  ORDER BY event_rating_ratio DESC
			  LIMIT ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
		$event_host_first_name, $event_host_last_name, $event_name, 
	    $event_category_label, $event_type_label, 
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count, $event_rating_ratio);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
      $eventHost = array
      (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
      );
      $event = array
      (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventRatingRatio" => $event_rating_ratio, 
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
      );
      array_push($events, $event);
    }
    $statement->close();
    
    $eventList = array
    (
        "events" => $events
    );
    
    return $eventList;
}



/* FUNCTION:    dbGetLiveEventDataByTopNRatings
 * DESCRIPTION: Gets the data of an entire live event for all of the events that
 *              have the top N ratings, where N is the input value and a rating is
 *              the ratio of likes to the sum of likes and dislikes (ratings). An
 *              event whose start-date-to-end-date time period is no more than the
 *              configured amount of time is considered live.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetLiveEventDataByTopNRatings($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE DATA REPRESENTING THE TIME UNIT AND VALUE OF THE OFFSET OF A LIVE EVENT DURATION
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$event_live_datetime_duration_offset_unit
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeUnit"];
	$event_live_datetime_duration_offset_value
	= $array_gv["Event"]["EventLiveDatetimeDurationOffset"]["DatetimeValue"];

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name, 
	                 event_category_label, event_type_label, 
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count,
					 (event_like_count / (event_like_count + event_dislike_count)) AS event_rating_ratio
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     LEFT JOIN T_EVENT_TYPE ON T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                       AND T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                     LEFT JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code
  			  HAVING TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   CURRENT_TIMESTAMP, event_start_datetime) < ? AND 
			    	 TIMESTAMPDIFF($event_live_datetime_duration_offset_unit, 
			      	   event_end_datetime, CURRENT_TIMESTAMP) < ?
			  ORDER BY event_rating_ratio DESC
			  LIMIT ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("iii", 
		$event_live_datetime_duration_offset_value, $event_live_datetime_duration_offset_value, 
		$top_n);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	if ($statement->num_rows === 0) return "No such event exists.";

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_host_username,
		$event_host_first_name, $event_host_last_name, $event_name, 
	    $event_category_label, $event_type_label,
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count, $event_rating_ratio);

	$events = array();
	
	while($statement->fetch())
	{
	  $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
	  $event_address = dbGetAddressByEid($eid);
	  
	  $eventHost = array
	  (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
	  );
	  $event = array
	  (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventCategoryLabel" => $event_category_label,
        "eventTypeLabel" => $event_type_label,
        "eventInviteTypeLabel" => $event_invite_type_label,
        "eventPrivacyLabel" => $event_privacy_label,
        "eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
        "eventStartDatetime" => $event_start_datetime,
        "eventEndDatetime" => $event_end_datetime,
        "eventGpsLatitude" => $event_gps_latitude,
        "eventGpsLongitude" => $event_gps_longitude,
        "eventLikeCount" => $event_like_count,
        "eventDislikeCount" => $event_dislike_count,
        "eventViewCount" => $event_view_count,
        "eventRatingRatio" => $event_rating_ratio,
        "eventHost" => $eventHost, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
	  );
	  array_push($events, $event);
	}
	$statement->close();
	
	$eventList = array
	(
	    "events" => $events
	);
	
	return $eventList;
}



/* FUNCTION:    dbGetEventDataByTopNRandom
 * DESCRIPTION: Gets the data of an entire event for all of the events that are the 
 *              first N in the "randomly" selected order,  where N is the input 
 *              value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNRandom($uid, $top_n)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT * 
            FROM 
            (
              SELECT eid, event_host_uid, username, first_name, last_name, 
                     event_name, event_description_text,  
                     event_category_label, event_type_label, 
                     address_name, address_unparsed_text, event_insert_timestamp
              FROM   T_EVENT 
                     JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid 
                     JOIN T_EVENT_TYPE ON T_EVENT.event_category_code = T_EVENT_TYPE.event_category_code 
                       AND T_EVENT.event_type_code = T_EVENT_TYPE.event_type_code 
                     JOIN T_EVENT_CATEGORY ON T_EVENT_TYPE.event_category_code = T_EVENT_CATEGORY.event_category_code 
                     LEFT JOIN T_ADDRESS ON T_EVENT.event_aid = T_ADDRESS.aid 
              WHERE  eid IN 
              (
                SELECT eid 
                FROM   R_EVENT_USER 
                       JOIN T_EVENT_USER_INVITE_STATUS_TYPE 
              	         ON R_EVENT_USER.event_user_invite_status_type_code = 
                           T_EVENT_USER_INVITE_STATUS_TYPE.event_user_invite_status_type_code
                WHERE  uid = ? AND event_user_invite_status_type_label = 'Joined'    
              )
              ORDER BY RAND()
              LIMIT ? 
            ) T 
            ORDER BY event_insert_timestamp DESC";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $top_n);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return formatJsonResponseError($error); }
  if ($statement->num_rows === 0) return formatJsonResponseSuccess("No such event exists.");

  // ASSIGN THE RETURNED VALUES TO VARIABLES
  $statement->bind_result($eid, 
      $event_host_uid, $event_host_username, $event_host_first_name, $event_host_last_name, 
      $event_name, $event_description_text, $event_category_label, $event_type_label, 
      $address_name, $address_unparsed_text, $event_insert_timestamp);
  // NEED TO UPDATE THE ABOVE
  
  $events = array();

  while($statement->fetch())
  {
    $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
    $event_address = dbGetAddressByEid($eid);
    
    $event_host = array
    (
        "uid" => $event_host_uid,
        "username" => $event_host_username,
        "firstName" => $event_host_first_name,
        "lastName" => $event_host_last_name
    );
    $event_address = array
    (
        "addressName" => $address_name, 
        "addressUnparsedText" => $address_unparsed_text
    );
    $event = array
    (
        "eid" => $eid,
        "eventName" => $event_name, 
        "eventDescriptionText" => $event_description_text, 
        "eventCategoryLabel" => $event_category_label, 
        "eventTypeLabel" => $event_type_label, 
        "eventHost" => $event_host, 
        "eventProfileImages" => $event_profile_images, 
        "eventAddress" => $event_address
    );
    array_push($events, $event);
  }
  $statement->close();

  $parent = array
  (
      "events" => $events
  );

  return $parent;
}



/* FUNCTION:    dbUpdateEvent
 * DESCRIPTION: Updates an event into the corresponding database table with the
 *  			newly provided values.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbUpdateEvent($event, $set_or_not)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// FETCH THE CURRENT VALUES FOR THIS EVENT
	$eventCurrent = dbGetEventDataEncoded($event["eid"]);
	
	// UPDATE THE CURRENT VALUES WITH VALID NEW VALUES
	if ($set_or_not["eventHostUid"] === true)
	  $eventCurrent["eventHostUid"] = $event["eventHostUid"];
    if ($set_or_not["eventName"] === true)
      $eventCurrent["eventName"] = $event["eventName"];
    if ($set_or_not["eventCategoryCode"] === true)
      $eventCurrent["eventCategoryCode"] = $event["eventCategoryCode"];
    if ($set_or_not["eventTypeCode"] === true)
      $eventCurrent["eventTypeCode"] = $event["eventTypeCode"];
    if ($set_or_not["eventInviteTypeCode"] === true)
      $eventCurrent["eventInviteTypeCode"] = $event["eventInviteTypeCode"];
    if ($set_or_not["eventPrivacyCode"] === true)
      $eventCurrent["eventPrivacyCode"] = $event["eventPrivacyCode"];
    if ($set_or_not["eventImageUploadAllowedIndicator"] === true)
      $eventCurrent["eventImageUploadAllowedIndicator"] = $event["eventImageUploadAllowedIndicator"];
    if ($set_or_not["eventDescriptionText"] === true)
      $eventCurrent["eventDescriptionText"] = $event["eventDescriptionText"];
    if ($set_or_not["eventStartDatetime"] === true)
      $eventCurrent["eventStartDatetime"] = $event["eventStartDatetime"];
    if ($set_or_not["eventEndDatetime"] === true)
      $eventCurrent["eventEndDatetime"] = $event["eventEndDatetime"];
    if ($set_or_not["eventGpsLatitude"] === true)
      $eventCurrent["eventGpsLatitude"] = $event["eventGpsLatitude"];
    if ($set_or_not["eventGpsLongitude"] === true)
      $eventCurrent["eventGpsLongitude"] = $event["eventGpsLongitude"];
	
	// EXECUTE THE QUERY
	$query = "UPDATE T_EVENT 
			  SET event_host_uid = ?, 
			      event_name = ?, 
	              event_category_code = ?, 
	              event_type_code = ?, 
			      event_invite_type_code = ?,
			      event_privacy_code = ?, 
			      event_image_upload_allowed_indicator = ?,
	              event_description_text = ?, 
			      event_start_datetime = ?, 
			      event_end_datetime = ?, 
			      event_gps_latitude = ?, 
			      event_gps_longitude = ? 
		      WHERE eid = ?";
		
	$statement = $conn->prepare($query);
		
	$statement->bind_param("isiiiiisssddi", $eventCurrent["eventHostUid"], $eventCurrent["eventName"], 
	    $eventCurrent["eventCategoryCode"], $eventCurrent["eventTypeCode"], 
		$eventCurrent["eventInviteTypeCode"], $eventCurrent["eventPrivacyCode"], 
		$eventCurrent["eventImageUploadAllowedIndicator"], $eventCurrent["eventDescriptionText"], 
	    $eventCurrent["eventStartDatetime"], $eventCurrent["eventEndDatetime"], 
	    $eventCurrent["eventGpsLatitude"], $eventCurrent["eventGpsLongitude"], $event["eid"]);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { return "DB ERROR: " . $error; }
		
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	if ($statement->affected_rows === 0)
	  return "Event has failed to update: no event has been updated, possibly because the input data is not new.";
	else if ($statement->affected_rows === 1)
      return "Event has been successfully updated.";
	else 
      return "Event has failed to update: no event or multiple events have been updated.";
	
	$statement->close();
}



/* FUNCTION:    dbUpdateEventUnparsedAddress
 * DESCRIPTION: Updates an event with the specified eid to have the address with 
 *              the specified aid. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbUpdateEventUnparsedAddress($event)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Address.php';
  
  // FETCH THE CURRENT VALUES FOR THIS EVENT
  $eventCurrent = dbGetEventDataEncoded($event["eid"]);
  
  // DELETE THE ADDRESS IF IT IS NOT BEING USED BY ANY OTHER EVENT
  dbDeleteAddressIfUnused($eventCurrent["eid"], $eventCurrent["eventAid"]);

  // EXECUTE THE QUERY
  $query = "UPDATE T_EVENT
            SET    event_aid = ?
            WHERE  eid = ?";

  $statement = $conn->prepare($query);

  $statement->bind_param("ii", $event["eventAid"], $event["eid"]);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return formatJsonResponseError($error); }

  // RETURN A SUCCESS CONFIRMATION MESSAGE
  if ($statement->affected_rows === 0)
    return formatJsonResponseError(
      "Event has failed to update: no event has been updated, possibly because the input data is not new."
    );
  else if ($statement->affected_rows === 1)
    return formatJsonResponseSuccess(
      "Event has been successfully updated."
    );
  else
    return formatJsonResponseError(
      "Event has failed to update: no event or multiple events have been updated."
    );

  $statement->close();
}



/* FUNCTION:    dbGetCountHostedEvents
 * DESCRIPTION: Retrieves and returns the count of events that the user with the
 *              specified uid hosted..
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetCountHostedEvents($uid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT COUNT(*) AS countHostedEvents 
            FROM   T_EVENT 
            WHERE  event_host_uid = ?";
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
    return formatJsonResponseError("Contact the database administrator about the dbGetCountHostedEvents PHP method.");
  }

  // ASSIGN THE RETURNED VALUES TO VARIABLES
  $statement->bind_result($countHostedEvents);
  $statement->fetch();
  $statement->close();

  // RETURN THE REQUESTED VALUE(S)
  return $countHostedEvents;
}

?>