<?php

$function = $_GET['function'];

if ($function == "createEvent")
	createEvent();

	
	
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
	$event_name = $json_decoded["eventName"];
	$event_host_uid = $json_decoded["eventHostUid"];
	$event_privacy_label = $json_decoded["eventPrivacyLabel"];
	$event_invite_type_label = $json_decoded["eventInviteTypeLabel"];
	$event_image_upload_allowed_indicator = $json_decoded["eventImageUploadAllowedIndicator"];
	$event_start_datetime = $json_decoded["eventStartDatetime"];
	$event_end_datetime = $json_decoded["eventEndDatetime"];
	$event_gps_latitude = $json_decoded["eventGpsLatitude"];
	$event_gps_longitude = $json_decoded["eventGpsLongitude"];
		
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
	}
	
	$conn->commit();
	$conn->autocommit(TRUE);
	
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	echo "Success";	
}
	
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */