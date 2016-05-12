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



/* FUNCTION: fetchEventDataEncoded
 * DESCRIPTION: Fetches the data of an entire event for the specified eid
 *              (Event Identifier) with all of the labels encoded (i.e. those that
 *              are stored in reference/lookup tables).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchEventDataEncoded($eid)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT event_host_uid, event_name,
			         event_invite_type_code, event_privacy_code, event_image_upload_allowed_indicator,
			         event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
			         event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT 
			  WHERE  eid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// DEFAULT AND ASSIGN THE EVENT VARIABLES
	$event_start_datetime = null;
	$event_end_datetime   = null;
	$event_gps_latitude   = null;
	$event_gps_longitude  = null;
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
		"eventInviteTypeCode" => $event_invite_type_label,
		"eventPrivacyCode" => $event_privacy_label,
		"eventImageUploadAllowedIndicator" => $event_image_upload_allowed_indicator,
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
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid
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
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
		$event_host_first_name, $event_host_last_name, $event_name,
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
			"eventHostUsername" => $event_host_username,
			"eventHostFirstName" => $event_host_first_name,
			"eventHostLastName" => $event_host_last_name,
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



/* FUNCTION: fetchEventDataByMember
 * DESCRIPTION: Fetches the data of an entire event for all of the events of which 
 *  			the specified uid (User Identifier) is a member.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchEventDataByName($event_name)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// MAKE THE EVENT NAME SEARCH BE A PARTIAL MATCH SEARCH
	$event_name = "%".$event_name."%";

	// EXECUTE THE QUERY
	$query = "SELECT DISTINCT T_EVENT.eid, uid, username, first_name, last_name, event_name,
		       		 invite_type_label, privacy_label, event_image_upload_allowed_indicator,
		       		 event_start_datetime, event_end_datetime, event_gps_latitude, event_gps_longitude,
		       		 event_like_count, event_dislike_count, event_view_count
			  FROM   T_EVENT
		       		 LEFT JOIN T_INVITE_TYPE ON T_EVENT.event_invite_type_code = T_INVITE_TYPE.invite_type_code
		       		 LEFT JOIN T_PRIVACY ON T_EVENT.event_privacy_code = T_PRIVACY.privacy_code
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid
			  WHERE  event_name LIKE ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $event_name);
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
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
			$event_host_first_name, $event_host_last_name, $event_name,
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
				"eventHostUsername" => $event_host_username, 
				"eventHostFirstName" => $event_host_first_name, 
				"eventHostLastName" => $event_host_last_name, 
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
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid
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
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
			$event_host_first_name, $event_host_last_name, $event_name,
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
				"eventHostUsername" => $event_host_username,
				"eventHostFirstName" => $event_host_first_name,
				"eventHostLastName" => $event_host_last_name,
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
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid
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
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
			$event_host_first_name, $event_host_last_name, $event_name,
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
				"eventHostUsername" => $event_host_username,
				"eventHostFirstName" => $event_host_first_name,
				"eventHostLastName" => $event_host_last_name,
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
		       		 LEFT JOIN T_USER ON T_EVENT.event_host_uid = T_USER.uid
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
	$statement->bind_result($eid, $event_host_uid, $event_host_username, 
			$event_host_first_name, $event_host_last_name, $event_name,
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
			"eventHostUsername" => $event_host_username,
			"eventHostFirstName" => $event_host_first_name,
			"eventHostLastName" => $event_host_last_name,
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



/* FUNCTION:    dbUpdateEvent
 * DESCRIPTION: Updates an event into the corresponding database table with the
 *  			newly provided values.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbUpdateEvent($event)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// FETCH THE CURRENT VALUES FOR THIS EVENT
	$eventCurrent = fetchEventDataEncoded($event["eid"]);
	
	// UPDATE THE CURRENT VALUES WITH VALID NEW VALUES
	if ($event["eventHostUid"] != null)
		$eventCurrent["eventHostUid"] = $event["eventHostUid"];
	if ($event["eventName"] != null)
		$eventCurrent["eventName"] = $event["eventName"];
	if ($event["eventInviteTypeCode"] != null)
		$eventCurrent["eventInviteTypeCode"] = $event["eventInviteTypeCode"];
	if ($event["eventPrivacyCode"] != null)
		$eventCurrent["eventPrivacyCode"] = $event["eventPrivacyCode"];
	if ($event["eventImageUploadAllowedIndicator"] != null)
		$eventCurrent["eventImageUploadAllowedIndicator"] = $event["eventImageUploadAllowedIndicator"];
	if ($event["eventStartDatetime"] != null)
		$eventCurrent["eventStartDatetime"] = $event["eventStartDatetime"];
	if ($event["eventEndDatetime"] != null)
		$eventCurrent["eventEndDatetime"] = $event["eventEndDatetime"];
	if ($event["eventGpsLatitude"] != null)
		$eventCurrent["eventGpsLatitude"] = $event["eventGpsLatitude"];
	if ($event["eventGpsLongitude"] != null)
		$eventCurrent["eventGpsLongitude"] = $event["eventGpsLongitude"];
	
	// EXECUTE THE QUERY
	$query = "UPDATE T_EVENT 
			  SET event_host_uid = ?, 
			      event_name = ?,
			      event_invite_type_code = ?,
			      event_privacy_code = ?, 
			      event_image_upload_allowed_indicator = ?,
			      event_start_datetime = ?, 
			      event_end_datetime = ?, 
			      event_gps_latitude = ?, 
			      event_gps_longitude = ? 
		      WHERE eid = ?";
		
	$statement = $conn->prepare($query);
		
	$statement->bind_param("isiiissddi", $eventCurrent["eventHostUid"], $eventCurrent["eventName"], 
		$eventCurrent["eventInviteTypeCode"], $eventCurrent["eventPrivacyCode"], 
		$eventCurrent["eventImageUploadAllowedIndicator"], $eventCurrent["eventStartDatetime"], 
		$eventCurrent["eventEndDatetime"], $eventCurrent["eventGpsLatitude"], 
		$eventCurrent["eventGpsLongitude"], $event["eid"]);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { return "DB ERROR: " . $error; }
		
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	if ($statement->affected_rows === 1)
		return "Event has been successfully updated.";
	else 
		return "Event has failed to update: no event or multiple events have been updated.";
	
	$statement->close();
}

?>