<?php

$function = $_GET['function'];

if ($function == "addImagesToEvent")
  addImagesToEvent();
if ($function == 'setEuiEventProfileSequence')
  setEuiEventProfileSequence();

  
  
  
    
/* FUNCTION:    addImagesToEvent
 * DESCRIPTION: Adds the images with the uiids in the specified list to the
 *              specified event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function addImagesToEvent()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // DECODE JSON STRING //
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES //
  $eid   = $json_decoded["eid"];
  $uiids = $json_decoded["uiids"];

  // FOR EVERY USER IMAGE IDENTIFIER (UIID), ADD IT IMAGE TO THE EVENT //
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/EventUserImage.php';

  $responses = array();
  foreach($uiids as $uiid)
  {
    $response = dbAddImageToEvent($eid, $uiid);
    array_push($responses, $response);
  }

  echo json_encode($responses);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    setEuiEventProfileSequence
 * DESCRIPTION: Sets sequence of the event user image for the event profile.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function setEuiEventProfileSequence()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // DECODE JSON STRING //
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES //
  $eid   = $json_decoded["eid"];
  $uiid = $json_decoded["uiid"];
  $euiEventProfileSequence = $json_decoded["euiEventProfileSequence"];

  // FOR EVERY USER IMAGE IDENTIFIER (UIID), ADD IT IMAGE TO THE EVENT //
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/EventUserImage.php';

  $response = dbSetEuiEventProfileSequence($eid, $uiid, $euiEventProfileSequence);

  echo json_encode($response);
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>