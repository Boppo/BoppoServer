<?php

$function = $_GET['function'];

if ($function == "setObjectComment")
	setObjectComment();
if ($function == "getObjectComments")
	getObjectComments();

	
	
	
	
/* FUNCTION:    setObjectComment
 * DESCRIPTION: Adds the specified comment of the specified user to the specified 
 *              object type with the specified object id at the specified 
 *              timestamp. If the comment is a reply to an existing comment, its 
 *              parent UCID (parent user comment ID) must also have been specified 
 *              for this request. If this is an existing comment, only its contents 
 *              and timestamp will be updated instead. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function setObjectComment()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$uid = $json_decoded["uid"];
	$object_type_label = $json_decoded["objectTypeLabel"];
	$oid = $json_decoded["oid"];
	$user_comment_upsert_timestamp = $json_decoded["userCommentUpsertTimestamp"];
	$user_comment = $json_decoded["userComment"];
	$parent_ucid = $json_decoded["parentUcid"];

	// CONVERT THE OBJECT TYPE LABEL TO AN OBJECT TYPE CODE
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
	$object_type_code = fetchObjectTypeCode($object_type_label);

	// PASS THE PARAMETERS TO THE DBIO METHOD TO SET THE OBJECT COMMENT FOR THE USER
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserComment.php';
	$response = dbSetObjectComment($uid, $object_type_code, $oid, $user_comment_upsert_timestamp, 
		$user_comment, $parent_ucid);

	// RETURN THE RESPONSE
	echo $response;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    getObjectComments
 * DESCRIPTION: Retrieves the comments and all of their associated data for the 
 *              specified type of object with the specified identifier. This may, 
 *              for example, retrieve all of the comments that were added to a 
 *              particular event, user, or user image.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getObjectComments()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	// DECODE JSON STRING
	$json_decoded = json_decode(file_get_contents("php://input"), true);
	// ASSIGN THE JSON VALUES TO VARIABLES
	$object_type_label = $json_decoded["objectTypeLabel"];
	$oid = $json_decoded["oid"];
	/*$object_type_label = "Event";
	$oid = 56;*/

	// CONVERT THE OBJECT TYPE LABEL TO AN OBJECT TYPE CODE
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
	$object_type_code = fetchObjectTypeCode($object_type_label);

	// PASS THE PARAMETERS TO THE DBIO METHOD TO GET THE OBJECT COMMENTS 
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserComment.php';
	$response = dbGetObjectComments($object_type_code, $oid);

	// RETURN THE RESPONSE
	echo json_encode($response);
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
	
?>