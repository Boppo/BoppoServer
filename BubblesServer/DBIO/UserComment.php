<?php

/* FUNCTION:    dbSetObjectComment
 * DESCRIPTION: Adds the specified comment of the specified user to the specified 
 *              object type with the specified object id. If the comment is a reply
 *              to an existing comment, its parent UCID (parent user comment ID) 
 *              must also have been specified for this request. If this is an 
 *              existing comment, only its contents and timestamp will be updated 
 *              instead. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetObjectComment($uid, $object_type_code, $oid, $user_comment_set_timestamp, 
  $user_comment, $parent_ucid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_setObjectComment(?, ?, ?, ?, ?, ?)"; 
  $statement = $conn->prepare($query);
  $statement->bind_param("iiissi", $uid, $object_type_code, $oid, $user_comment_set_timestamp, 
    $user_comment, $parent_ucid);
  $statement->execute();
    
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { return "DB ERROR: " . $error; }

  return "User has successfully commented the object.";

  $statement->close();
}

/* FUNCTION:    dbGetObjectComments
 * DESCRIPTION: Retrieves the comments and all of their associated data for the 
 *              specified type of object with the specified identifier. This may, 
 *              for example, retrieve all of the comments that were added to a 
 *              particular event, user, or user image.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetObjectComments($object_type_code, $oid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT ucid, T_USER_COMMENT.uid, username, first_name, last_name, 
                   user_image_sequence, user_image_name, 
                   user_comment_set_timestamp, user_comment, parent_ucid 
            FROM        T_USER_COMMENT 
              JOIN      T_USER ON T_USER_COMMENT.uid = T_USER.uid 
              LEFT JOIN T_USER_IMAGE ON T_USER.uid = T_USER_IMAGE.uid
            WHERE object_type_code = ? AND oid = ? AND user_image_profile_sequence = 0";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $object_type_code, $oid);
  $statement->execute();
  $statement->store_result();   // Need this to check the number of rows later
  
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }
  
  // RETURN MESSAGE INSTEAD IF NO ROW EXISTS (I.E. USER WAS NEVER A MEMBER OF EVENT)
  if ($statement->num_rows === 0)  
    return "The specified object with the specified ID does not exist, or it has no comments."; 
  
  // ASSIGN THE OBJECT COMMENT VARIABLES
  $statement->bind_result($ucid, $uid, $username, $first_name, $last_name, 
      $user_image_sequence, $user_image_name, 
      $user_comment_set_timestamp, $user_comment, $parent_ucid);
  
  $commentArray = array();

  while($statement->fetch())
  {
    $user = array(
      "username" => $username, 
      "firstName" => $first_name, 
      "lastName" => $last_name
    ); 
    $image = array(
        "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name
    );
    $comment = array(
      "ucid" => $ucid, 
      "uid" => $uid, 
      "objectTypeCode" => $object_type_code, 
      "oid" => $oid, 
      "userCommentSetTimestamp" => $user_comment_set_timestamp, 
      "userComment" => $user_comment, 
      "parentUcid" => $parent_ucid, 
      "user" => $user, 
      "image" => $image
    );
    array_push($commentArray, $comment);
  }
  $commentData = array("comments" => $commentArray);

  $statement->close();

  return $commentData;
}

?>