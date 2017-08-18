<?php

/* FUNCTION: dbGetObjectTypeCode
 * DESCRIPTION: Retrieves and returns the code representing a type of an object.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetObjectTypeCode($object_type_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	
	// ACQUIRE THE INVITE TYPE LABEL
	$query = "SELECT object_type_code 
			  FROM T_OBJECT_TYPE
			  WHERE object_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("s", $object_type_label);
	$statement->execute();
	$statement->error;
	
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	$error = $statement->error;
	if ($error != "") { return formatResponseError($error); }
	
	// DEFAULT AND ASSIGN THE INVITE TYPE CODE
	$object_type_code = -1;
	$statement->bind_result($object_type_code);
	$statement->fetch();
	$statement->close();
	
	// RETURN THE INVITE TYPE CODE
	return $object_type_code;
}
?>