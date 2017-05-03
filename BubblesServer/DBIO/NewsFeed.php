<?php

/* FUNCTION:    dbGetNewsFriendCreatedEvents
 * DESCRIPTION: Gets the latest MAX events created by any of the friends of the 
 *              specified user for the news feed. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendCreatedEvent($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendCreatedEvent(?, ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($username, $eid, $event_host_uid, $event_name, $event_insert_timestamp);

  $friendCreatedEventList = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($event_host_uid);
    $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
    
    $eventHostUser = array
    (
        "uid" => $event_host_uid, 
        "username" => $username, 
        "userProfileImages" => $user_profile_images
    );
    $event = array
    (
        "eid" => $eid,
        "eventHostUid" => $event_host_uid,
        "eventName" => $event_name,
        "eventInsertTimestamp" => $event_insert_timestamp, 
        "eventHostUser" => $eventHostUser, 
        "eventProfileImages" => $event_profile_images
    );
    $friendCreatedEvent = array
    (
        "friendCreatedEvent" => $event 
    );
    array_push($friendCreatedEventList, $friendCreatedEvent);
  }

  $statement->close();

  return $friendCreatedEventList;
}

/* FUNCTION:    dbGetNewsFriendsJoinedMutualEvent
 * DESCRIPTION: Gets the latest MAX friend-join-mutual-event actions of the 
 *              specified user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendJoinedMutualEvent($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendJoinedMutualEvent(?, ?);";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);

  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result($uid, $username, $eid, $event_host_uid, $event_name, 
      $event_user_invite_status_upsert_timestamp);

  $friendJoinedMutualEventList = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
    $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
    
    $user = array
    (
      "uid" => $uid,
      "username" => $username, 
      "userProfileImages" => $user_profile_images
    );
    $event = array
    (
      "eid" => $eid,
      "eventHostUid" => $event_host_uid,
      "eventName" => $event_name,
      "eventUserInviteStatusUpsertTimestamp" => $event_user_invite_status_upsert_timestamp, 
      "eventUser" => $user, 
      "eventProfileImages" => $event_profile_images
    );
    $friendJoinedMutualEvent = array
    (
      "friendJoinedMutualEvent" => $event
    );

    array_push($friendJoinedMutualEventList, $friendJoinedMutualEvent);
  }

  $statement->close();

  return $friendJoinedMutualEventList;
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
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

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

  $statement->bind_result($uid1, $u1_username, $uid2, $u2_username, $user_relationship_start_timestamp);

  $friendsThatBecameFriendsList = array();

  while($statement->fetch())
  {
    $user_1_profile_images = dbGetImagesFirstNProfileByUid($uid1);
    $user_2_profile_images = dbGetImagesFirstNProfileByUid($uid2);
    
    $user1 = array
    (
        "uid" => $uid1,
        "username" => $u1_username, 
        "user1ProfileImages" => $user_1_profile_images
    );
    $user2 = array
    (
        "uid" => $uid2, 
        "username" => $u2_username, 
        "user2ProfileImages" => $user_2_profile_images, 
    );
    $userRelationship = array
    (
        "user1" => $user1, 
        "user2" => $user2, 
        "userRelationshipStartTimestamp" => $user_relationship_start_timestamp
    );
    $friendsThatBecameFriends = array
    (
        "friendsThatBecameFriends" => $userRelationship
    ); 
    array_push($friendsThatBecameFriendsList, $friendsThatBecameFriends);
  }

  $statement->close();

  return $friendsThatBecameFriendsList;
}

/* FUNCTION:    dbGetNewsFriendUploadedImages
 * DESCRIPTION: Gets the latest MAX friend-upload-image actions of the specified 
 * \            user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetNewsFriendUploadedImage($uid, $max)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // EXECUTE THE QUERY
  $query = "CALL sp_get_newsFriendUploadedImage(?, ?);";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $max);

  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  $statement->bind_result(
      $uid, $username, $first_name, $last_name, 
      $uploadedUI_uiid, $uploadedUI_user_image_sequence, $uploadedUI_user_image_profile_sequence, 
      $uploadedUI_user_image_name, $uploadedUI_image_purpose_label, $uploadedUI_privacy_label, 
      $uploadedUI_user_image_gps_latitude, $uploadedUI_user_image_gps_longitude, 
      $uploadedUI_user_image_upload_timestamp, $uploadedUI_user_image_view_count, 
      $uploadedUI_user_image_like_count, $uploadedUI_user_image_dislike_count, 
      $uploadedUI_user_image_comment_count);

  $friendUploadedImageList = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
    
    $user = array
    (
      "uid" => $uid, 
      "username" => $username, 
      "firstName" => $first_name, 
      "lastName" => $last_name, 
      "userProfileImages" => $user_profile_images
    );
    $uploadedUserImage = array
    (
      "uiid" => $uploadedUI_uiid, 
      "uid" => $uid, 
      "userImageSequence" => $uploadedUI_user_image_sequence, 
      "userImageProfileSequence" => $uploadedUI_user_image_profile_sequence, 
      "userImageName" => $uploadedUI_user_image_name, 
      "userImagePath" => $uid . "/" . $uploadedUI_user_image_sequence . "/" . $uploadedUI_user_image_name,
      "userImagePurposeLabel" => $uploadedUI_image_purpose_label, 
      "userImagePrivacyLabel" => $uploadedUI_privacy_label, 
      "userImageGpsLatitude" => $uploadedUI_user_image_gps_latitude, 
      "userImageGpsLongitude" => $uploadedUI_user_image_gps_longitude, 
      "userImageUploadTimestamp" => $uploadedUI_user_image_upload_timestamp, 
      "userImageViewCount" => $uploadedUI_user_image_view_count, 
      "userImageLikeCount" => $uploadedUI_user_image_like_count, 
      "userImageDislikeCount" => $uploadedUI_user_image_dislike_count, 
      "userImageCommentCount" => $uploadedUI_user_image_comment_count, 
      "user" => $user
    );
    $friendUploadedImage = array
    (
      "friendUploadedImage" => $uploadedUserImage
    );
    array_push($friendUploadedImageList, $friendUploadedImage);
  }

  $statement->close();

  return $friendUploadedImageList;
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
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';

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
      $event_insert_timestamp, $event_start_datetime, $event_end_datetime, 
      $event_gps_longitude, $event_gps_latitude, $event_view_count, 
      $event_like_count, $event_dislike_count, $event_comment_count, 
      $uid, $username);

  $friendActiveEventList = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
    $event_profile_images = dbGetImagesFirstNEventProfileByEid($eid);
    
    $eventHost = array
    (
      "uid" => $uid,
      "username" => $username, 
      "userProfileImages" => $user_profile_images
    );
    $event = array
    (
      "eid" => $eid, 
      "eventHostUid" => $event_host_uid, 
      "eventName" => $event_name, 
      "eventInviteTypeLabel" => $invite_type_label, 
      "eventPrivacyLabel" => $privacy_label, 
      "eventImageUploadAllowedIndicator" => $event_image_upload_allowed_indicator, 
      "eventInsertTimestamp" => $event_insert_timestamp, 
      "eventStartDatetime" => $event_start_datetime, 
      "eventEndDatetime" => $event_end_datetime, 
      "eventGpsLongitude" => $event_gps_longitude, 
      "eventGpsLatitude" => $event_gps_latitude, 
      "eventViewCount" => $event_view_count, 
      "eventLikeCount" => $event_like_count, 
      "eventDislikeCount" => $event_dislike_count, 
      "eventCommentCount" => $event_comment_count, 
      "eventHost" => $eventHost, 
      "eventProfileImages" => $event_profile_images
    );
    $friendActiveEvent = array
    (
      "friendActiveEvent" => $event
    );
    array_push($friendActiveEventList, $friendActiveEvent);
  }

  $statement->close();

  return $friendActiveEventList;
}

?>