<?php

/* FUNCTION:    dbGetEventCategoryCode 
 * DESCRIPTION: Retrieves and returns the code representing a category of an
 *              event. 
* --------------------------------------------------------------------------------
* ================================================================================
* -------------------------------------------------------------------------------- */
function dbGetEventCategoryCode($event_category_label)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT event_category_code  
			FROM T_EVENT_CATEGORY 
			WHERE event_category_label = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $event_category_label);
  $statement->execute();
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE INVITE TYPE CODE
  $event_category_code = null;
  $statement->bind_result($event_category_code);
  $statement->fetch();
  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $event_category_code;
}

/* FUNCTION:    dbGetEventTypeCode
 * DESCRIPTION: Retrieves and returns the code representing a type of an event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventTypeCode($event_category_label, $event_type_label)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
  
  $event_category_code = dbGetEventCategoryCode($event_category_label);

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT event_type_code
			FROM T_EVENT_TYPE
			WHERE event_category_code = ? AND event_type_label = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("is", $event_category_code, $event_type_label);
  $statement->execute();
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE INVITE TYPE CODE
  $event_type_code = null;
  $statement->bind_result($event_type_code);
  $statement->fetch();
  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $event_type_code;
}
?>