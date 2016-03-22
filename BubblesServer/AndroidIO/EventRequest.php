<?php

$function = $_GET['function'];

if ($function == "createEvent")
	createEvent();
if ($function == "getEid")
	getEid();
if ($function == "getEventData")
	getEventData();
if ($function == "deleteEvent")
	deleteEvent();

	
	
/* FUNCTION: createEvent
 * DESCRIPTION: Adds an event into the corresponding database table.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function createEvent()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */
		
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$event_host_uid                       = $json_decoded["eventHostUid"];
	$event_name                           = $json_decoded["eventName"];
	$event_invite_type_label              = $json_decoded["eventInviteTypeLabel"];
	$event_privacy_label                  = $json_decoded["eventPrivacyLabel"];
	$event_image_upload_allowed_indicator = $json_decoded["eventImageUploadAllowedIndicator"];
	$event_start_datetime                 = $json_decoded["eventStartDatetime"];
	$event_end_datetime                   = $json_decoded["eventEndDatetime"];
	$event_gps_latitude                   = $json_decoded["eventGpsLatitude"];
	$event_gps_longitude                  = $json_decoded["eventGpsLongitude"];
		
	// ENCODE THE PRIVACY LABEL
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Privacy.php';
	$event_privacy_code = getPrivacyCode($event_privacy_label);
	if ($event_privacy_code == -1) { 
		echo "ERROR: Incorrect event privacy specified.";
		return; }
			
	// ENCODE THE INVITE TYPE LABEL
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/InviteType.php';
	$event_invite_type_code = getInviteTypeCode($event_invite_type_label);
	if ($event_invite_type_code == -1) {
		echo "ERROR: Incorrect event invite type specified.";
		return; }
				
	// CONVERT THE IMAGE UPLOAD ALLOWED INDICATOR TO A CHARACTER
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
	$event_image_upload_allowed_indicator = booleanToChar($event_image_upload_allowed_indicator);
	
	//echo "PRIVACY CODE: " . $event_privacy_code . "<br><br>"; 
	//echo "INVITE TYPE CODE: " . $event_invite_type_code . "<br><br>";
	//echo "IMAGE UPLOAD ALLOWED INDICATOR: " . $event_image_upload_allowed_indicator . "<br><br>";
	//echo "GPS LATITUDE: " . $event_gps_latitude . "<br><br>";
	//echo "GPS LONGITUDE: " . $event_gps_longitude . "<br><br>";
			
	// EXECUTE THE TRANSACTION
	$queries = array(
		"INSERT IGNORE INTO T_GEOLOCATION (gps_latitude, gps_longitude) 
		 	VALUES (?, ?)", 
		"INSERT INTO T_EVENT 
		    (event_name, event_host_uid, event_privacy_code, event_invite_type_code, 
			 event_image_upload_allowed_indicator, event_start_datetime, event_end_datetime, 
		     event_gps_latitude, event_gps_longitude)
		 VALUES 
		 	(?, ?, ?, ?, ?, ?, ?, ?, ?)"
	);
		
	$conn->autocommit(FALSE);
	
	$response = "PLACEHOLDER FOR RESPONSE";
	
	foreach ($queries as $query)
	{
		$statement = $conn->prepare($query);
		
		$index = array_search($query, $queries);
		if ($index === 0) {
			$statement->bind_param("dd", $event_gps_latitude, $event_gps_longitude);
		}
		elseif ($index === 1) {
			$statement->bind_param("siiiissdd", $event_name, $event_host_uid, $event_privacy_code,
				$event_invite_type_code, $event_image_upload_allowed_indicator,
				$event_start_datetime, $event_end_datetime, $event_gps_latitude, $event_gps_longitude);
		}
		
		$statement->execute();
		$error = $statement->error;
		// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
		if ($error != "") { echo "DB ERROR: " . $error; return; }
		$statement->close();
		
		if ($conn->insert_id != 0)
			$response = "Success. ID: " . $conn->insert_id;
	}
	
	$conn->commit();
	$conn->autocommit(TRUE);
	
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	echo $response;	
}
	
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getEid
 * DESCRIPTION: Gets an Event Identifier from the corresponding database table.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getEid()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$event_host_uid = $json_decoded["eventHostUid"];
	$event_name     = $json_decoded["eventName"];
		
	// EXECUTE THE QUERY
	$query = "SELECT eid 
			  FROM T_EVENT 
			  WHERE event_host_uid = ? AND event_name = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $event_host_uid, $event_name);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE EVENT ID
	$eid = 0;
	$statement->bind_result($eid);
	$statement->fetch();
	$statement->close();
	
	// RETURN THE EVENT ID
	echo $eid;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION: getEventData
 * DESCRIPTION: Gets the data of an entire event for the specified eid
 *              (Event Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getEventData()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */
	
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$eid = $json_decoded["eid"];

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
		"eventImageUploadAllowedIndicator" => charToBoolean($event_image_upload_allowed_indicator),
		"eventStartDatetime" => $event_start_datetime, 
		"eventEndDatetime" => $event_end_datetime, 
		"eventGpsLatitude" => $event_gps_latitude, 
		"eventGpsLongitude" => $event_gps_longitude, 
		"eventLikeCount" => $event_like_count, 
		"eventDislikeCount" => $event_dislike_count, 
		"eventViewCount" => $event_view_count
	);

	$statement->close();

	// RETURN THE EVENT ID
    echo json_encode($event);
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION: deleteEvent
 * DESCRIPTION: Deletes an Event with the specified Event Identifier from the 
 *              corresponding database table.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function deleteEvent()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$eid = $json_decoded["eid"];

	// EXECUTE THE QUERY
	$query = "DELETE 
			  FROM T_EVENT 
			  WHERE eid = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $eid);
	$statement->execute();
	$error = $statement->error;
	$statement->close();
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// RETURN A SUCCESS MESSAGE
	echo "Success.";
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */