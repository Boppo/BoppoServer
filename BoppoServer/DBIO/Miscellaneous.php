<?php

/* FUNCTION:    incrementObjectViewCount
 * DESCRIPTION: Incremenets the view count of the specified object by 1.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbIncrementObjectViewCount($oid, $object_type_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

	// EXECUTE THE QUERY
	if (strcmp($object_type_label, "User Image") === 0)
		$query = "UPDATE T_USER_IMAGE
				  SET user_image_view_count = user_image_view_count + 1
				  WHERE uiid = ?;";
	else if (strcmp($object_type_label, "Event") === 0)
		$query = "UPDATE T_EVENT
				  SET event_view_count = event_view_count + 1
				  WHERE eid = ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $oid);
	$statement->execute();

	if ($statement->affected_rows === 1)
	{
		return "Object view count successfully incremented by 1.";
	}
	else if ($statement->affected_rows === 0)
	{
		return "Object view count failed to increment because the object or object instance does not exist.";
	}
	else
	{
		return "QUERY FLAWED: Please contact the database administrator with this method's name
			  because something went wrong!";
	}

	$statement->close();
}

?>