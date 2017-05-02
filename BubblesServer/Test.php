<?php

/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
/* END. */

require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';

echo "!START OF SCRIPT!<br><br>";

/*
$true = "true";
$false = "false";
$val0 = "0";
$val1 = "1";
echo "The value of true is: " . strBoolToChar($true) . "<br>";
echo "The value of false is: " . strBoolToChar($false) . "<br>";
echo "The value of 0 is: " . charToStrBool($val0) . "<br>";
echo "The value of 1 is: " . charToStrBool($val1) . "<br>";
*/

/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/EventUser.php';

$eid = 18;
$uid = 3;
echo var_dump(dbGetEventUserData($eid, $uid)) . "<br>";
*/

/*
$file_gv = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Resources/GlobalVariables.json');
$json_file_gv = json_decode($file_gv, true);
$event_user_reinvite_user_type_code = $json_file_gv["Permission"]["EventUserReinviteUserTypeCode"];
echo $event_user_reinvite_user_type_code . "<br>";
*/

/*
$file_gv = file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Resources/GlobalVariables.json');
$json_file_gv = json_decode($file_gv, true);
$event_user_reinvite_wait_duration_unit
= $json_file_gv["Duration"]["EventUserReinviteWaitDuration"]["DatetimeUnit"];
$event_user_reinvite_wait_duration_value
= $json_file_gv["Duration"]["EventUserReinviteWaitDuration"]["DatetimeValue"];
echo $event_user_reinvite_wait_duration_unit . "<br>";
echo $event_user_reinvite_wait_duration_value . "<br>";
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/EventUserRequest.php';
addUserToEvent();
*/
/*
$date1 = date('Y-m-d H:i:s');
echo $date1;
$date2 = date_format(date_create_from_format('Y-m-d H:i:s', '2009-02-15 15:16:17'), 'Y-m-d H:i:s');
echo $date2;
*/
/*
$date1 = new DateTime('2016-02-15 15:16:17');
$date2 = new DateTime(date('Y-m-d H:i:s'));
$difference = date_diff($date1, $date2);
echo date_format($date1, 'Y-m-d H:i:s') . "<br>";
echo date_format($date2, 'Y-m-d H:i:s') . "<br>";
echo $difference->format('%a') . "<br>";
*/
//echo date_format($date, 'Y-m-d H:i:s') . "<br><br>";
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Event.php';
print_r(fetchEventDataByMember(2));
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
print_r(fetchImagesByPrivacyAndPurpose("Public", "Regular"));
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/FriendshipStatus.php';
print_r(fetchFriendshipStatusRequestSentUsers(1, "Request Sent"));
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/FriendshipStatus.php';
print_r(isFriend(1, 2));
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/UserLikeRequest.php';
setObjectLikeOrDislike();
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/EventRequest.php';
updateEvent();
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
$date_recorded = new DateTime("2016-05-19 04:15:53"); ;
$date_current  = new DateTime(date('Y-m-d H:i:s'));
$date_time_unit  = "Months";
$date_difference_value = compareDates($date_recorded, $date_current, $date_time_unit);
$date_difference_value_max = 1;
$result = compareDateDifferences($date_difference_value, $date_difference_value_max);
echo "RESULT: " . $result . "<br>";
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/EventRequest.php';
getLiveEventDataByRadius();
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/UserImageRequest.php';
uploadImageToEvents();
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/UserCommentRequest.php';
getObjectComments();
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/NewsFeed.php';
$result = dbGetNewsFriendCreatedEvents(2, 5);
echo(json_encode($result) . "<br><br>");


require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/NewsFeed.php';
$result = dbGetNewsFriendsJoinedMutualEvent(2, 5);
echo(json_encode($result) . "<br><br>");


require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/NewsFeed.php';
$result = dbGetNewsFriendsThatBecameFriends(2, 5);
echo(json_encode($result) . "<br><br>");


require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/NewsFeed.php';
$result = dbGetNewsFriendUploadedImages(2, 5);
echo(json_encode($result) . "<br><br>");


require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/NewsFeed.php';
$result = dbGetNewsFriendActiveEvent(2, 5);
echo(json_encode($result) . "<br><br>");
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/AndroidIO/NewsFeedRequest.php';
$result = getNewsEvents();
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/ReferenceData.php';
$result1 = dbGetEventCategoryCode("Sport");
$result2 = dbGetEventTypeCode("Sport", "Soccer");
echo "Event Category Code: " . $result1 . "<br>";
echo "Event Type Label:" . $result2 . "<br>";
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
echo json_encode(dbGetImagesFirstNProfileByUid(1));
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/UserImage.php';
echo json_encode(dbGetImagesFirstNEventProfileByEid(59));
*/
/*
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Older/Functions/Image.php';
deleteImage();
echo "<br><br>!END OF SCRIPT!";
*/
// TEST FINAL!
?>