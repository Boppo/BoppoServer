<?php

/* FUNCTION:    addDeviceToDeviceGroup
 * DESCRIPTION: Adds the devices with the specified Firebase Registration
 *              Identifier to the device group with the specified Firebase 
 *              Registration Identifier. 
 * USE CASES:
 *   - A Device Group may be used instead of a device to send a message to all of 
 *     a user's logged-in devices. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function addDeviceToDeviceGroup($deviceFrid, $uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv        = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv        = file_get_contents($path_gv);
  $array_gv       = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];
  $SENDER_ID      = $array_gv["Firebase"]["SENDER_ID"];
  $HEADER = array(
      'Content-Type:application/json',
      'Authorization:key=' . $API_ACCESS_KEY, 
      'project_id:' . $SENDER_ID
  );

  // Form the URLs for cURL
  $url = "https://android.googleapis.com/gcm/notification";
  
  // Fetch the object type code for the user type
  $objectTypeCode = dbGetObjectTypeCode("User");
  if (contains(json_encode($objectTypeCode), "responseType"))
    return $objectTypeCode; 
  
  // Fetch the device group frid if exists, create and fetch if not exists
  $deviceGroupFrid = dbGetDeviceGroupFrid($uid);
  if (contains(json_encode($deviceGroupFrid), "responseType") && contains(json_encode($deviceGroupFrid), "Success")) {
    $deviceGroupFrid = createDeviceGroup($deviceFrid, $uid); 
    return json_encode($deviceGroupFrid);
  }
  else if (contains(json_encode($deviceGroupFrid), "responseType") && contains(json_encode($deviceGroupFrid), "Error"))
    return json_encode($deviceGroupFrid);
  
  $message = array
  (
      "operation" => "add",
      "notification_key_name" => $objectTypeCode . "-" . $uid, 
      "notification_key" => $deviceGroupFrid, 
      "registration_ids" => array($deviceFrid)
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($message));
  $curlResult = json_decode(curl_exec($curl), true);
  $curlError = json_decode(curl_error($curl), true);
  $curlResponse = json_decode(curl_getinfo($curl, CURLINFO_HTTP_CODE), true);
  curl_close($curl);
  
  //echo "CHECK2: " . $curlResponse["notification_key"] . " END OF CHECK.";

  if ($curlResponse === 200)
  {
    $dbResponse = dbAddDeviceToDeviceGroup($deviceFrid, $deviceGroupFrid);
  }
  else
  {
    $dbResponse = ""; // No dbResponse because no DB call was made
  }
  
  $parent = array
  (
      "curlResult"   => $curlResult,
      "curlResponse" => $curlResponse,
      "curlError"    => $curlError,
      "dbResponse"   => $dbResponse
  );

  saveToSystemLog(json_encode($parent), __FUNCTION__);

  return json_encode($parent);
}



/* FUNCTION:    removeDeviceFromDeviceGroup
 * DESCRIPTION: Removes the device with the specified device Firebase Registration
 *              Identifier from the device group it is assigned to.
 * USE CASES:
 *   - A user logs in to a device. Because there is a chance this is a different 
 *     user logging into the device, it should be removed from its previous 
 *     user device group. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function removeDeviceFromDeviceGroup($deviceFrid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv        = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv        = file_get_contents($path_gv);
  $array_gv       = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];
  $SENDER_ID      = $array_gv["Firebase"]["SENDER_ID"];
  $HEADER = array(
      'Content-Type:application/json',
      'Authorization:key=' . $API_ACCESS_KEY,
      'project_id:' . $SENDER_ID
  );

  // Form the URLs for cURL
  $url = "https://android.googleapis.com/gcm/notification";

  // Fetch the object type code for the user type
  $objectTypeCode = dbGetObjectTypeCode("User");
  if (contains(json_encode($objectTypeCode), "responseType"))
    return $objectTypeCode;
  // Fetch the device data for the device FRID
  $device = dbGetDevice($deviceFrid);
  if (contains(json_encode($device), "responseType"))
    return $device;
  
  echo "DEBUG: " . json_encode($device);

  $message = array
  (
      "operation" => "remove",
      "notification_key_name" => $objectTypeCode . "-" . $device["device"]["deviceLatestUid"],
      "notification_key" => $device["device"]["deviceGroupFrid"],
      "registration_ids" => array($deviceFrid)
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($message));
  $curlResult = json_decode(curl_exec($curl));
  $curlError = json_decode(curl_error($curl));
  $curlResponse = json_decode(curl_getinfo($curl, CURLINFO_HTTP_CODE));
  curl_close($curl);

  if ($curlResponse === 200)
  {
    $dbResponse = dbRemoveDeviceFromDeviceGroup($deviceFrid);
  }
  else
  {
    $dbResponse = ""; // No dbResponse because no DB call was made
  }

  $parent = array
  (
      "curlResult"   => $curlResult,
      "curlResponse" => $curlResponse,
      "curlError"    => $curlError,
      "dbResponse"   => $dbResponse
  );

  saveToSystemLog(json_encode($parent), __FUNCTION__);

  return json_encode($parent);
}



/* FUNCTION:    createDeviceGroup
 * DESCRIPTION: Creates a firebase device group for the user with the specified 
 *              uid. 
 * USE CASES:
 *   - A Device Group may be used instead of a device to send a message to all of
 *     a user's logged-in devices. However, if one does not exist, it needs to be
 *     created by calling FCM first. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function createDeviceGroup($deviceFrid, $uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv        = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv        = file_get_contents($path_gv);
  $array_gv       = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];
  $SENDER_ID      = $array_gv["Firebase"]["SENDER_ID"];
  $HEADER = array(
      'Content-Type:application/json',
      'Authorization:key=' . $API_ACCESS_KEY,
      'project_id:' . $SENDER_ID
  );

  // Form the URLs for cURL
  $url = "https://android.googleapis.com/gcm/notification";

  // Fetch the object type code for the user type
  $objectTypeCode = dbGetObjectTypeCode("User");
  if (contains(json_encode($objectTypeCode), "responseType"))
    return $objectTypeCode;

  $message = array
  (
      "operation" => "create",
      "notification_key_name" => $objectTypeCode . "-" . $uid,
      "registration_ids" => array($deviceFrid)
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($message));
  $curlResult = json_decode(curl_exec($curl), true);
  $curlError = json_decode(curl_error($curl), true);
  $curlResponse = json_decode(curl_getinfo($curl, CURLINFO_HTTP_CODE), true);
  curl_close($curl);
  
  $deviceGroupFrid = $curlResult["notification_key"];

  $dbResponse = array();
  if ($curlResponse === 200)
  {
    array_push($dbResponse, dbCreateDeviceGroup($deviceGroupFrid));
    array_push($dbResponse, dbAddDeviceToDeviceGroup($deviceFrid, $deviceGroupFrid)); 
  }
  else
  {
    $dbResponse = ""; // No dbResponse because no DB call was made
  }

  $parent = array
  (
      "curlResult"   => $curlResult,
      "curlResponse" => $curlResponse,
      "curlError"    => $curlError,
      "dbResponse"   => $dbResponse
  );

  saveToSystemLog(json_encode($parent), __FUNCTION__);
  
  if ($curlResponse === 200)
  {
    return json_encode($curlResult["notification_key"]);
  }
  else
  {
    return json_encode($parent);
  }
}

?>