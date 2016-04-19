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
echo var_dump(fetchEventUserData($eid, $uid)) . "<br>";
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
require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/FriendshipStatus.php';
print_r(isFriend(1, 2));
echo "<br><br>!END OF SCRIPT!";

?>