<?php

$function = $_GET['function'];

if ($function == "addUnparsedAddress")
  addUnparsedAddress();


  
  
  
  /* FUNCTION:    addUnparsedAddress
   * DESCRIPTION: Adds the specified unparsed address to the database. 
   * --------------------------------------------------------------------------------
   * ================================================================================
   * -------------------------------------------------------------------------------- */
  function addUnparsedAddress()
  {
    /* THE FOLLOWING 3 LINES OF CODE ENABLE ERROR REPORTING. */
    error_reporting(E_ALL);
    ini_set('display_errors', TRUE);
    ini_set('display_startup_errors', TRUE);
    /* END. */
  
    // IMPORT THE DATABASE CONNECTION FUNCTION AND OTHER REQUIRED FUNCTIONS
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/DBConnect/dbConnect.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/BubblesServer/Functions/Miscellaneous.php';
    // DECODE JSON STRING
    $json_decoded = json_decode(file_get_contents("php://input"), true);
    // ASSIGN THE JSON VALUES TO VARIABLES
    $address_name = $json_decoded["addressName"];
    $address_unparsed_text = $json_decoded["addressUnparsedText"];
    
    // FIRST CHECK IF UNPARSED ADDRESS ALREADY EXISTS, RETURN ITS AID IF SO
    $query = "SELECT aid FROM T_ADDRESS WHERE address_unparsed_text = ?";
    $statement = $conn->prepare($query);
    $statement->bind_param("s", $address_unparsed_text);
    $statement->execute();
    $statement->store_result(); 	// Need this to check the number of rows later
    $error = $statement->error;
    // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS
    if ($error != "") { echo formatJsonResponseError($error); return; }
    // RETURN THE AID IF SUCH AN UNPARSED ADDRESS ALREADY EXISTS
    if ($statement->num_rows === 1) 
    { 
      // DEFAULT AND ASSIGN THE EVENT VARIABLES
      $statement->bind_result($aid);
      $statement->fetch();
      echo formatJsonResponseSuccess(array("aid" => $aid)); 
      return; 
    }
    	
    // PREPARE AND EXECUTE THE QUERY
    $query = "INSERT INTO T_ADDRESS (address_name, address_unparsed_text) VALUES (?, ?)";
    $statement = $conn->prepare($query);
    $statement->bind_param("ss", $address_name, $address_unparsed_text);
    $statement->execute();
    $statement->error;
    
    // CHECK FOR AN ERROR, RETURN IT IF ONE EXISTS 
    $error = $statement->error;
    if ($error != "") { echo formatJsonResponseError($error); return; }
    
    echo formatJsonResponseSuccess(array("aid" => $conn->insert_id));
    return;
  }
  
  /* --------------------------------------------------------------------------------
   * ================================================================================
   * -------------------------------------------------------------------------------- */

?>