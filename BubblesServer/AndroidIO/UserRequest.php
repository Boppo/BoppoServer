<?php

$function = $_GET['function'];

if ($function == "setUser")
  setUser();

  
  
/* FUNCTION: setUser
 * DESCRIPTION: Updates the user's properties in the database.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

function setUser()
{
  // THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. //
  error_reporting(E_ALL);
  ini_set('display_errors', TRUE);
  ini_set('display_startup_errors', TRUE);
  // END. //

  // ESTABLISH DATABASE CONNECTION //
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

  // DECODE INCOMING JSON CONTENTS //
  $json_decoded = json_decode(file_get_contents("php://input"), true);
  
  $uid = $json_decoded["uid"];
  $first_name = $json_decoded["firstName"];
  $last_name = $json_decoded["lastName"];
  $email = $json_decoded["email"];
  $phone = $json_decoded["phone"];
  $user_account_privacy_label = $json_decoded["userAccountPrivacyLabel"];

  // MAKE SURE THAT A VALID USER IDENTIFIER WAS PROVIDED
  if ($uid <= 0) {
    echo "ERROR: Incorrect user identifier specified.";
    return; }

  // ENCODE THE PRIVACY LABEL
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Privacy.php';
  $user_account_privacy_code = fetchPrivacyCode($user_account_privacy_label);
  if (!($json_decoded["userAccountPrivacyLabel"] == null || $user_account_privacy_code != null)) {
    echo "ERROR: Incorrect user account privacy specified.";
    return; }

  // SEND THE NEW VALUES IN AN EVENT OBJECT TO THE CORRESPONDING DBIO METHOD
  $user = array
  (
    "uid" => $uid,
    "firstName" => $first_name, 
    "lastName" => $last_name, 
    "email" => $email, 
    "phone" => $phone, 
    "userAccountPrivacyCode" => $user_account_privacy_code
  );
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/User.php';
  $response = dbSetUser($user);

  echo $response;
}
/* --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */

?>