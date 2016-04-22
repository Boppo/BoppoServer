<?php

/* FUNCTION: fetchEventData
 * DESCRIPTION: Fetches the data of an entire event for the specified eid
 *              (Event Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchEventData($eid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// EXECUTE THE QUERY
	$query = "SELECT event_host_uid, event_name,
			         invite_type_label, privacy_label, event_image_upload_allowed_indicator,
			         event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
			         event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
			         LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
			         LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
			  WHERE  eid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$event_start_datetime = "";
	$event_end_datetime   = "";
	$event_gps_latitude   = -1.0;
	$event_gps_longitude  = -1.0;
	$statement->bind_result($event_host_uid, $event_name,
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);
	$statement->fetch();
	
	$event = array
	(
		"eventHostUid" => $event_host_uid,
		"eventName" => $event_name,
		"eventInviteTypeLabel" => $event_invite_type_label,
		"eventPrivacyLabel" => $event_privacy_label,
		"eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
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



/* FUNCTION: fetchEventData
 * DESCRIPTION: Fetches the data of an entire event for the specified eid
 *              (Event Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchEventDataByMember($uid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, event_host_uid, event_name,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_EVENT_USER ON T_EVENT.eid = T_EVENT_USER.eid 
			  WHERE  uid = ? OR event_host_uid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $uid, $uid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	$event_start_datetime = "";
	$event_end_datetime   = "";
	$event_gps_latitude   = -1.0;
	$event_gps_longitude  = -1.0;
	*/
	$statement->bind_result($eid, $event_host_uid, $event_name,
		$event_invite_type_label, $event_privacy_label,
		$event_image_upload_allowed_indicator, $event_start_datetime,
		$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
		$event_like_count, $event_dislike_count, $event_view_count);
	
	$eventList = array();
	
	while($statement->fetch())
	{
		$event = array
		(
			"eid" => $eid, 
			"eventHostUid" => $event_host_uid,
			"eventName" => $event_name,
			"eventInviteTypeLabel" => $event_invite_type_label,
			"eventPrivacyLabel" => $event_privacy_label,
			"eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
			"eventStartDatetime" => $event_start_datetime,
			"eventEndDatetime" => $event_end_datetime,
			"eventGpsLatitude" => $event_gps_latitude,
			"eventGpsLongitude" => $event_gps_longitude,
			"eventLikeCount" => $event_like_count,
			"eventDislikeCount" => $event_dislike_count,
			"eventViewCount" => $event_view_count
		);
		array_push($eventList, $event);
	}

	$statement->close();

	return $eventList;
}



/* FUNCTION: dbGetEventDataByTopNViews
 * DESCRIPTION: Gets the data of an entire event for all of the events that have 
 *              the top N count of views, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNViews($top_n_views)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, event_host_uid, event_name,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_EVENT_USER ON T_EVENT.eid = T_EVENT_USER.eid
			  ORDER BY event_view_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n_views);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_name,
			$event_invite_type_label, $event_privacy_label,
			$event_image_upload_allowed_indicator, $event_start_datetime,
			$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
			$event_like_count, $event_dislike_count, $event_view_count);

	$eventList = array();

	while($statement->fetch())
	{
		$event = array
		(
				"eid" => $eid,
				"eventHostUid" => $event_host_uid,
				"eventName" => $event_name,
				"eventInviteTypeLabel" => $event_invite_type_label,
				"eventPrivacyLabel" => $event_privacy_label,
				"eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
				"eventStartDatetime" => $event_start_datetime,
				"eventEndDatetime" => $event_end_datetime,
				"eventGpsLatitude" => $event_gps_latitude,
				"eventGpsLongitude" => $event_gps_longitude,
				"eventLikeCount" => $event_like_count,
				"eventDislikeCount" => $event_dislike_count,
				"eventViewCount" => $event_view_count
		);
		array_push($eventList, $event);
	}

	$statement->close();

	return $eventList;
}



/* FUNCTION: dbGetEventDataByTopNLikes
 * DESCRIPTION: Gets the data of an entire event for all of the events that have
 *              the top N count of likes, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventDataByTopNLikes($top_n)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, event_host_uid, event_name,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_EVENT_USER ON T_EVENT.eid = T_EVENT_USER.eid
			  ORDER BY event_like_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_name,
			$event_invite_type_label, $event_privacy_label,
			$event_image_upload_allowed_indicator, $event_start_datetime,
			$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
			$event_like_count, $event_dislike_count, $event_view_count);

	$eventList = array();

	while($statement->fetch())
	{
		$event = array
		(
				"eid" => $eid,
				"eventHostUid" => $event_host_uid,
				"eventName" => $event_name,
				"eventInviteTypeLabel" => $event_invite_type_label,
				"eventPrivacyLabel" => $event_privacy_label,
				"eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
				"eventStartDatetime" => $event_start_datetime,
				"eventEndDatetime" => $event_end_datetime,
				"eventGpsLatitude" => $event_gps_latitude,
				"eventGpsLongitude" => $event_gps_longitude,
				"eventLikeCount" => $event_like_count,
				"eventDislikeCount" => $event_dislike_count,
				"eventViewCount" => $event_view_count
		);
		array_push($eventList, $event);
	}

	$statement->close();

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
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, event_host_uid, event_name,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_EVENT_USER ON T_EVENT.eid = T_EVENT_USER.eid
			  ORDER BY event_dislike_count DESC
			  LIMIT ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $top_n);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	/*
	 $event_start_datetime = "";
	 $event_end_datetime   = "";
	 $event_gps_latitude   = -1.0;
	 $event_gps_longitude  = -1.0;
	 */
	$statement->bind_result($eid, $event_host_uid, $event_name,
			$event_invite_type_label, $event_privacy_label,
			$event_image_upload_allowed_indicator, $event_start_datetime,
			$event_end_datetime, $event_gps_latitude, $event_gps_longitude,
			$event_like_count, $event_dislike_count, $event_view_count);

	$eventList = array();

	while($statement->fetch())
	{
		$event = array
		(
				"eid" => $eid,
				"eventHostUid" => $event_host_uid,
				"eventName" => $event_name,
				"eventInviteTypeLabel" => $event_invite_type_label,
				"eventPrivacyLabel" => $event_privacy_label,
				"eventImageUploadAllowedIndicator" => charToStrBool($event_image_upload_allowed_indicator),
				"eventStartDatetime" => $event_start_datetime,
				"eventEndDatetime" => $event_end_datetime,
				"eventGpsLatitude" => $event_gps_latitude,
				"eventGpsLongitude" => $event_gps_longitude,
				"eventLikeCount" => $event_like_count,
				"eventDislikeCount" => $event_dislike_count,
				"eventViewCount" => $event_view_count
		);
		array_push($eventList, $event);
	}

	$statement->close();

	return $eventList;
}



/* FUNCTION: incrementEventViewCount
 * DESCRIPTION: Incremenets the view count of the specified event by 1.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbIncrementEventViewCount($eid)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "UPDATE T_EVENT 
			  SET event_view_count = event_view_count + 1
			  WHERE eid = ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	
	if ($statement->affected_rows === 1)
	{
		return "Event view count successfully incremented by 1.";
	}
	else if ($statement->affected_rows === 0)
	{
		return "Event view count failed to increment because the event does not exist.";
	}
	else
	{
		return "QUERY FLAWED: Please contact the database administrator with this method's name 
			  because something went wrong!";
	}
	
	$statement->close();
}

?>