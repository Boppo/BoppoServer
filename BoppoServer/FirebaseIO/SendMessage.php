<?php

// NOTE: The $fbRegistrationId is the Google Cloud Messaging ID assigned to the application after
//       having it added to Firebase. I will store the value in Resources/GlobalVariables.json.
function sendMessage($fbTitleName, $fbSubtitleName, $fbMessageText, $fbTickerText,
    $fbVibrateIndicator, $fbSoundIndicator, $fbLargeIconText, $fbSmallIconText)
{
  // Fetch the API_ACCESS_KEY from the GlobalVariables
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $API_ACCESS_KEY = $array_gv["Firebase"]["API_ACCESS_KEY"];

  // Form the URL for cURL
  $url = "https://iid.googleapis.com/iid/v1/REGISTRATION_TOKEN/rel/topics/TOPIC_NAME";

  // Prepare the Firebase Message
  // TO-DO: Once this works, try and see if reordering the array parameters will work
  $fbMessage = array
  (
      "message"    => $fbMessageText,
      "title"      => $fbTitleName,
      "subtitle"   => $fbSubtitleName,
      "tickerText" => $fbTickerText,
      "vibrate"    => $fbVibrateIndicator,
      "sound"      => $fbSoundIndicator,
      "largeIcon"  => $fbLargeIconText,
      "smallIcon"  => $fbSmallIconText
  );

  $fbFields = array
  (
      // Instead of RegistrationIds, we will list a topic here. Or have this be conditional.
      "data"       => $fbMessage,
  );
}

?>