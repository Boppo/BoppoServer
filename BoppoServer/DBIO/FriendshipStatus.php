<?php

/* FUNCTION:    getFriendshipStatus
 * NOTE:        User 1 is THIS user, User 2 is the OTHER user.
 * DESCRIPTION: Retrieves the label that represents the current status between
 *              User 1 and User 2.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetFriendshipStatus($uid_1, $uid_2)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

	// EXECUTE THE QUERY
	$query = "SELECT uid_1, uid_2, user_relationship_type_label
			  FROM R_USER_RELATIONSHIP, T_USER_RELATIONSHIP_TYPE 
			  WHERE ((uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? AND uid_1 = ?)) AND 
  				R_USER_RELATIONSHIP.user_relationship_type_code = 
				T_USER_RELATIONSHIP_TYPE.user_relationship_type_code";
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid_1, $uid_2, $uid_1, $uid_2);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// DEFAULT AND ASSIGN THE FRIENDSHIP STATUS TYPE LABEL
	$returned_uid_1 = -1;
	$returned_uid_2 = -1;
	$user_relationship_type_label = "";
	$statement->bind_result($returned_uid_1, $returned_uid_2, $user_relationship_type_label);
	$statement->fetch();
	$statement->close();
	
	// IF FRIENDSHIP STATUS TYPE LABEL IS FRIENDSHIP PENDING OR BLOCK, IDENTIFY WHO PERFORMED THE ACTION
	if ($uid_1 === $returned_uid_1 && $uid_2 === $returned_uid_2)
	{
		if ($user_relationship_type_label === "Friendship Pending")
			$user_relationship_type_label = "Friendship Pending request sent by this user.";
		if ($user_relationship_type_label === "Blocked")
			$user_relationship_type_label = "This user already blocked the other user.";
	}
	if ($uid_1 === $returned_uid_2 && $uid_2 === $returned_uid_1)
	{
		if ($user_relationship_type_label === "Friendship Pending")
			$user_relationship_type_label = "Friendship Pending request sent to this user.";
		if ($user_relationship_type_label === "Blocked")
			$user_relationship_type_label = "This user is already blocked by the other user.";
	}
	
	// IF THE TWO USERS DO NOT HAVE A RELATIONSHIP CURRENTLY
	if (strlen($user_relationship_type_label) === 0)
		$user_relationship_type_label = "Not friends.";
	
	// RETURN THE FRIENDSHIP STATUS TYPE LABEL
	return $user_relationship_type_label;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    fetchFriendshipStatusRequestSentUsers
 * NOTE:        User 1 is the user that sent the friendship status request.
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has sent the specified type of request to. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchFriendshipStatusRequestSentUsers($uid_1, $user_relationship_type_label)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

	$query = "SELECT uid, username, first_name, last_name 
			  FROM R_USER_RELATIONSHIP 
			    LEFT JOIN T_USER_RELATIONSHIP_TYPE ON 
			      T_USER_RELATIONSHIP_TYPE .user_relationship_type_code = R_USER_RELATIONSHIP.user_relationship_type_code 
			    LEFT JOIN T_USER ON R_USER_RELATIONSHIP.uid_2 = T_USER.uid
			  WHERE uid_1 = ? AND user_relationship_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $uid_1, $user_relationship_type_label);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// ASSIGN THE USER VARIABLES
	$statement->bind_result($uid, $username, $first_name, $last_name);

	$users = array();
	
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
      array_push($users, $user);
	}
	
	$statement->close();
	
	// RETURN THE LIST OF USERS
	return $users;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    fetchFriendshipStatusRequestReceivedUsers
 * NOTE:        User 2 is the user that received the friendship status request).
 * DESCRIPTION: This method echos a list of users (lists) that the specified user
 *              has received the specified type of request from.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function fetchFriendshipStatusRequestReceivedUsers($uid_2, $user_relationship_type_label)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';

	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

	$query = "SELECT uid, username, first_name, last_name
			  FROM R_USER_RELATIONSHIP
			    LEFT JOIN T_USER_RELATIONSHIP_TYPE ON
			      T_USER_RELATIONSHIP_TYPE .user_relationship_type_code = R_USER_RELATIONSHIP.user_relationship_type_code
			    LEFT JOIN T_USER ON R_USER_RELATIONSHIP.uid_1 = T_USER.uid
			  WHERE uid_2 = ? AND user_relationship_type_label = ?";
	$statement = $conn->prepare($query);
	$statement->bind_param("is", $uid_2, $user_relationship_type_label);
	$statement->execute();
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }

	// ASSIGN THE USER VARIABLES
	$statement->bind_result($uid, $username, $first_name, $last_name);

	$users = array();

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
      array_push($users, $user);
	}

	$statement->close();

	// RETURN THE LIST OF USERS
	return $users;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

/* FUNCTION:    isFriend
 * NOTE:        User 1 is THIS user (i.e. the logged in user), User 2 is the OTHER.
 * DESCRIPTION: This method returns "true" if the two specified users are friends
 *              and "false" otherwise.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function isFriend($uid_1, $uid_2)
{
	// IMPORT REQUIRED METHODS
	require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
	
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
	
	$query = "SELECT 1
			  FROM R_USER_RELATIONSHIP
			  WHERE user_relationship_type_code = (
  				SELECT user_relationship_type_code 
  				FROM T_USER_RELATIONSHIP_TYPE 
  				WHERE user_relationship_type_label = 'Friend')
  				AND (
    			  (uid_1 = ? AND uid_2 = ?) OR (uid_2 = ? and uid_1 = ?)
  				)";
	$statement = $conn->prepare($query);
	$statement->bind_param("iiii", $uid_1, $uid_2, $uid_1, $uid_2);
	$statement->execute();
	$statement->store_result(); 	// Need this to check the number of rows later
	$error = $statement->error;
	// CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
	if ($error != "") { echo "DB ERROR: " . $error; return; }
	
	// ADD USER TO EVENT IF USER HAS NOT YET BEEN ADDED
	if ($statement->num_rows === 0) 
		return "true";
	else if ($statement->num_rows > 0)
		return "false";
	
	$statement->close();
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */



/* FUNCTION:    dbGetFriendsByTopNRandom
 * DESCRIPTION: Gets the data of a friend for all of the friends that are the first 
 *              N in the "randomly" selected order, where N is the input value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetFriendsByTopNRandom($uid, $top_n)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Miscellaneous.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/UserImage.php';

  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT T_USER.uid, username, first_name, last_name, user_relationship_upsert_timestamp 
            FROM   T_USER 
                   JOIN 
                   (
                     SELECT uid, user_relationship_upsert_timestamp 
                     FROM 
                     (
                       SELECT uid_1 AS uid, user_relationship_upsert_timestamp 
                       FROM   R_USER_RELATIONSHIP 
                              JOIN T_USER_RELATIONSHIP_TYPE 
                              ON R_USER_RELATIONSHIP.user_relationship_type_code = 
                                T_USER_RELATIONSHIP_TYPE.user_relationship_type_code 
                       WHERE  user_relationship_type_label = 'Friend' 
                              AND uid_2 = ?  
                       
                       UNION 
                       
                       SELECT uid_2 AS uid, user_relationship_upsert_timestamp 
                       FROM   R_USER_RELATIONSHIP 
                              JOIN T_USER_RELATIONSHIP_TYPE 
                              ON R_USER_RELATIONSHIP.user_relationship_type_code = 
                                T_USER_RELATIONSHIP_TYPE.user_relationship_type_code 
                       WHERE  user_relationship_type_label = 'Friend' 
                              AND uid_1 = ?  
                    ) as T1 
                    ORDER BY RAND() 
                    LIMIT ? 
                  ) T2 ON T_USER.uid = T2.uid 
            ORDER BY user_relationship_upsert_timestamp DESC";
  $statement = $conn->prepare($query);
  $statement->bind_param("iii", $uid, $uid, $top_n);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return formatJsonResponseError($error); }
  if ($statement->num_rows === 0) return formatJsonResponseSuccess("No such friend exists.");

  // ASSIGN THE RETURNED VALUES TO VARIABLES
  $statement->bind_result($uid, $username, $first_name, $last_name, $user_relationship_upsert_timestamp);

  $friends = array();

  while($statement->fetch())
  {
    $user_profile_images = dbGetImagesFirstNProfileByUid($uid);
    
    $friend = array
    (
        "uid" => $uid,
        "username" => $username,
        "firstName" => $first_name,
        "lastName" => $last_name, 
        "userRelationshipUpsertTimestamp" => $user_relationship_upsert_timestamp, 
        "userProfileImages" => $user_profile_images
    );

    array_push($friends, $friend);
  }
  $statement->close();

  $parent = array
  (
      "friends" => $friends
  );

  return $parent;
}



/* FUNCTION:    dbGetCountFriends
 * DESCRIPTION: Retrieves and returns the count of friends that the user with the
 *              specified uid has. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetCountFriends($uid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';

  // EXECUTE THE QUERY
  $query = "SELECT SUM(c) as countFriends 
            FROM 
            (
              SELECT COUNT(*) AS c
              FROM   R_USER_RELATIONSHIP 
              JOIN   T_USER_RELATIONSHIP_TYPE 
                     ON R_USER_RELATIONSHIP.user_relationship_type_code = T_USER_RELATIONSHIP_TYPE.user_relationship_type_code 
              WHERE  user_relationship_type_label = 'Friend' 
              AND    uid_1 = ?  
              
              UNION 
              
              SELECT COUNT(*) AS c
              FROM   R_USER_RELATIONSHIP 
              JOIN   T_USER_RELATIONSHIP_TYPE 
                  ON R_USER_RELATIONSHIP.user_relationship_type_code = T_USER_RELATIONSHIP_TYPE.user_relationship_type_code 
              WHERE  user_relationship_type_label = 'Friend' 
              AND    uid_2 = ?  
            ) as T";
  $statement = $conn->prepare($query);
  $statement->bind_param("ii", $uid, $uid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { return formatJsonResponseError($error); }
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->num_rows === 0) {
    return formatJsonResponseError("Contact the database administrator about the dbGetFriendCount PHP method.");
  }

  // ASSIGN THE RETURNED VALUES TO VARIABLES
  $statement->bind_result($countFriends);
  $statement->fetch();
  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $countFriends;
}

?>