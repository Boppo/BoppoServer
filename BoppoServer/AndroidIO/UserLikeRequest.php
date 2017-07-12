<?php

$function = $_GET['function'];

if ($function == "setObjectLikeOrDislike")
	setObjectLikeOrDislike();





/* FUNCTION:    setObjectLikeOrDislike
 * DESCRIPTION: Sets the specified object in the specified table to be liked or 
 *              disliked (as specified) by the specified user.
 *              In other words, this may set an Event with ID ## to be liked or  
 *              disliked by the user with ID #####.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function setObjectLikeOrDislike()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid = $json_decoded["uid"];
	$object_type_label = $json_decoded["objectTypeLabel"];
	$oid = $json_decoded["oid"];
	$user_like_indicator = $json_decoded["userLikeIndicator"];
	
	// CONVERT THE OBJECT TYPE LABEL TO AN OBJECT TYPE CODE
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
	$object_type_code = fetchObjectTypeCode($object_type_label);
	// CONVERT THE USER LIKE INDICATOR TO A CHARACTER
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	$user_like_indicator = strBoolToChar($user_like_indicator); 
	
	// PASS THE PARAMETERS TO THE DBIO METHOD TO SET THE OBJECT LIKE OR DISLIKE FOR THE USER
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserLike.php';
	$response = dbSetObjectLikeOrDislike($uid, $object_type_code, $oid, $user_like_indicator);

	// RETURN THE RESPONSE
	echo $response;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
	
?>