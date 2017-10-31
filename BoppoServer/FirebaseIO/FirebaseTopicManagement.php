<?php

function subscribeDevicesToTopics()
{
  // EDIT THE FOLLOWING LIST TO CONTAIN FIREBASE REGISTRATION IDENTIFIERS OF THE DEVICE
  $frid = "";
  // EDIT THE FOLLOWING VARIABLE TO CONTAIN THE BASE NAME OF THE TOPICS
  $topicBaseName = "";
  // EDIT THE FOLLOWING LIST TO CONTAIN THE ID FOR THE TOPICS
  $topicIds = array
  (
  
  );
  
  
  
  
  
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
    
  $curlResults = array();
  $curlResponses = array();
  $curlErrors = array();
  foreach ($topicIds as $topicId)
  {
    // Form the URLs for cURL
    // URL TEMPLATE: "https://iid.googleapis.com/iid/v1/REGISTRATION_TOKEN/rel/topics/TOPIC_NAME"
    $urlTemplate = "https://iid.googleapis.com/iid/v1/$frid/rel/topics/";
  
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $urlTemplate . $topicBaseName . "-" . $topicId);
    curl_setopt($curl, CURLOPT_POST, true);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $HEADER);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    array_push($curlResults, curl_exec($curl));
    array_push($curlErrors, curl_error($curl));
    array_push($curlResponses, curl_getinfo($curl, CURLINFO_HTTP_CODE));
    curl_close($curl);
  }
  
  print_r($curlResults);
  print_r($curlResponses);
  print_r($curlErrors);
}

?>