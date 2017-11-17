<?php

$function = $_GET['function'];

if ($function == "setUserRelationship")
  setUserRelationship();

  
  
  
  
/* FUNCTION:    setUserRelationship
 * DESCRIPTION: Updates the user's properties in the database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function setUserRelationship()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //
  
  // IMPORT THE DATABASE CONNECTION FUNCTION AND OTHER REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Firebase.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ObjectType.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/User.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Topic.php';
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/FirebaseIO/Message.php'; 
  
  // GET REQUIRED METADATA 
  // Fetch the object type code for a "user" objectTypeLabel type
  $objectTypeCode = dbGetObjectTypeCode("User");
  if (contains(json_encode($objectTypeCode), "responseType"))
    return json_encode($objectTypeCode);

  // DECODE INCOMING JSON CONTENTS //
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  
  $uid1                   = $json_decoded["uid1"];
  $uid2                   = $json_decoded["uid2"];
  $userRelationshipAction = $json_decoded["userRelationshipAction"]; 
  
  // GET ADDITIONAL USER DATA
  $user1ProfileData = dbGetUserProfileData($uid1)["user"];
  $user2ProfileData = dbGetUserProfileData($uid2)["user"];
  
  // DETERMINE WHAT TO DO FROM THE USER RELATIONSHIP ACTION 
  $query = "";
  if ($userRelationshipAction == "Add")
    $query = "CALL sp_setUserRelationship_sendFriendRequest(?, ?)";
  else if ($userRelationshipAction == "Cancel")
    $query = "CALL sp_setUserRelationship_cancelFriendRequest(?, ?)";
  else if ($userRelationshipAction == "Accept")
    $query = "CALL sp_setUserRelationship_acceptFriendRequest(?, ?)";
  else if ($userRelationshipAction == "Reject")
    $query = "CALL sp_setUserRelationship_rejectFriendRequest(?, ?)";
  else if ($userRelationshipAction == "Unfriend")
    $query = "CALL sp_setUserRelationship_unfriendUser(?, ?)"; 
  else if ($userRelationshipAction == "Block")
    $query = "CALL sp_setUserRelationship_blockUser(?, ?)";
  else if ($userRelationshipAction == "Unblock")
    $query = "CALL sp_setUserRelationship_unblockUser(?, ?)";
  else 
  {
    echo formatResponseError("Invalid userRelationshipAction provided."); 
    return;
  }
  
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid1, $uid2);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $error = $statement->error;
  
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo json_encode(formatResponseError($error)); return; }
  
  if ($statement->num_rows === 1)
  {
    $statement->bind_result($result);
    $statement->fetch();
    echo json_encode(formatResponseError(removeString($result, "FAIL: ")));
  }
  else if ($statement->affected_rows === 1)
  {
    echo json_encode(formatResponseSuccess("User relationship has been set successfully.")); 
    if ($userRelationshipAction == "Accept")
    {
      // Let User with UID2 know that User with UID1 has accepted the friend request
      sendMessageToUser(
        "User", $uid2, "User Befriended", 
        $user1ProfileData["username"] . " has befriended you.", 
        null, null);
      // Subscribe the two users to each other
      subscribeDevicesToTopic($uid1, $objectTypeCode . "." . $uid2); 
      subscribeDevicesToTopic($uid2, $objectTypeCode . "." . $uid1);
    }
    else if ($userRelationshipAction == "Unfriend")
    {
      // Let User with UID2 know that User with UID1 has unfriended them
      sendMessageToUser(
          "User", $uid2, "User Unfriended",
          $user1ProfileData["username"] . " has unfriended you.",
          null, null);
      // Unsubscribe the two users from each other
      unsubscribeDevicesFromTopic($uid1, $objectTypeCode . "." . $uid2);
      unsubscribeDevicesFromTopic($uid2, $objectTypeCode . "." . $uid1);
    }
    else if ($userRelationshipAction == "Add")
    {
      // Let User with UID2 know that User with UID1 has sent them a friend request
      sendMessageToUser(
          "User", $uid2, "User Sent Friend Rquest",
          $user1ProfileData["username"] . " has sent you a friend request.",
          null, null);
    }
    else if ($userRelationshipAction == "Reject")
    {
      // Let User with UID2 know that User with UID1 has rejected their friend request
      sendMessageToUser(
          "User", $uid2, "User Rejected Friend Rquest",
          $user1ProfileData["username"] . " has rejected your friend request.",
          null, null);
    }
  }
  else 
  {
    echo json_encode(formatResponseError("User relationship has failed to set for an unknown reason. 
      Please report this to the PHP/Database administrator."));
  }
  
  $statement->close();
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?> 