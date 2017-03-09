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

  $statement->bind_result($username, $user_image_sequence, $user_image_name, 
      $eid, $event_host_uid, $event_name, $event_creation_timestamp);

  $friendCreatedEventList = array();

  while($statement->fetch())
  {
    $eventHostUserProfileImage = array
    (
        "uid" => $event_host_uid, 
        "userImageSequence" => $user_image_sequence, 
        "userImageName" => $user_image_name, 
        "userImagePath" => $event_host_uid . "/" . $user_image_sequence . "/" . $user_image_name
    );
    $eventHostUser = array
    (
        "uid" => $event_host_uid, 
        "username" => $username, 
        "userImage" => $eventHostUserProfileImage
    );
    $event = array
    (
        "eid" => $eid,
        "eventHostUid" => $event_host_uid,
        "eventName" => $event_name,
        "eventCreationTimestamp" => $event_creation_timestamp, 
        "eventHostUser" => $eventHostUser
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

  $statement->bind_result($uid, $username, 
      $user_image_sequence, $user_image_name, 
      $eid, $event_host_uid, $event_name, $event_user_invite_status_action_timestamp);

  $friendJoinedMutualEventList = array();

  while($statement->fetch())
  {
    $userProfileImage = array
    (
      "uid" => $uid,
      "userImageSequence" => $user_image_sequence,
      "userImageName" => $user_image_name,
      "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name
    );
    $user = array
    (
      "uid" => $uid,
      "username" => $username, 
      "userProfileImage" => $userProfileImage
    );
    $event = array
    (
      "eid" => $eid,
      "eventHostUid" => $event_host_uid,
      "eventName" => $event_name,
      "eventUserInviteStatusActionTimestamp" => $event_user_invite_status_action_timestamp, 
      "eventUser" => $user
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

  $statement->bind_result(
      $uid1, $u1_username, $u1pi_user_image_sequence, $u1pi_user_image_name, 
      $uid2, $u2_username, $u2pi_user_image_sequence, $u2pi_user_image_name, 
      $user_relationship_start_timestamp);

  $friendsThatBecameFriendsList = array();

  while($statement->fetch())
  {
    $user1ProfileImage = array
    (
        "uid" => $uid1, 
        "userImageSequence" => $u1pi_user_image_sequence, 
        "userImageName" => $u1pi_user_image_name, 
        "userImagePath" => $uid1 . "/" . $u1pi_user_image_sequence . "/" . $u1pi_user_image_name
    );
    $user1 = array
    (
        "uid" => $uid1,
        "username" => $u1_username, 
        "userProfileImage" => $user1ProfileImage
    );
    $user2ProfileImage = array
    (
        "uid" => $uid2,
        "userImageSequence" => $u2pi_user_image_sequence,
        "userImageName" => $u2pi_user_image_name, 
        "userImagePath" => $uid2 . "/" . $u2pi_user_image_sequence . "/" . $u2pi_user_image_name
    );
    $user2 = array
    (
        "uid" => $uid2, 
        "username" => $u2_username, 
        "userProfileImage" => $user2ProfileImage, 
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
      $uploadedUI_user_image_comment_count, 
      $profileUI_user_image_sequence, $profileUI_user_image_name);

  $friendUploadedImageList = array();

  while($statement->fetch())
  {
    $profileUserImage = array
    (
      "uid" => $uid,
      "userImageSequence" => $profileUI_user_image_sequence,
      "userImageName" => $profileUI_user_image_name,
      "userImagePath" => $uid . "/" . $profileUI_user_image_sequence . "/" . $profileUI_user_image_name
    );
    $user = array
    (
      "uid" => $uid, 
      "username" => $username, 
      "firstName" => $first_name, 
      "lastName" => $last_name, 
      "userImage" => $profileUserImage
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
      $uid, $username, 
      $user_image_sequence, $user_image_name);

  $friendActiveEventList = array();

  while($statement->fetch())
  {
    $userProfileImage = array
    (
        "uid" => $uid,
        "userImageSequence" => $user_image_sequence,
        "userImageName" => $user_image_name,
        "userImagePath" => $uid . "/" . $user_image_sequence . "/" . $user_image_name
    );
    $eventHost = array
    (
        "uid" => $uid,
        "username" => $username, 
        "userProfileImage" => $userProfileImage
    );
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
      "eventCommentCount" => $event_comment_count, 
      "eventHost" => $eventHost
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