<?php

$function = $_GET['function'];

if ($function == "incrementObjectViewCount")
	incrementObjectViewCount();


	
/* FUNCTION:    incrementObjectViewCount
 * DESCRIPTION: Incremenets the view count of the specified object by 1.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function incrementObjectViewCount()
{
	/*
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
	$oid = $json_decoded["oid"];
	$object_type_label = $json_decoded["objectTypeLabel"];

	// EXECUTE THE DBIO METHOD TO INCREMENT THE OBJECT VIEW COUNT AND GET THE RESPONSE
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Miscellaneous.php';
	$response = dbIncrementObjectViewCount($oid, $object_type_label);

	// RETURN THE RESPONSE
	echo $response;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
	
?>