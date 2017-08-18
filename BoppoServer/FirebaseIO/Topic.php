<?php

/* FUNCTION:    subscribeDeviceToTopics
 * DESCRIPTION: Subscribes the device with the specified Firebase Registration 
 *              Identifier to all of the input topics' IDs for the specified type
 *              of object (User, User Image, Event, etc.). 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function subscribeDeviceToTopics($firebaseRegistrationIdentifier, $objectTypeLabel, $topics) 
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
  $HEADER         = array(
                      'Content-Type:application/json', 
                      'Content-Length:0', 
                      'Authorization:key=' . $API_ACCESS_KEY          
                    );
  
  // Fetch the object type code for the specified objectTypeLabel
  // Ex: x for the User, y for the User Image, z for the Event
  $objectTypeCode = dbGetObjectTypeCode($objectTypeLabel);
  if (contains(json_encode($objectTypeCode), "responseType"))
    return $objectTypeCode;
  
  // Form the URLs for cURL
  // URL TEMPLATE: "https://iid.googleapis.com/iid/v1/REGISTRATION_TOKEN/rel/topics/TOPIC_NAME"
  $urlTemplate = "https://iid.googleapis.com/iid/v1/$firebaseRegistrationIdentifier/rel/topics/"; 
  $curlResults = array(); 
  $curlResponses = array();
  $curlErrors = array();
  foreach ($topics as $id)
  {    
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $urlTemplate . $objectTypeCode . "-" . $id);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    array_push($curlResults, curl_exec($curl));
    array_push($curlErrors, curl_error($curl));
    array_push($curlResponses, curl_getinfo($curl, CURLINFO_HTTP_CODE));
    curl_close($curl);
  }
    
  $dbResponses = array();
  for ($i = 0; $i < sizeof($topics); $i++)
  {    
    if ($curlResponses[$i] === 200)
    {      
      $result = dbSubscribeDeviceToTopic($firebaseRegistrationIdentifier, $objectTypeCode . "-" . $topics[$i]);
      if (!contains($result, "Device has been successfully subscribed to topic."))
      {
        // TO-DO: If the DB insert fails and the device is not subscribed to the topic,
        // unsubscribe the device from the topic in FCM. 
        return $result;
      }
      else 
      {
        array_push($dbResponses, $result); 
      }
    }
  }
  
  $parent = array
  (
      "curlResults"   => $curlResults,
      "curlResponses" => $curlResponses,
      "curlErrors"    => $curlErrors, 
      "dbResponses"   => $dbResponses
  );
  
  return $parent;
}



/* FUNCTION:    subscribeDeviceToFriendUserTopics
 * DESCRIPTION: Subscribes the device with the specified Firebase Registration
 *              Identifier to all of the input friends' (users) UIDs.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function subscribeDevicesToEvent($devices, $eid)
{
  echo "<br>DEVIES: <br>";
  print_r($devices);
  echo "<br>EID: " . $eid . "<br>";
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv        = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv        = file_get_contents($path_gv);
  $array_gv       = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];
  $HEADER         = array(
      'Content-Type:application/json',
      'Content-Length:0',
      'Authorization:key=' . $API_ACCESS_KEY
  );
  
  $deviceFirebaseRegistrationIdentifiers = $devices["deviceFirebaseRegistrationIdentifiers"];
  
  $curlResults = array();
  $curlResponses = array();
  $curlErrors = array();
  $dbResponses = array();
  foreach ($deviceFirebaseRegistrationIdentifiers as $firebaseRegistrationIdentifier)
  {
    // Form the URLs for cURL
    // URL TEMPLATE: "https://iid.googleapis.com/iid/v1/REGISTRATION_TOKEN/rel/topics/TOPIC_NAME"
    $urlTemplate = "https://iid.googleapis.com/iid/v1/$firebaseRegistrationIdentifier/rel/topics/";

    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $urlTemplate . "eid-" . $eid);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    array_push($curlResults, curl_exec($curl));
    array_push($curlErrors, curl_error($curl));
    array_push($curlResponses, curl_getinfo($curl, CURLINFO_HTTP_CODE));
    curl_close($curl);
    
    if ($curlResponses[count($curlResponses)-1] === 200)
    {
      $result = dbSubscribeDeviceToTopic($firebaseRegistrationIdentifier, "eid-" . $eid);
      array_push($dbResponses, $result);
    }
    else 
    {
      array_push($dbResponses, formatResponseError(
        "The device has not been subscribed to topic in database because it failed to be registered to topic in FCM. ")
      );
    }
  }
  
  $parent = array
  (
      "curlResults"   => $curlResults,
      "curlResponses" => $curlResponses,
      "curlErrors"    => $curlErrors,
      "dbResponses"   => $dbResponses
  );
  
  return $parent;
}

?>