<?php

$function = $_GET['function'];

if ($function == "subscribeDevice")
  subscribeDevice();
if ($function == "unsubscribeDevice")
  unsubscribeDevice();

/*
if ($function == "addDeviceToFirebaseAndDb")
  addDeviceToFirebaseAndDb();
if ($function == "removeDeviceFromFirebaseAndDb")
  removeDeviceFromFirebaseAndDb();
if ($function == "getDeviceSubscribedTopics")
  getDeviceSubscribedTopics();
*/
/*
if ($function == "deleteDeviceFromFirebaseAndDb")
  deleteDeviceFromFirebaseAndDb();
*/

  

/* FUNCTION:    subscribeDevice
 * DESCRIPTION: Subscribes the device with the specified FRID for the user with the
 *              specified UID to all topics subscribed to by that user in the 
 *              database as well as in Firebase. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

 function subscribeDevice()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //
  
  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';
  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $uid  = $json_decoded["uid"];
  $frid = $json_decoded["frid"];
  
  if (!isset($uid))
  {
    echo json_encode(formatResponseError("The uid was not provided.")); return;
  }
  if (!isset($frid))
  {
    echo json_encode(formatResponseError("The frid was not provided.")); return;
  }
  
  $setDeviceUserResponse = dbSetDeviceUser($uid, $frid); 
  if (!contains(json_encode($setDeviceUserResponse), "Success"))
  {
    echo json_encode($setDeviceUserResponse); return; 
  }
  
  $subscribeDeviceToTopicsResponse = subscribeDeviceToTopics($frid);

  saveToSystemLog(json_encode($subscribeDeviceToTopicsResponse), __FUNCTION__);
  echo json_encode($subscribeDeviceToTopicsResponse); 
}

/* FUNCTION:    unsubscribeDevice
 * DESCRIPTION: Unsubscribes the device with the specified FRID from all topics 
 *              subscribed to in the database as well as in Firebase.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function unsubscribeDevice()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';
  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $frid = $json_decoded["frid"];

  if (!isset($frid))
  {
    echo json_encode(formatResponseError("The frid was not provided.")); return;
  }
  
  // Firebase stuff here
  $unsubscribeDeviceFromTopicsResponse = unsubscribeDeviceFromTopics($frid);
  // Consider building a fail-safe here that loops through the above variable's firebase responses 
  //   and checks for anything other than "200". If exists !200, prevent DB-side unsubscription. 
  // Firebase stuff here
  
  $unsetDeviceUserResponse = dbUnsetDeviceUser($frid);
  if (!contains(json_encode($unsetDeviceUserResponse), "Success"))
  {
    echo json_encode($unsetDeviceUserResponse); return;
  }

  saveToSystemLog(json_encode($unsetDeviceUserResponse), __FUNCTION__);
  echo json_encode($unsetDeviceUserResponse);
}

/* FUNCTION:    addDeviceToFirebaseAndDb
 * DESCRIPTION: Adds the specified device to the Firebase Cloud Messaging service 
 *              and database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function addDeviceToFirebaseAndDb()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

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
*/



/* FUNCTION:    removeDeviceFromFirebaseAndDb
 * DESCRIPTION: Removes the specified device from the Firebase Cloud Messaging 
 *              service and database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function removeDeviceFromFirebaseAndDb()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

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
*/

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
/*
function getDeviceSubscribedTopics()
{
  /*
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

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
*/

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>