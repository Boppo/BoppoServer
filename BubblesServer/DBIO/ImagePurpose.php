<?php

/* FUNCTION: fetchImagePurposeCode
 * DESCRIPTION: Retrieves and returns the code representing an image purpose.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchImagePurposeCode($image_purpose_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
	
	// ACQUIRE THE IMAGE PURPOSE LABEL
	$query = "SELECT image_purpose_code
			  FROM T_IMAGE_PURPOSE
			  WHERE image_purpose_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $image_purpose_label);
	$statement->execute();
	$statement->error;
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE IMAGE PURPOSE CODE
	$privacy_code = null;
	$statement->bind_result($image_purpose_code);
	$statement->fetch();
	$statement->close(); 
	
	// RETURN THE IMAGE PURPOSE CODE
	return $image_purpose_code;
}
?>