<?php

/* FUNCTION:    dbGetNewsFriendCreatedEvents
 * DESCRIPTION: Gets the latest MAX events created by any of the friends of the 
 *              specified user for the news feed. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendCreatedEvents($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendCreatedEvents(?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($uid, $username, $eid, $event_host_uid, $event_name, $event_creation_timestamp);

  $friendCreatedEventsList = array();

  while($statement->fetch())
  {
    $eventHostUser = array
    (
        "uid" => $uid, 
        "username" => $username
    );
    $event = array
    (
        "eid" => $eid,
        "eventHostUid" => $event_host_uid,
        "eventName" => $event_name,
        "eventCreationTimestamp" => $event_creation_timestamp 
    );
    $friendCreatedEvent = array
    (
        "event" => $event, 
        "eventHostUser" => $eventHostUser
    );
    array_push($friendCreatedEventsList, $friendCreatedEvent);
  }

  $statement->close();

  return $friendCreatedEventsList;
}

/* FUNCTION:    dbGetNewsFriendsJoinedMutualEvent
 * DESCRIPTION: Gets the latest MAX friend-join-mutual-event actions of the 
 *              specified user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendsJoinedMutualEvent($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendsJoinedMutualEvent(?, ?);";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);

  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($uid, $username, 
      $eid, $event_host_uid, $event_name, $event_user_invite_status_action_timestamp);

  $mutualEventList = array();

  while($statement->fetch())
  {
    $event = array
    (
      "eid" => $eid,
      "eventHostUid" => $event_host_uid,
      "eventName" => $event_name,
      "eventUserInviteStatusActionTimestamp" => $event_user_invite_status_action_timestamp
    );
    $user = array
    (
      "uid" => $uid, 
      "username" => $username
    );
    $userList = array();
    array_push($userList, $user); 
    $mutualEvent = array
    (
      "event" => $event, 
      "userList" => $userList
    );

    array_push($mutualEventList, $mutualEvent);
  }

  $statement->close();

  return $mutualEventList;
}

/* FUNCTION:    dbGetNewsFriendsThatBecameFriends
 * DESCRIPTION: Gets the latest MAX friend-that-became-friends actions of the 
 *              specified user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendsThatBecameFriends($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendsThatBecameFriends(?, ?);";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);

  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($uid1, $username1, $uid2, $username2, $user_relationship_start_timestamp);

  $userRelationshipList = array();

  while($statement->fetch())
  {
    $user1 = array
    (
        "uid" => $uid1,
        "username" => $username1
    );
    $user2 = array
    (
        "uid" => $uid2, 
        "username" => $username2
    );
    $userRelationship = array
    (
        "userRelationshipStartTimestamp" => $user_relationship_start_timestamp, 
        "user1" => $user1, 
        "user2" => $user2
    );
    array_push($userRelationshipList, $userRelationship);
  }

  $statement->close();

  return $userRelationshipList;
}

/* FUNCTION:    dbGetNewsFriendUploadedImages
 * DESCRIPTION: Gets the latest MAX friend-upload-image actions of the specified 
 * \            user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendUploadedImages($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendUploadedImages(?, ?);";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);

  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($uiid, $uid, $user_image_sequence, $user_image_profile_sequence, 
      $user_image_name, $image_purpose_label, $privacy_label, 
      $user_image_gps_latitude, $user_image_gps_longitude, $user_image_upload_timestamp, 
      $user_image_view_count, $user_image_like_count, $user_image_dislike_count, 
      $user_image_comment_count);

  $uploadedImageList = array();

  while($statement->fetch())
  {
    $userImage = array
    (
      "uiid" => $uiid, 
      "uid" => $uid, 
      "userImageSequence" => $user_image_sequence, 
      "userImageProfileSequence" => $user_image_profile_sequence, 
      "userImageName" => $user_image_name, 
      "userImagePurposeLabel" => $image_purpose_label, 
      "userImagePrivacyLabel" => $privacy_label, 
      "userImageGpsLatitude" => $user_image_gps_latitude, 
      "userImageGpsLongitude" => $user_image_gps_longitude, 
      "userImageUploadTimestamp" => $user_image_upload_timestamp, 
      "userImageViewCount" => $user_image_view_count, 
      "userImageLikeCount" => $user_image_like_count, 
      "userImageDislikeCount" => $user_image_dislike_count, 
      "userImageCommentCount" => $user_image_comment_count
    );
    $uploadedImage = array
    (
      "userImage" => $userImage
    );
    array_push($uploadedImageList, $uploadedImage);
  }

  $statement->close();

  return $uploadedImageList;
}

/* FUNCTION:    dbGetNewsFriendActiveEvent
 * DESCRIPTION: Gets the latest MAX active events that were either hosted by or 
 *              joined by friends. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendActiveEvent($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendActiveEvent(?, ?);";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);

  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($eid, $event_host_uid, $event_name, $invite_type_label, 
      $privacy_label, $event_image_upload_allowed_indicator, 
      $event_creation_timestamp, $event_start_datetime, $event_end_datetime, 
      $event_gps_longitude, $event_gps_latitude, $event_view_count, 
      $event_like_count, $event_dislike_count, $event_comment_count, 
      $uid, $username);

  $friendActiveEventList = array();

  while($statement->fetch())
  {
    $event = array
    (
      "eid" => $eid, 
      "eventHostUid" => $event_host_uid, 
      "eventName" => $event_name, 
      "eventInviteTypeLabel" => $invite_type_label, 
      "eventPrivacyLabel" => $privacy_label, 
      "eventImageUploadAllowedIndicator" => $event_image_upload_allowed_indicator, 
      "eventCreationTimestamp" => $event_creation_timestamp, 
      "eventStartDatetime" => $event_start_datetime, 
      "eventEndDatetime" => $event_end_datetime, 
      "eventGpsLongitude" => $event_gps_longitude, 
      "eventGpsLatitude" => $event_gps_latitude, 
      "eventViewCount" => $event_view_count, 
      "eventLikeCount" => $event_like_count, 
      "eventDislikeCount" => $event_dislike_count, 
      "eventCommentCount" => $event_comment_count
    );
    $user = array
    (
      "uid" => $uid, 
      "username" => $username
    );
    $friendActiveEvent = array
    (
      "event" => $event, 
      "user" => $user
    );
    array_push($friendActiveEventList, $friendActiveEvent);
  }

  $statement->close();

  return $friendActiveEventList;
}

?>