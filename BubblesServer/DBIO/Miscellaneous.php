<?php

/* FUNCTION:    incrementObjectViewCount
 * DESCRIPTION: Incremenets the view count of the specified object by 1.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbIncrementObjectViewCount($oid, $object_type_label)
{
	// IMPORT THE DATABASE CONNECTION
	require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

	// EXECUTE THE QUERY
	if (strcmp($object_type_label, "User Image") === 0)
		$query = "UPDATE T_USER_IMAGE
				  SET user_image_view_count = user_image_view_count + 1
				  WHERE uiid = ?;";
	else if (strcmp($object_type_label, "Event") === 0)
		$query = "UPDATE T_EVENT
				  SET event_view_count = event_view_count + 1
				  WHERE eid = ?;";
	$statement = $conn->prepare($query);
	$statement->bind_param("i", $oid);
	$statement->execute();

	if ($statement->affected_rows === 1)
	{
		return "Object view count successfully incremented by 1.";
	}
	else if ($statement->affected_rows === 0)
	{
		return "Object view count failed to increment because the object or object instance does not exist.";
	}
	else
	{
		return "QUERY FLAWED: Please contact the database administrator with this method's name
			  because something went wrong!";
	}

	$statement->close();
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
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

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
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';

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