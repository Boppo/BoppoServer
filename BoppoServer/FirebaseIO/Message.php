<?php

function sendMessageToTopic($objectTypeLabel, $topic, 
    $titleName, $body, $icon, $sound)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  
  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];
  $HEADER         = array(
      'Content-Type:application/json',
      'Authorization:key=' . $API_ACCESS_KEY
  );

  // Form the URL for cURL
  $url = "https://fcm.googleapis.com/fcm/send";
  
  // Fetch the object type code for the specified objectTypeLabel
  // Ex: x for the User, y for the User Image, z for the Event
  $objectTypeCode = dbGetObjectTypeCode($objectTypeLabel);
  if (contains(json_encode($objectTypeCode), "responseType"))
  {
    saveToErrorLog(json_encode($objectTypeCode), __FUNCTION__);
    return;
  }
  
  // Prepare the Firebase Message
  $notification = array
  (
      "title"      => $titleName,
      "body"       => $body,
      "icon"       => $icon, 
      "sound"      => $sound
  );
  
  $id = $topic;
  $to = "/topics/" . $objectTypeCode . "-" . $id;
  $message = array
  (
      "to" => $to,
      "notification" => $notification
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($message));
  $curlResult = curl_exec($curl);
  $curlError = curl_error($curl);
  $curlResponse = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);
  
  $parent = array
  (
    "curlResult" => $curlResult, 
    "curlError" => $curlError, 
    "curlResponse" => $curlResponse
  );
  
  saveToSystemLog(json_encode($parent), __FUNCTION__);
  
  return formatResponseSuccess("Message sent to topic successfully.");
}



function sendMessageToUser($objectTypeLabel, $uid,
    $titleName, $body, $icon, $sound)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';

  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];
  $HEADER         = array(
      'Content-Type:application/json',
      'Authorization:key=' . $API_ACCESS_KEY
  );

  // Form the URL for cURL
  $url = "https://fcm.googleapis.com/fcm/send";

  // Fetch the object type code for the specified objectTypeLabel
  // Ex: x for the User, y for the User Image, z for the Event
  $objectTypeCode = dbGetObjectTypeCode($objectTypeLabel);
  if (contains(json_encode($objectTypeCode), "responseType"))
  {
    saveToErrorLog(json_encode($objectTypeCode), __FUNCTION__);
    return;
  }

  // Prepare the Firebase Message
  $notification = array
  (
      "title"      => $titleName,
      "body"       => $body,
      "icon"       => $icon,
      "sound"      => $sound
  );

  $frids = 
    dbGetUserDeviceFrids($uid)["deviceFrids"];
  $to = $frids; 
  $message = array
  (
      "to" => $to,
      "notification" => $notification
  );

  $curl = curl_init();
  curl_setopt($curl, CURLOPT_URL, $url);
  curl_setopt($curl, CURLOPT_POST, true);
  curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
  curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($message));
  $curlResult = curl_exec($curl);
  $curlError = curl_error($curl);
  $curlResponse = curl_getinfo($curl, CURLINFO_HTTP_CODE);
  curl_close($curl);

  $parent = array
  (
      "curlResult" => $curlResult,
      "curlError" => $curlError,
      "curlResponse" => $curlResponse
  );

  saveToSystemLog(json_encode($parent), __FUNCTION__);

  return formatResponseSuccess("Message sent to topic successfully.");
}

?>