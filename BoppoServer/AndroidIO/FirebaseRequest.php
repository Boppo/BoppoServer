<?php

$function = $_GET['function'];

if ($function == "addDeviceToFirebaseAndDb")
  addDeviceToFirebaseAndDb();
if ($function == "removeDeviceFromFirebaseAndDb")
  removeDeviceFromFirebaseAndDb();
if ($function == "getDeviceSubscribedTopics")
  getDeviceSubscribedTopics();
/*
if ($function == "deleteDeviceFromFirebaseAndDb")
  deleteDeviceFromFirebaseAndDb();
*/

  
  
/* FUNCTION:    addDeviceToFirebaseAndDb
 * DESCRIPTION: Adds the specified device to the Firebase Cloud Messaging service 
 *              and database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function addDeviceToFirebaseAndDb()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/DeviceGroup.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';
  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $uid  = $json_decoded["uid"];
  $frid = $json_decoded["frid"]; 
  
  if (!isset($frid))
  {
    echo json_encode(formatResponseError("The frid was not provided.")); return;
  }
   
  removeDeviceFromDeviceGroup($frid);
  $result = dbSetDeviceUser($frid, $uid); 
  if (contains($result, "responseType") && contains($result, "ERROR")) {
    echo $result; return; }
  addDeviceToDeviceGroup($frid, $uid);
  
  echo json_encode($result);
  return; 
}



/* FUNCTION:    removeDeviceFromFirebaseAndDb
 * DESCRIPTION: Removes the specified device from the Firebase Cloud Messaging 
 *              service and database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function removeDeviceFromFirebaseAndDb()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/DeviceGroup.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';
  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $frid = $json_decoded["frid"];

  if (!isset($frid))
  {
    echo json_encode(formatResponseError("The frid was not provided.")); return;
  }
   
  removeDeviceFromDeviceGroup($frid);
  $result = dbUnsetDeviceUser($frid);

  echo json_encode($result);
  return;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* This method may not longer be required but a device-group level one may be */

/* FUNCTION:    getDeviceSubscribedTopics
 * DESCRIPTION: Retrieves and returns all of the topics to which the device with 
 *              the input Firebase Registration Identifier is subscribed. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getDeviceSubscribedTopics()
{
  /*
   * CONTINUE HERE
   * 
   * - Get the list of all topics to which the device is subscribed 
   * - Return the list to that device
   * - Have the device unsubscribe from all those topics 
   * - After unsubscribing, call another method to acknowledge and 
   *     delete the device and its subscribed topics from the database
   */
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */

  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  
  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $frid = $json_decoded["frid"];

  if (!isset($frid))
  {
    echo json_encode(formatResponseError("The frid was not provided.")); return;
  }

  $results = dbGetDeviceSubscribedTopics($frid);
  echo json_encode($results);
  return;
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>