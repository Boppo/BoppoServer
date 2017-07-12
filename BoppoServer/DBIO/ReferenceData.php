<?php

/* FUNCTION:    dbGetEventCategoryCode 
 * DESCRIPTION: Retrieves and returns the code representing a category of an
 *              event. 
* --------------------------------------------------------------------------------
* ================================================================================
* -------------------------------------------------------------------------------- */
function dbGetEventCategoryCode($event_category_label)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT event_category_code  
			FROM T_EVENT_CATEGORY 
			WHERE event_category_label = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $event_category_label);
  $statement->execute();
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE INVITE TYPE CODE
  $event_category_code = null;
  $statement->bind_result($event_category_code);
  $statement->fetch();
  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $event_category_code;
}

/* FUNCTION:    dbGetEventTypeCode
 * DESCRIPTION: Retrieves and returns the code representing a type of an event.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventTypeCode($event_category_label, $event_type_label)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';
  
  $event_category_code = dbGetEventCategoryCode($event_category_label);

  // ACQUIRE THE INVITE TYPE CODE
  $query = "SELECT event_type_code
			FROM T_EVENT_TYPE
			WHERE event_category_code = ? AND event_type_label = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("is", $event_category_code, $event_type_label);
  $statement->execute();
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE INVITE TYPE CODE
  $event_type_code = null;
  $statement->bind_result($event_type_code);
  $statement->fetch();
  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $event_type_code;
}

/* FUNCTION:    dbGetEventUserInviteStatusTypeCode
 * DESCRIPTION: Retrieves and returns the code representing a type of an 
 * \            event user invite status.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetEventUserInviteStatusTypeCode($event_user_invite_status_type_label)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // ACQUIRE THE EVENT USER INVITE STATUS TYPE CODE
  $query = "SELECT event_user_invite_status_type_code
			FROM T_EVENT_USER_INVITE_STATUS_TYPE
			WHERE event_user_invite_status_type_label = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $event_user_invite_status_type_label);
  $statement->execute();
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }

  // DEFAULT AND ASSIGN THE EVENT USER INVITE STATUS TYPE CODE
  $event_type_code = null;
  $statement->bind_result($event_user_invite_status_type_code);
  $statement->fetch();
  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $event_user_invite_status_type_code;
}

/* FUNCTION:    dbGetCountryByNumericCode
 * DESCRIPTION: Retrieves and returns the country information corresponding to the
 *              specified country numeric code.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetCountryByNumericCode($country_numeric_code)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT country_numeric_code, country_2c_mnemonic_code,
                   country_3c_mnemonic_code, country_english_name
            FROM   T_COUNTRY
            WHERE  country_numeric_code = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $country_numeric_code);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->num_rows === 0) {
    echo "No such country exists for the specified country numeric code.";
    return;
  }

  $statement->bind_result(
      $country_numeric_code, $country_2c_mnemonic_code, $country_3c_mnemonic_code, $country_english_name
      );
  $statement->fetch();

  // DEFAULT AND ASSIGN THE RETURN VARIABLE
  $country = array
  (
      "countryNumericCode" => $country_numeric_code,
      "country2cMnemonicCode" => $country_2c_mnemonic_code,
      "country3cMnemonicCode" => $country_3c_mnemonic_code,
      "countryEnglishName" => $country_english_name
  );

  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $country;
}

/* FUNCTION:    dbGetCountryBy3cMnemonicCode
 * DESCRIPTION: Retrieves and returns the country information corresponding to the
 *              specified three-character country mnemonic code.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetCountryBy3cMnemonicCode($country_3c_mnemonic_code)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBConnect/dbConnect.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT country_numeric_code, country_2c_mnemonic_code,
                   country_3c_mnemonic_code, country_english_name
            FROM   T_COUNTRY
            WHERE  country_3c_mnemonic_code = ?";
  $statement = $conn->prepare($query);
  $statement->bind_param("s", $country_3c_mnemonic_code);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $statement->error;

  echo "TEST: " . $country_3c_mnemonic_code . "<br>";

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  if ($statement->num_rows === 0) {
    echo "No such country exists for the specified three-character country mnemonic code.";
    return;
  }

  $statement->bind_result(
      $country_numeric_code, $country_2c_mnemonic_code, $country_3c_mnemonic_code, $country_english_name
      );
  $statement->fetch();

  // DEFAULT AND ASSIGN THE RETURN VARIABLE
  $country = array
  (
      "countryNumericCode" => $country_numeric_code,
      "country2cMnemonicCode" => $country_2c_mnemonic_code,
      "country3cMnemonicCode" => $country_3c_mnemonic_code,
      "countryEnglishName" => $country_english_name
  );

  $statement->close();

  // RETURN THE INVITE TYPE CODE
  return $country;
}

?>