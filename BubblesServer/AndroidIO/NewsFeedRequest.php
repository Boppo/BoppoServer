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
  $newsFriendCreatedEventsList = dbGetNewsFriendCreatedEvents($uid, $max);
  $newsFriendsJoinedMutualEventList = dbGetNewsFriendsJoinedMutualEvent($uid, $max);
  $newsFriendsThatBecameFriendsList = dbGetNewsFriendsThatBecameFriends($uid, $max);
  $newsFriendUploadedImagesList = dbGetNewsFriendUploadedImages($uid, $max);
  $newsFriendActiveEventList = dbGetNewsFriendActiveEvent($uid, $max);
  
  $newsEventList = array();
  
  
  
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
  
  
  
  // REORGANIZE DATA INTO THE CORRECT JSON FORMAT - PART 2
  for ($i = 0; $i < sizeof($newsFriendCreatedEventsList); $i++)
  {
    $newsEvent = $newsFriendCreatedEventsList[$i];
    $newsEvent["newsEventType"] = "FriendCreatedEvent";
    $newsEventList[$newsFriendCreatedEventsList[$i]["event"]["eventCreationTimestamp"]] = $newsEvent;
  }
  foreach ($newsFriendsJoinedMutualEventList as $k => $v)
  {
    $newsEvent = $newsFriendsJoinedMutualEventList[$k];
    $newsEvent["newsEventType"] = "FriendsJoinedMutualEvent";
    $newsEventList[$newsFriendsJoinedMutualEventList[$k]["event"]["eventUserInviteStatusActionTimestamp"]] = $newsEvent;
  }
  for ($i = 0; $i < sizeof($newsFriendsThatBecameFriendsList); $i++)
  {
    $newsEvent = $newsFriendsThatBecameFriendsList[$i];
    $newsEvent["newsEventType"] = "FriendsThatBecameFriends";
    $newsEventList[$newsFriendsThatBecameFriendsList[$i]["userRelationshipStartTimestamp"]] = $newsEvent;
  }
  for ($i = 0; $i < sizeof($newsFriendUploadedImagesList); $i++)
  {
    $newsEvent = $newsFriendUploadedImagesList[$i];
    $newsEvent["newsEventType"] = "FriendsUploadedImages";
    $newsEventList[$newsFriendUploadedImagesList[$i]["userImage"]["userImageUploadTimestamp"]] = $newsEvent;
  }
  for ($i = 0; $i < sizeof($newsFriendActiveEventList); $i++)
  {
    $newsEvent = $newsFriendActiveEventList[$i];
    $newsEvent["newsEventType"] = "FriendActiveEvent";
    $newsEventList[$newsFriendActiveEventList[$i]["event"]["eventStartDatetime"]] = $newsEvent;
  }
  
  // SORT THE NEWS EVENT ACTIONS BY DATE STARTING WITH MOST RECENT
  krsort($newsEventList);
  
  // IF THERE ARE LESS EVENTS THAN WERE REQUESTED, SET THE MAX TO THAT AMOUNT
  if ($max > sizeof($newsEventList))
    $max = sizeof($newsEventList);
  
  // RETURN THE MAXIMUM ALLOWED NUMBER OF NEWS ACTIONS IN JSON FORMAT
  echo json_encode(array_slice($newsEventList, 0, $max));
}

?>