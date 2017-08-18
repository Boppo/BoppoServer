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
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // DECODE INCOMING JSON CONTENTS //
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  
  $uid1                   = $json_decoded["uid1"];
  $uid2                   = $json_decoded["uid2"];
  $userRelationshipAction = $json_decoded["userRelationshipAction"];
  
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