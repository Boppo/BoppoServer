<?php

/* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
/* END. */

echo "!START OF SCRIPT!<br><br>";

/*
include 'DBIO/Privacy.php';
$privacy_label = "Private";
$privacy_code  = getPrivacyCode($privacy_label);
echo "The value returned for the privacy code from the database is: '" . $privacy_code . "'<br><br>";
*/

/*
include 'DBIO/InviteType.php';
$invite_type_label = "Everyone";
$invite_type_code  = getInviteTypeCode($invite_type_label);
echo "The value returned for the invite type code from the database is: '" . $invite_type_code . "'<br><br>";
*/

include 'Functions/Miscellaneous.php';

include 'DBIO/Friendship.php';
$uid_1 = 3;
$uid_2 = 4;
$friendship_status_type_label = getFriendshipStatus($uid_1, $uid_2);
echo "RETURNED FRIENDSHIP STATUS TYPE LABEL: " . $friendship_status_type_label . "<br><br>";

echo "!END OF SCRIPT!<br><br>";

?>