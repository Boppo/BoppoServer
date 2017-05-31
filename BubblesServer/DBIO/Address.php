<?php

/* FUNCTION:    dbGetAddressByEid
 * DESCRIPTION: Retrieves and returns all of the data related to an address for
 *              the specified eid (Event Identifier). 
* --------------------------------------------------------------------------------
* ================================================================================
* -------------------------------------------------------------------------------- */
function dbGetAddressByEid($eid)
{
  // IMPORT THE DATABASE CONNECTION
  require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBIO/Miscellaneous.php';

  // ACQUIRE THE INVITE TYPE LABEL
  $query = "SELECT aid, address_unparsed_text, address_country_numeric_code, 
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
  if ($statement->num_rows === 0) { echo "No such address exists for the specified event."; return; }
  
  // DEFAULT AND ASSIGN THE EVENT VARIABLES
  $statement->bind_result(
    $aid, $address_unparsed_text, $address_country_numeric_code, 
    $address_country_division_name, $address_postal_code, 
    $address_country_subdivision_name, $address_municipality_name, 
    $address_street_name, $address_prefix_label, $address_suffix_label, 
    $address_street_number);
  $statement->fetch();
  
  $country = dbGetCountryByNumericCode($address_country_numeric_code);
  
  $address = array
  (
      "aid" => $aid,
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