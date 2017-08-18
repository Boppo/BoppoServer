<?php

/* FUNCTION: fetchUserEncoded
 * DESCRIPTION: Gets the image and its data by specified User Identifier (uid).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchUserEncoded($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT  uid, first_name, last_name, email, phone, user_privacy_code
            FROM    T_USER
            WHERE   uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE IMAGE VARIABLES
  $statement->bind_result($uid, $first_name, $last_name, $email, $phone, $user_privacy_code);
  $statement->fetch();

  $user = array
  (
      "uid" => $uid, 
      "firstName" => $first_name, 
      "lastName" => $last_name, 
      "email" => $email, 
      "phone" => $phone, 
      "userPrivacyCode" => $user_privacy_code
  );

  $statement->close();

  return $user;
}



/* FUNCTION: getUserFriendRequestUsers
 * DESCRIPTION: Retrieves and returns all of the users that sent friend requests
 * 				to the specified (logged in) user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function getUserFriendRequestUsers()
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

	$query = "SELECT user_relationship_type_code
			  FROM T_USER_RELATIONSHIP_TYPE
			  WHERE user_relationship_type_label = 'Friendship Pending'";
	$statement = $conn->prepare($query);
	// 3.2 - EXECUTE THE QUERY
	$statement->execute();
	// 3.3 - CHECK FOR ERROR AND STOP IF EXISTS
	$error = $statement->error;
	if ($error != "") {
		echo "MYSQL ERROR: " . $error;
		return; }
		// 3.4 - STORE THE QUERY RESULT IN A VARIABLE
		$statement->bind_result($user_relationship_type_code);
		$statement->fetch();
		$statement->close(); 	// Need to close statements if variable is to be recycled
		// 3.5 - CHECK IF VALUE EXISTS AND STOP IF IT DOESN'T
		if ($user_relationship_type_code == -1) {
			echo "FRIENDSHIP STATUS TYPE LABEL IS NOT VALID.";
			return;
		}

		// 4 - PREPARE THE QUERY
		$query = "SELECT uid_1
				  FROM R_USER_RELATIONSHIP
				  WHERE uid_2 = ?
					AND user_relationship_type_code = ?";
		$statement = $conn->prepare($query);
		$statement->bind_param("ii", $uid, $user_relationship_type_code);

		// 5 - EXECUTE THE QUERY
		$statement->execute();

		// 6 - RETURN RESULTING ERROR IF THERE IS ONE, OTHERWISE A LIST OF UIDs, THEN CLOSE STATEMENT
		$error = $statement->error;
		if ($error != "") {
			echo "MYSQL ERROR: " . $error;
			return; }
			else {

				// 7 - STORE THE RESULTING VARIABLES IN AN INDEX ARRAY
				$statement->bind_result($uid_2);
				$data = array();
				while ($statement->fetch())
					array_push($data, $uid_2);

					// 8 - RETURN JSON-ENCODED ARRAY AND CLOSE STATEMENT
					echo json_encode($data);
			}

			$statement->close(); 	// Need to close statements if variable is to be recycled
}



/* FUNCTION:    dbSetUser
 * DESCRIPTION: Updates the user's properties in the database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbSetUser($user, $set_or_not)
{
    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */
  
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	
	// FETCH THE CURRENT VALUES FOR THIS EVENT
	$userCurrent = fetchUserEncoded($user["uid"]);
	
    // UPDATE THE CURRENT VALUES WITH VALID NEW VALUES
    if ($set_or_not["firstName"] === true)
      $userCurrent["firstName"] = $user["firstName"];
    if ($set_or_not["lastName"] === true)
      $userCurrent["lastName"] = $user["lastName"];
    if ($set_or_not["email"] === true)
      $userCurrent["email"] = $user["email"];
    if ($set_or_not["phone"] === true)
      $userCurrent["phone"] = $user["phone"];
    if ($set_or_not["userPrivacyCode"] === true)
      $userCurrent["userPrivacyCode"] = $user["userPrivacyCode"];
	
	// EXECUTE THE QUERY
	$query = "UPDATE T_USER
			  SET    first_name = ?, 
	                 last_name = ?, 
	                 email = ?, 
	                 phone = ?, 
	                 user_privacy_code = ?
	          WHERE  uid = ?";
		
	$statement = $conn->prepare($query);
		
	$statement->bind_param("ssssii", $userCurrent["firstName"], $userCurrent["lastName"], 
	    $userCurrent["email"], $userCurrent["phone"], $userCurrent["userPrivacyCode"], 
	    $userCurrent["uid"]);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { return "DB ERROR: " . $error; }
	
	// RETURN A SUCCESS CONFIRMATION MESSAGE
	if ($statement->affected_rows === 0)
	  return "User has failed to update: no user has been updated, possibly because the input data is not new.";
    else if ($statement->affected_rows === 1)
      return "User has been successfully updated.";
    else
      return "User has failed to update: no user or multiple users have been updated.";
	
	$statement->close();
}

/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    dbGetUserProfileData
 * DESCRIPTION: Gets the profile data for the user with the specified uid. In
 *              other words, gets anything about the user that includes the
 *              username, first name, last name, profile image and its thumbnail,
 *              count of friends, a few friends, a few events, etc. This retrieves
 *              more data than getUserData.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetUserProfileData($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/FriendshipStatus.php'; 
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Event.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/EventUser.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT uid, username, first_name, last_name 
            FROM   T_USER 
            WHERE  uid = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $uid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return json_encode(formatResponseError($error)); }

  // DEFAULT AND ASSIGN VARIABLES WHERE APPROPRIATE 
  $path_gv = $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Resources/GlobalVariables.json';
  $file_gv = file_get_contents($path_gv);
  $array_gv = json_decode($file_gv, true);
  $user_profile_event_max_amount = $array_gv["MaxAmount"]["UserProfileEventMaxAmount"];
  $user_profile_friend_max_amount = $array_gv["MaxAmount"]["UserProfileFriendMaxAmount"];
  
  $statement->bind_result($uid, $username, $first_name, $last_name);
  $statement->fetch();

  $user = array();

  $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
  $count_friends = dbGetCountFriends($uid); 
  $count_hosted_events = dbGetCountHostedEvents($uid);
  $count_joined_events = dbGetCountJoinedEvents($uid);
  $count_images = dbGetCountImages($uid);
  $top_n_random_events = dbGetEventDataByTopNRandom($uid, $user_profile_event_max_amount);
  $top_n_random_friends = dbGetFriendsByTopNRandom($uid, $user_profile_friend_max_amount); 
  $latest_n_images = dbGetImagesLatestNByUid($uid);
  
  $user = array
  (
      "uid" => $uid,
      "username" => $username, 
      "firstName" => $first_name, 
      "lastName" => $last_name, 
      "userProfileImages" => $user_profile_images, 
      "countFriends" => $count_friends, 
      "countHostedEvents" => $count_hosted_events, 
      "countJoinedEvents" => $count_joined_events, 
      "countImages" => $count_images, 
      "topNRandomEvents" => $top_n_random_events, 
      "topNRandomFriends" => $top_n_random_friends, 
      "latestNImages" => $latest_n_images
  );
  
  $parent = array
  (
    "user" => $user  
  );

  $statement->close();

  return $parent;
}



/* FUNCTION:    dbGetUsersSearchedByName
 * DESCRIPTION: Gets the users and their related data whose first names, last 
 *              names, and/or usernames match the input substring, and are 
 *              visible to the searched-by user. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetUsersSearchedByName($searched_by_uid, $searched_name)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php'; 
  
  // PREPARE VARIABLE SUBQUERY TO FILTER BY THE SEARCHED NAME
  $names = explode(" ", $searched_name); 
  for ($i = 0; $i < count($names); $i++)
  {
    $names[$i] = "%" . $names[$i] . "%";
  }
  if (count($names) == 1)
  {
    $subquery = "username LIKE ? OR first_name LIKE ? OR last_name LIKE ?";
  }
  else if (count($names) > 1)
  {
    $subquery = "(first_name LIKE ? AND last_name LIKE ?) OR (last_name LIKE ? AND first_name LIKE ?)"; 
  }
  else { return "A search string has not been specified."; }

  // EXECUTE THE QUERY
  //                    user_image_sequence, user_image_name 
  $query = "SELECT T_USER.uid, facebook_uid, googlep_uid, username, first_name, last_name, email, phone, 
                   privacy_label, user_comment_count, user_insert_timestamp
            FROM   T_USER 
                   INNER JOIN T_PRIVACY ON user_privacy_code = privacy_code 
            WHERE  (" . $subquery . ")
                   AND 
                   ( (
                       privacy_label = 'Public' 
                       AND T_USER.uid <> ?
                     )
                     OR
                     (
                       T_USER.uid IN
                       (
                         SELECT F.uid_1 AS uid
                         FROM 
                         T_USER 
                           INNER JOIN R_USER_RELATIONSHIP F ON uid = F.uid_1 
                         WHERE F.uid_2 = ? 
                           AND F.user_relationship_type_code = 2
                    
                         UNION
                    
                         SELECT F.uid_2 AS uid
                         FROM 
                         T_USER 
                           INNER JOIN R_USER_RELATIONSHIP F ON uid = F.uid_2 
                         WHERE F.uid_1 = ? 
                           AND F.user_relationship_type_code = 2
                  ) ) )";
  $statement = $conn->prepare($query);
  if (count($names) == 1)
  {
    $statement->bind_param("sssiii", $names[0], $names[0], $names[0], 
      $searched_by_uid, $searched_by_uid, $searched_by_uid);
  }
  else if (count($names) > 1)
  {
    $statement->bind_param("ssssiii", $names[0], $names[1], $names[0], $names[1], 
      $searched_by_uid, $searched_by_uid, $searched_by_uid);
  }
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE EVENT VARIABLES
  $statement->bind_result($uid, $facebook_uid, $googlep_uid, $username, $first_name, $last_name, 
      $email, $phone, $privacy_label, $user_comment_count, $user_insert_timestamp);
//      $user_image_sequence, $user_image_name); 

  $users = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
    $user = array
    (
        "uid" => $uid,
        "facebookUid" => $facebook_uid, 
        "googlepUid" => $googlep_uid, 
        "username" => $username, 
        "firstName" => $first_name, 
        "lastName" => $last_name, 
        "email" => $email, 
        "phone" => $phone, 
        "privacyLabel" => $privacy_label, 
        "userCommentCount" => $user_comment_count, 
        "userInsertTimestamp" => $user_insert_timestamp, 
        "userProfileImages" => $user_profile_images
    );
    array_push($users, $user);
  }
  $statement->close();
  
  $userList = array
  (
      "users" => $users
  );

  return $userList;
}



/* FUNCTION:    dbGetFriends
 * DESCRIPTION: Returns all of the friends and their data of the specified user.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetFriends($uid)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT T_USER.uid, facebook_uid, googlep_uid, username, first_name, last_name, email, phone,
                   privacy_label, user_comment_count, user_insert_timestamp
            FROM   T_USER
                   INNER JOIN T_PRIVACY ON user_privacy_code = privacy_code
            WHERE  T_USER.uid IN
                   (
                     SELECT F.uid_1 AS uid
                     FROM
                     T_USER
                       INNER JOIN R_USER_RELATIONSHIP F ON uid = F.uid_1
                     WHERE F.uid_2 = ?
                       AND F.user_relationship_type_code = 2

                     UNION

                     SELECT F.uid_2 AS uid
                     FROM
                     T_USER
                       INNER JOIN R_USER_RELATIONSHIP F ON uid = F.uid_2
                     WHERE F.uid_1 = ?
                       AND F.user_relationship_type_code = 2
                   )";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $uid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE EVENT VARIABLES
  $statement->bind_result($uid, $facebook_uid, $googlep_uid, $username, $first_name, $last_name,
      $email, $phone, $privacy_label, $user_comment_count, $user_insert_timestamp);

  $friends = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
    $user = array
    (
        "uid" => $uid,
        "facebookUid" => $facebook_uid,
        "googlepUid" => $googlep_uid,
        "username" => $username,
        "firstName" => $first_name,
        "lastName" => $last_name,
        "email" => $email,
        "phone" => $phone,
        "privacyLabel" => $privacy_label,
        "userCommentCount" => $user_comment_count,
        "userInsertTimestamp" => $user_insert_timestamp,
        "userProfileImages" => $user_profile_images
    );
    array_push($friends, $user);
  }
  $statement->close();

  $friendList = array
  (
      "friends" => $friends
  );

  return $friendList;
}

?>