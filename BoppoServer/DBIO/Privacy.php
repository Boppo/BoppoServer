<?php

/* FUNCTION: getPrivacyCode
 * DESCRIPTION: Retrieves and returns the code representing a privacy.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchPrivacyCode($privacy_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
	
	// ACQUIRE THE PRIVACY LABEL
	$query = "SELECT privacy_code
			  FROM T_PRIVACY
			  WHERE privacy_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $privacy_label);
	$statement->execute();
	$statement->error;
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE PRIVACY CODE
	$privacy_code = null;
	$statement->bind_result($privacy_code);
	$statement->fetch();
	$statement->close(); 
	
	// RETURN THE INVITE TYPE CODE
	return $privacy_code;
}
?>