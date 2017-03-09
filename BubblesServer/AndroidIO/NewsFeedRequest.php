<?php

$function = $_GET['function'];

if ($function == "getNewsEvents")
  getNewsEvents();


  
  
  
function getNewsEvents()
{
  /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  /* END. */
  
  // DECODE JSON STRING
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  // ASSIGN THE JSON VALUES TO VARIABLES
  $uid = $json_decoded["uid"];
  $max = $json_decoded["max"];
    
  // IMPORT REQUIRED FUNCTIONS
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/NewsFeed.php';
  
  // RETRIEVE THE DATA
  $newsFriendCreatedEventsList = dbGetNewsFriendCreatedEvent($uid, $max);
  $newsFriendJoinedMutualEventList = dbGetNewsFriendJoinedMutualEvent($uid, $max);
  $newsFriendsThatBecameFriendsList = dbGetNewsFriendsThatBecameFriends($uid, $max);
  $newsFriendUploadedImageList = dbGetNewsFriendUploadedImage($uid, $max);
  $newsFriendActiveEventList = dbGetNewsFriendActiveEvent($uid, $max);
  
  $newsEventList = array();
  
  /*
   // REORGANIZE DATA INTO THE CORRECT JSON FORMAT - PART 1
   $tempList = array();
   for ($i = 0; $i < sizeof($newsFriendsJoinedMutualEventList); $i++)
   {
   $eid = $newsFriendsJoinedMutualEventList[$i]["event"]["eid"];
   if (array_key_exists($eid, $tempList))
   {
   $userList = $tempList[$eid]["userList"];
   unset($tempList[$eid]["userList"]);
   array_push($userList, $newsFriendsJoinedMutualEventList[$i]["userList"][0]);
   $tempList[$eid]["userList"] = $userList;
   if ($newsFriendsJoinedMutualEventList[$i]["event"]["eventUserInviteStatusActionTimestamp"] >
   $tempList[$eid]["eventUserInviteStatusActionTimestamp"])
     $tempList[$eid]["eventUserInviteStatusActionTimestamp"] =
     $newsFriendsJoinedMutualEventList[$i]["event"]["eventUserInviteStatusActionTimestamp"];
     }
     else
     {
     $mutualEventList = array
     (
     "event" => $newsFriendsJoinedMutualEventList[$i]["event"],
     "userList" => $newsFriendsJoinedMutualEventList[$i]["userList"]
     );
     $tempList[$eid] = $mutualEventList;
     }
     $newsFriendsJoinedMutualEventList = $tempList;
     }
   */
  // REORGANIZE DATA INTO THE CORRECT JSON FORMAT - PART 1
  $tempList = array();
  $newsFriendsJoinedMutualEventList = $newsFriendJoinedMutualEventList;
  for ($i = 0; $i < sizeof($newsFriendsJoinedMutualEventList); $i++)
  {
    $eid = $newsFriendsJoinedMutualEventList[$i]["friendJoinedMutualEvent"]["eid"];
    if (array_key_exists($eid, $tempList))
    {
      $userList = $tempList[$eid]["userList"];
      unset($tempList[$eid]["userList"]);
      array_push($userList, $newsFriendsJoinedMutualEventList[$i]["userList"][0]);
      $tempList[$eid]["userList"] = $userList;
      if ($newsFriendsJoinedMutualEventList[$i]["friendJoinedMutualEvent"]["eventUserInviteStatusActionTimestamp"] >
          $tempList[$eid]["eventUserInviteStatusActionTimestamp"])
      $tempList[$eid]["eventUserInviteStatusActionTimestamp"] =
      $newsFriendsJoinedMutualEventList[$i]["friendJoinedMutualEvent"]["eventUserInviteStatusActionTimestamp"];
    }
    else
    {
      $userList = array();
      array_push($userList, $newsFriendsJoinedMutualEventList[$i]["friendJoinedMutualEvent"]["eventUser"]); 
      $friendsJoinedMutualEvent = $newsFriendsJoinedMutualEventList[$i]["friendJoinedMutualEvent"];
      unset($friendsJoinedMutualEvent["eventUser"]);
      $friendsJoinedMutualEventList = array
      (
          "friendsJoinedMutualEvent" => $friendsJoinedMutualEvent,
          "userList" => $userList
      );
      $tempList[$eid] = $friendsJoinedMutualEventList;
    }
    $newsFriendsJoinedMutualEventList = $tempList;
  }

  
  
  // REORGANIZE DATA INTO THE CORRECT JSON FORMAT - PART 2
  for ($i = 0; $i < sizeof($newsFriendCreatedEventsList); $i++)
  {
    $newsEvent = $newsFriendCreatedEventsList[$i];
    $newsEvent["newsEventType"] = "FriendCreatedEvent";
    $newsEventList[$newsFriendCreatedEventsList[$i]
        ["friendCreatedEvent"]["eventCreationTimestamp"]] = $newsEvent;
  }
  foreach ($newsFriendsJoinedMutualEventList as $k => $v)
  {
    $newsEvent = $newsFriendsJoinedMutualEventList[$k];
    $newsEvent["newsEventType"] = "FriendsJoinedMutualEvent";
    $newsEventList[$newsFriendsJoinedMutualEventList[$k]
        ["friendsJoinedMutualEvent"]["eventUserInviteStatusActionTimestamp"]] = $newsEvent;
  }
  for ($i = 0; $i < sizeof($newsFriendsThatBecameFriendsList); $i++)
  {
    $newsEvent = $newsFriendsThatBecameFriendsList[$i];
    $newsEvent["newsEventType"] = "FriendsThatBecameFriends";
    $newsEventList[$newsFriendsThatBecameFriendsList[$i]
        ["friendsThatBecameFriends"]["userRelationshipStartTimestamp"]] = $newsEvent;
  }
  for ($i = 0; $i < sizeof($newsFriendUploadedImageList); $i++)
  {
    $newsEvent = $newsFriendUploadedImageList[$i];
    $newsEvent["newsEventType"] = "FriendUploadedImage";
    $newsEventList[$newsFriendUploadedImageList[$i]["friendUploadedImage"]["userImageUploadTimestamp"]] = $newsEvent;
  }
  for ($i = 0; $i < sizeof($newsFriendActiveEventList); $i++)
  {
    $newsEvent = $newsFriendActiveEventList[$i];
    $newsEvent["newsEventType"] = "FriendActiveEvent";
    $newsEventList[$newsFriendActiveEventList[$i]["friendActiveEvent"]["eventStartDatetime"]] = $newsEvent;
  }
  
  // SORT THE NEWS EVENT ACTIONS BY DATE STARTING WITH MOST RECENT
  krsort($newsEventList);
  
  // CREATE AN ARRAY OUT OF THE LIST FOR EASIER ACCESS TO THE DATA
  $newsEventArray= array();
  foreach($newsEventList as $k => $v)
  {
    array_push($newsEventArray, $v);
  }
  
  // IF THERE ARE LESS EVENTS THAN WERE REQUESTED, SET THE MAX TO THAT AMOUNT
  if ($max > sizeof($newsEventList))
    $max = sizeof($newsEventList);
  
  // RETURN THE MAXIMUM ALLOWED NUMBER OF NEWS ACTIONS IN JSON FORMAT
  echo json_encode(array_slice($newsEventArray, 0, $max));
}

?>