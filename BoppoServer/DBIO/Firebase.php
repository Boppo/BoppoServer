<?php

/* FUNCTION:    dbSetDeviceUser
 * DESCRIPTION: Sets the association between the user with the specified UID and 
 *              the device with the specified Firebase Registration Identifier
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetDeviceUser($uid, $frid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_upsertDeviceUser(?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("si", $frid, $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { return formatResponseError($error); }

  // RETURN RESULT MESSAGE  
  if ($statement->affected_rows === 1)
  {
    return formatResponseSuccess("User has been successfully registered to device.");
  }
  else if ($statement->affected_rows === 0)
  {
    return formatResponseSuccess("User is already registered to device.");
  }
  else
  {
    return formatResponseError("QUERY FLAWED: Please contact the database administrator with this method's name
      because something went wrong!");
  }

  $statement->close();
}



/* FUNCTION:    dbUnsetDeviceUser
 * DESCRIPTION: Unsets the user for the device with the specified Firebase 
 * Registration Identifier in the database. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbUnsetDeviceUser($frid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "UPDATE T_DEVICE 
            SET device_latest_uid = NULL
            WHERE device_frid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $frid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { return formatResponseError($error); }

  // RETURN RESULT MESSAGE
  if ($statement->affected_rows === 1)
  {
    return formatResponseSuccess("Device has been successfully removed.");
  }
  else if ($statement->affected_rows === 0)
  {
    return formatResponseSuccess("Device failed to be removed, possible because it is already not
        in the database.");
  }
  else
  {
    return formatResponseError("QUERY FLAWED: Please contact the database administrator with this method's name
      because something went wrong! [" . __FUNCTION__ . "].");
  }

  $statement->close();
}



/* FUNCTION:    dbSubscribeUserToTopic
 * DESCRIPTION: Subscribes the user with the specified UID to the topic (User, 
 *              Image, Event, etc. notifications) with the specified topicName. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSubscribeUserToTopic($uid, $topicName)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "INSERT INTO T_SUBSCRIBED_TOPIC (uid, topic_name) 
            VALUES (?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("is", $uid, $topicName);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->affected_rows === 0) {
    return formatResponseError("User subscription to topic failed, possibly because one with the specified
      uid and topic_name already exists.");
  }
  if ($statement->affected_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
      because something went wrong (" . __FUNCTION__ . ").");
  }
  if ($error != "") { return formatResponseError($error); }
  
  return formatResponseSuccess("User subscribed to topic successfully.");
  
  $statement->close();
}



/* FUNCTION:    dbUnsubscribeUserFromTopic
 * DESCRIPTION: Unsubscribes the user with the specified UID from the topic (User,
 *              Image, Event, etc. notifications) with the specified topicName.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbUnsubscribeUserFromTopic($uid, $topicName)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "DELETE 
            FROM T_SUBSCRIBED_TOPIC 
            WHERE uid = ? AND topic_name = ?"; 
  $statement = $conn->prepare($query);
  $statement->bind_param("is", $uid, $topicName);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->affected_rows === 0) {
    return formatResponseError("User unsubscription from topic failed, possibly because none with the specified
      uid and topic_name already exist.");
  }
  if ($statement->affected_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
      because something went wrong (" . __FUNCTION__ . ").");
  }
  if ($error != "") { return formatResponseError($error); }

  return formatResponseSuccess("User unsubscribed from topic successfully.");

  $statement->close();
}



/* FUNCTION:    dbGetDevices
 * DESCRIPTION: Retrieves and returns Device data of all of the devices used by the
 *              user with the specified UID. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetDevices($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT did, device_frid, device_latest_uid, device_latest_uid_update_timestamp 
            FROM T_DEVICE
            WHERE device_latest_uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($did, $device_frid, $device_latest_uid, $device_latest_uid_update_timestamp);

  // RETURN RESULT MESSAGE
  $devices = array();
  while($statement->fetch())
  {
    $device = array
    (
      "did" => $did, 
      "deviceFrid" => $device_frid, 
      "deviceLatestUid" => $device_latest_uid,
      "deviceLatestUidUpdateTimestamp" => $device_latest_uid_update_timestamp
    );
    array_push($devices, $device);
  }
  
  $parent = array
  (
      "devices" => $devices
  );

  return $parent;

  $statement->close();
}



/* FUNCTION:    dbGetSubscribedTopics
 * DESCRIPTION: Retrieves and returns Topic data of all of the topics subscribed to
 *              by the user with the device latest UID of the device with the
 *              specified FRID.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetSubscribedTopics($frid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT tid, uid, topic_name
            FROM T_SUBSCRIBED_TOPIC
              JOIN T_DEVICE ON uid = device_latest_uid
            WHERE device_frid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $frid);
  $statement->execute();

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($tid, $uid, $topic_name);

  // RETURN RESULT MESSAGE
  $topics = array();
  while($statement->fetch())
  {
    $topic = array
    (
        "tid" => $tid,
        "uid" => $uid,
        "topicName" => $topic_name
    );
    array_push($topics, $topic);
  }

  $parent = array
  (
      "topics" => $topics
  );

  return $parent;

  $statement->close();
}



/* FUNCTION:    dbIsUserSubscribedToTopic
 * DESCRIPTION: Returns true or false depending on whether or not the user with the
 *              specified UID is subscribed to the topic with the specified topic 
 *              name. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbIsUserSubscribedToTopic($uid, $topicName)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT 1 
            FROM T_SUBSCRIBED_TOPIC 
            WHERE uid = ? AND topic_name = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("is", $uid, $topicName);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->affected_rows === 0) {
    return false;
  }
  if ($statement->affected_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
      because something went wrong (" . __FUNCTION__ . ").");
  }
  if ($error != "") { return formatResponseError($error); }
  
  return true;
  
  $statement->close();
}



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbGetUserDeviceFrids
 * DESCRIPTION: Retrieves and returns all of the devices to which the user with
 *              the input UID is currently logged in.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbGetUserDeviceFrids($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT device_frid
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
  $statement->bind_result($device_frid);

  $deviceFrids = array();
  while($statement->fetch())
  {
    array_push($deviceFrids, $device_frid);
  }

  $statement->close();

  $parent = array
  (
      "deviceFrids" => $deviceFrids
  );

  return $parent;
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* This method may be replaced by the device group level version */

/* FUNCTION:    dbSubscribeDeviceToTopic
 * DESCRIPTION: Subscribes the device with the specified Firebase Registration 
 *              Identifier to the specified topic. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbSubscribeDeviceToTopic($frid, $topicName)
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
                WHERE device_frid = ?), 
              ?
            )";
  $statement = $conn->prepare($query);
  $statement->bind_param("ss", $frid, $topicName);
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
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbSubscribeDeviceGroupToTopic
 * DESCRIPTION: Subscribes the device group for the user with the input UID to the 
 *              specified topic.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbSubscribeDeviceGroupToTopic($uid, $topicName)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  
  $deviceGroupFrid = dbGetDeviceGroupFrid($uid);
  if (contains(json_encode($deviceGroupFrid), "responseType"))
    return json_encode($deviceGroupFrid);

  // EXECUTE THE QUERY
  $query = "INSERT INTO T_SUBSCRIBED_TOPIC2 (dgid, topic_name)
            VALUES (
               (SELECT dgid
                FROM T_DEVICE_GROUP
                WHERE device_group_frid = ?),
              ?
            )";
  $statement = $conn->prepare($query);
  $statement->bind_param("ss", $deviceGroupFrid, $topicName);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }

  // RETURN RESULT MESSAGE
  if ($statement->affected_rows === 1)
  {
    return json_encode(formatResponseSuccess("Device group has been successfully subscribed to topic."));
  }
  else if ($statement->affected_rows === 0)
  {
    return json_encode(formatResponseSuccess("Device group is already subscribed to topic."));
  }
  else
  {
    return formatResponseError("QUERY FLAWED: Please contact the database administrator with this method's name
      because something went wrong!");
  }

  $statement->close();
}
*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* This method may be replaced by the device group level version */

/* FUNCTION:    dbUnsubscribeDeviceFromTopic
 * DESCRIPTION: Unsubscribes the device with the specified Firebase Registration
 *              Identifier from the specified topic.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */ 
/*
function dbUnsubscribeDeviceFromTopic($frid, $topicName)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "DELETE FROM T_SUBSCRIBED_TOPIC 
            WHERE 
              did = 
               (SELECT did
                FROM T_DEVICE
                WHERE device_frid = ?) 
              AND topic_name = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("ss", $frid, $topicName);
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
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbUnsubscribeDeviceGroupFromTopic
 * DESCRIPTION: Unsubscribes the device group for the user with the specified UID 
 *              from the specified topic.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbUnsubscribeDeviceGroupFromTopic($uid, $topicName)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  
  $deviceGroupFrid = dbGetDeviceGroupFrid($uid);
  if (contains(json_encode($deviceGroupFrid), "responseType"))
    return json_encode($deviceGroupFrid);

  // EXECUTE THE QUERY
  $query = "DELETE FROM T_SUBSCRIBED_TOPIC2
            WHERE
              dgid =
               (SELECT dgid
                FROM T_DEVICE_GROUP
                WHERE device_group_frid = ?)
              AND topic_name = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("ss", $deviceGroupFrid, $topicName);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }

  // RETURN RESULT MESSAGE
  if ($statement->affected_rows === 1)
  {
    return json_encode(formatResponseSuccess("Device group has been successfully unsubscribed from topic."));
  }
  else if ($statement->affected_rows === 0)
  {
    return json_encode(formatResponseSuccess("Device group is already unsubscribed from topic."));
  }
  else
  {
    return formatResponseError("QUERY FLAWED: Please contact the database administrator with this method's name
      because something went wrong!");
  }

  $statement->close();
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    getDeviceSubscribedTopics
 * DESCRIPTION: Retrieves and returns all of the topics to which the device with 
 *              the input Firebase Registration Identifier is subscribed. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbGetDeviceSubscribedTopics($frid)
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
                WHERE device_frid = ?
            )";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $frid);
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
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbGetDeviceGroupFrid
 * DESCRIPTION: Retrieves and returns the Device Group Firebase Registration 
 *              Identifier (FRID) used by user's (whose UID is provided) devices. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbGetDeviceGroupFrid($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT DISTINCT device_group_frid
            FROM T_DEVICE 
              JOIN T_DEVICE_GROUP ON T_DEVICE.dgid = T_DEVICE_GROUP.dgid 
            WHERE device_latest_uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->num_rows === 0) {
    return formatResponseSuccess("Device Group Firebase Registration Identifier does not exist for this user.");
  }
  if ($statement->num_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
      because something went wrong.");
  }
  if ($error != "") { return formatResponseError($error); }

  // DEFAULT AND ASSIGN THE NECESSARY VARIABLES
  $statement->bind_result($device_group_frid);
  $statement->fetch();

  return $device_group_frid;

  $statement->close();
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbGetDevice
 * DESCRIPTION: Retrieves and returns the Device Group data, mainly the UID of the
 *              latest device user and the device group FRID, of the device with 
 *              the specified device FRID. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbGetDevice($deviceFrid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT device_latest_uid, T_DEVICE.dgid, device_group_frid
            FROM T_DEVICE
              LEFT JOIN T_DEVICE_GROUP ON T_DEVICE.dgid = T_DEVICE_GROUP.dgid
            WHERE device_frid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $deviceFrid);
  $statement->execute();

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }
  
  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($device_latest_uid, $dgid, $device_group_frid);
  
  // RETURN RESULT MESSAGE
  $statement->fetch();
  $device = array
  (
      "deviceLatestUid" => $device_latest_uid, 
      "dgid" => $dgid, 
      "deviceGroupFrid" => $device_group_frid
  );
  $parent = array
  (
      "device" => $device
  );
  
  return $parent;

  $statement->close();
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbAddDeviceToDeviceGroup
 * DESCRIPTION: Adds the device with the specified device firebase registration
 *              identifier (FRID) to the device group with the specified device
 *              group firebase registration identifier (FRID). 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbAddDeviceToDeviceGroup($deviceFrid, $deviceGroupFrid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  
  //$deviceGroupFrid = dbGetDeviceGroupFrid($uid);
  //if (contains(json_encode($deviceGroupFrid), "responseType"))
  //  return $deviceGroupFrid;

  // EXECUTE THE QUERY
  $query = "UPDATE T_DEVICE 
            SET dgid = 
             (SELECT dgid 
              FROM T_DEVICE_GROUP 
              WHERE device_group_frid = ?)
            WHERE device_frid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("ss", $deviceGroupFrid, $deviceFrid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->affected_rows === 0) {
    return formatResponseError("Device failed to be associated with the device group, possibly because the 
      device group with the specified device_group_frid does not exist.");
  }
  if ($statement->affected_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
      because something went wrong.");
  }
  if ($error != "") { return formatResponseError($error); }

  // DEFAULT AND ASSIGN THE NECESSARY VARIABLES

  return formatResponseSuccess("Device associated with the device group successfully.");

  $statement->close();
}*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbCreateDeviceGroup
 * DESCRIPTION: Creates a device group with the input device group firebase 
 *              registration identifier (FRID) in the database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbCreateDeviceGroup($deviceGroupFrid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "INSERT INTO T_DEVICE_GROUP (device_group_frid) 
            VALUES (?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $deviceGroupFrid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->affected_rows === 0) {
    return formatResponseError("Device group failed to be inserted, possibly because one with the specified 
      device_group_frid already exists.");
  }
  if ($statement->affected_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
    because something went wrong.");
  }
  if ($error != "") { return formatResponseError($error); }

  return formatResponseSuccess("Device group created successfully.");

  $statement->close();
}
*/



/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* !!!!! PLANNING TO DEPRECATE THIS !!!!! */
/* FUNCTION:    dbRemoveDeviceFromDeviceGroup
 * DESCRIPTION: Removes the device with the specified device firebase registration
 *              identifier (FRID) from the current device group.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
/*
function dbRemoveDeviceFromDeviceGroup($deviceFrid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  
  // FETCH THE DEVICE DATA FOR THE DEVICE FRID 
  $device = dbGetDevice($deviceFrid);
  if (contains(json_encode($device), "responseType"))
  {
    saveToErrorLog(json_encode($device), __FUNCTION__);
    return; 
  }

  // EXECUTE THE QUERY
  $query = "UPDATE T_DEVICE
            SET dgid = NULL
            WHERE device_frid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $deviceFrid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  
  echo "DEBUG: " . $deviceFrid;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->affected_rows === 0) {
    return formatResponseError("Device failed to be disassociated from the device group, possibly because the
    device was not associated to a device group to begin with. ");
  }
  if ($statement->affected_rows > 1) {
    return formatResponseError("DATA FLAWED: Please contact the database administrator with this method's name
    because something went wrong.");
  }
  if ($error != "") { return formatResponseError($error); }

  // TO-DO: Run another query that checks for the number of devices still assigned to that device group FRID
  //        If none are assigned to it, delete the device group FRID 
  
  // EXECUTE THE QUERY
  $query = "CALL sp_deleteEmptyDeviceGroup(?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $device["device"]["deviceGroupFrid"]);
  $statement->execute();

  return formatResponseSuccess("Device disassociated from the device group successfully.");

  $statement->close();
}
*/

?>