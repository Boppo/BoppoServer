<?php

$function = $_GET['function'];

if ($function == 'getImagesByPrivacyAndPurpose')
	getImagesByPrivacyAndPurpose();
if ($function == 'getImageProfileMaxAmount')
	getImageProfileMaxAmount();

	
	
/* FUNCTION:    getImageProfileMaxAmount
 * DESCRIPTION: Gets the integer that represents the maximum amount of allowed
 *              profile images. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getImageProfileMaxAmount()
{
	/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
	error_reporting(E_ALL);
	ini_set('display_errors', TRUE);
	ini_set('display_startup_errors', TRUE);
	/* END. */

	// FETCH THE DATA REPRESENTING THE MAXIMUM AMOUNT OF ALLOWED PROFILE IMAGES.
	$path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Resources/GlobalVariables.json';
	$file_gv = file_get_contents($path_gv);
	$array_gv = json_decode($file_gv, true);
	$image_profile_max_amount = $array_gv["Image"]["ImageProfileMaxAmount"];
	
	// RETURN THE EVENT ID
    echo $image_profile_max_amount;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
?>