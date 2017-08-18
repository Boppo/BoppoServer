<?php

/* FUNCTION:    dbSetDeviceUser
 * DESCRIPTION: Sets the association between the user with the specified UID and 
 *              the device with the specified Firebase Registration Identifier
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetDeviceUser($firebaseRegistrationIdentifier, $uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_upsertDeviceUser(?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("si", $firebaseRegistrationIdentifier, $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }

  // RETURN RESULT MESSAGE  
  if ($statement->affected_rows === 1)
  {
    return json_encode(formatResponseSuccess("User has been successfully registered to device."));
  }
  else if ($statement->affected_rows === 0)
  {
    return json_encode(formatResponseSuccess("User is already registered to device."));
  }
  else
  {
    return formatResponseError("QUERY FLAWED: Please contact the database administrator with this method's name
      because something went wrong!");
  }

  $statement->close();
}



/* FUNCTION:    dbSubscribeDeviceToTopic
 * DESCRIPTION: Subscribes the device with the specified Firebase Registration 
 *              Identifier to the specified topic. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSubscribeDeviceToTopic($firebaseRegistrationIdentifier, $topicName)
{ 
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "INSERT INTO T_SUBSCRIBED_TOPIC (did, topic_name) 
            VALUES (
               (SELECT did 
                FROM T_DEVICE 
                WHERE device_firebase_registration_identifier = ?), 
              ?
            )";
  $statement = $conn->prepare($query);
  $statement->bind_param("ss", $firebaseRegistrationIdentifier, $topicName);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }

  // RETURN RESULT MESSAGE
  if ($statement->affected_rows === 1)
  {
    return json_encode(formatResponseSuccess("Device has been successfully subscribed to topic."));
  }
  else if ($statement->affected_rows === 0)
  {
    return json_encode(formatResponseSuccess("Device is already subscribed to topic."));
  }
  else
  {
    return formatResponseError("QUERY FLAWED: Please contact the database administrator with this method's name
      because something went wrong!");
  }

  $statement->close();
}



/* FUNCTION:    getDeviceSubscribedTopics
 * DESCRIPTION: Retrieves and returns all of the topics to which the device with 
 *              the input Firebase Registration Identifier is subscribed. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetDeviceSubscribedTopics($firebaseRegistrationIdentifier)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT topic_name 
            FROM T_SUBSCRIBED_TOPIC 
            WHERE did = 
            (
                SELECT did 
                FROM T_DEVICE 
                WHERE device_firebase_registration_identifier = ?
            )";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $firebaseRegistrationIdentifier);
  $statement->execute();

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }
  
  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($topic_name);

  // RETURN RESULT MESSAGE
  $topicNames = array();
  while ($statement->fetch())
  {
    array_push($topicNames, $topic_name);
  }
  $parent = array
  (
      "topicNames" => $topicNames
  );
  
  return $parent;

  $statement->close();
}



/* FUNCTION:    dbGetUserDevices
 * DESCRIPTION: Retrieves and returns all of the devices to which the user with
 *              the input UID is currently logged in.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetUserDeviceFirebaseRegistrationIdentifiers($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT device_firebase_registration_identifier  
            FROM T_DEVICE 
            WHERE device_latest_uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->num_rows === 0) {
    return formatResponseSuccess("User is not logged in to any device.");
  }
  if ($error != "") { return formatResponseError($error); }
  
  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($device_firebase_registration_identifier);
  $statement->fetch();
  
  $deviceFirebaseRegistrationIdentifiers = array();
  while($statement->fetch())
  {
    array_push($deviceFirebaseRegistrationIdentifiers, $device_firebase_registration_identifier);
  }
  
  $statement->close();
  
  $parent = array
  (
    "deviceFirebaseRegistrationIdentifiers" => $deviceFirebaseRegistrationIdentifiers  
  );
  
  return $parent;
}

?>