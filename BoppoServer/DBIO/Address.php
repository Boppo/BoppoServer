<?php

/* FUNCTION:    dbDeleteAddressIfUnused
 * DESCRIPTION: Checks if the address with the specified aid is used by any event
 *              other than the one with the specified eid. If not, deletes it.
* --------------------------------------------------------------------------------
* ================================================================================
* -------------------------------------------------------------------------------- */
function dbDeleteAddressIfUnused($aid, $eid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/Miscellaneous.php';
  
  // CHECK IF THIS IS THE ONLY EVENT USING THIS ADDRESS
  $query = "SELECT COUNT(*) FROM T_EVENT WHERE event_aid = ? AND eid <> ?";
  
  $statement = $conn->prepare($query);
  
  $statement->bind_param("ii", $aid, $eid);
  $statement->execute();
  $error = $statement->error;
  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  if ($error != "") { return formatJsonResponseError($error); }
  
  $statement->bind_result($count);
  $statement->fetch();
  $statement->close(); 
  
  // IF SO, DELETE THE ADDRESS
  if ($count <= 0)
  {
    $query = "DELETE FROM T_ADDRESS WHERE aid = ?";
    
    $statement = $conn->prepare($query);
        
    $statement->bind_param("i", $aid);
    $statement->execute();
    $error = $statement->error;
    // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
    if ($error != "") { return formatJsonResponseError($error); }
    
    return formatJsonResponseSuccess("");
  }
  else 
  {
    return formatJsonResponseError("Cannot delete the address because it is being used by another event.");
  }
  
}



/* FUNCTION:    dbGetAddressByEid
 * DESCRIPTION: Retrieves and returns all of the data related to an address for
 *              the specified eid (Event Identifier).
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function dbGetAddressByEid($eid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/_DBConnect.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/DBIO/ReferenceData.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT aid, address_name, address_unparsed_text, address_country_numeric_code,
                   address_country_division_name, address_postal_code,
                   address_country_subdivision_name, address_municipality_name,
                   address_street_name, address_prefix_label, address_suffix_label,
                   address_street_number
            FROM T_ADDRESS
            WHERE aid IN
              (SELECT event_aid FROM T_EVENT WHERE eid = ?)";
  $statement = $conn->prepare($query);
  $statement->bind_param("i", $eid);
  $statement->execute();
  $statement->store_result(); 	// Need this to check the number of rows later
  $statement->error;

  // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
  $error = $statement->error;
  if ($error != "") { echo "DB ERROR: " . $error; return; }
  // CHECK FOR THE COUNT OF RESULTS, RETURN A MESSAGE IF NONE EXIST
  //if ($statement->num_rows === 0) { echo "No such address exists for the specified event."; return; }

  // DEFAULT AND ASSIGN THE EVENT VARIABLES
  $statement->bind_result(
      $aid, $address_name, $address_unparsed_text, $address_country_numeric_code,
      $address_country_division_name, $address_postal_code,
      $address_country_subdivision_name, $address_municipality_name,
      $address_street_name, $address_prefix_label, $address_suffix_label,
      $address_street_number);
  $statement->fetch();

  if(!$address_country_numeric_code)
  {
    $country = null;
  }
  else 
  {
    $country = dbGetCountryByNumericCode($address_country_numeric_code);
  }

  $address = array
  (
      "aid" => $aid, 
      "addressName" => $address_name, 
      "addressUnparsedText" => $address_unparsed_text,
      "addressCountryNumericCode" => $address_country_numeric_code,
      "addressCountryDivisionName" => $address_country_division_name,
      "addressPostalCode" => $address_postal_code,
      "addressCountrySubdivisionName" => $address_country_subdivision_name,
      "addressMunicipalityName" => $address_municipality_name,
      "addressStreetName" => $address_street_name,
      "addressPrefixLabel" => $address_prefix_label,
      "addressSuffixLabel" => $address_suffix_label,
      "addressStreetNumber" => $address_street_number,
      "addressCountry" => $country
  );

  $statement->close();

  return $address;
}
?>