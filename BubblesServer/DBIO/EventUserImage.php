<?php

/* FUNCTION:    dbAddImageToEvent
 * DESCRIPTION: Adds the image with the specified uiid to the event with the
 *              specified eid for the user with the specified uid.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbAddImageToEvent($eid, $uiid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // ACQUIRE THE USER IMAGE SEQUENCE
  $query = "INSERT INTO R_EVENT_USER_IMAGE (eid, uiid) VALUES (?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $eid, $uiid);
  $statement->execute();
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->close();

  // RETURN THE USER IMAGE SEQUENCE
  return "Success";
}



/* FUNCTION:    dbSetEuiEventProfileSequence
 * DESCRIPTION: Sets sequence of the event user image for the event profile.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetEuiEventProfileSequence($eid, $uiid, $euiEventProfileSequence)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "UPDATE R_EVENT_USER_IMAGE 
            SET    eui_event_profile_sequence = ? 
            WHERE  eid = ? AND uiid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("iii", $euiEventProfileSequence, $eid, $uiid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // RETURN A SUCCESS CONFIRMATION MESSAGE
  if ($statement->affected_rows === 0)
    return "Event user image event profile sequence has failed to update:
            no event user image has been updated.";
  else if ($statement->affected_rows === 1)
    return "Event user image event profile sequence has been successfully updated.";
  else
    return "Event user image event profile sequence has failed to update: 
            multiple event user images have been updated.";

  $statement->close();
}

?>