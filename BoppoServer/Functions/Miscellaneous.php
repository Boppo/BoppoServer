<?php

/* FUNCTION:    strBoolToChar
 * DESCRIPTION: Converts a boolean that is stored as a string into a character.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function strBoolToChar($strBool)
{
	$char = "";
	
	if ($strBool === "false")
		$char = "0";
	elseif ($strBool === "true")
		$char = "1";
	
	return $char;
}

/* FUNCTION:    charToStrBool
 * DESCRIPTION: Converts a boolean that is stored as a character into a string.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function charToStrBool($char)
{
	$strBool = "";
	
	if ($char === "0" || $char === "N") 
		$strBool = "false";
	elseif ($char === "1" || $char === "Y") 
		$strBool = "true";
	
	return $strBool;
}

/* FUNCTION:    contains
 * DESCRIPTION: Checks if the input string contains another input string. 
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function contains($containingString, $containedString)
{
  if (strpos($containingString, $containedString) !== false)
    return true; 
  else 
    return false; 
}



/* FUNCTION:    compareDates
 * DESCRIPTION: Compares the difference of the two input dates with the specified duration.
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function compareDates($date1, $date2, $time_unit)
{
	$diff = date_diff($date1, $date2);
	
	if (strtolower($time_unit) === "second")
		$diff = $diff->format("%a")*24*60*60 + $diff->h*60*60 + $diff->m*60 + $diff->s;
	elseif (strtolower($time_unit) === "minute")
		$diff = $diff->format("%a")*24*60 + $diff->h*60 + $diff->m;
	elseif (strtolower($time_unit) === "hour")
		$diff = $diff->format("%a")*24 + $diff->h;
	elseif (strtolower($time_unit) === "day")
		$diff = $diff->format("%a");
	elseif (strtolower($time_unit) === "month")
		$diff = $diff->y*12 + $diff->m;
	elseif (strtolower($time_unit) === "year")
		$diff = $diff->y;
	
	return $diff;
}

/* FUNCTION:    compareDateDifferences
 * DESCRIPTION: Returns a string stating whether the date difference is in or out of bounds.
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function compareDateDifferences($diff1, $diff2)
{
	if ($diff1 > $diff2)
		return "The time difference is out of bounds.";
	else
		return "The time difference is within bounds.";
}

/* FUNCTION:    formatResponseError
 * DESCRIPTION: Returns the input string as a formatted error array. 
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function formatResponseError($error)
{
  $response = array
  (
    "responseType" => "ERROR", 
    "response" => $error
  );
  return $response;
}

/* FUNCTION:    formatResponseSuccess
 * DESCRIPTION: Returns the input string as a formatted success array.
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function formatResponseSuccess($success)
{
  $response = array
  (
    "responseType" => "Success", 
    "response" => $success
  );
  return $response;
}

/* FUNCTION:    removeString
 * DESCRIPTION: Returns the input string with the other input string removed from it.
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function removeString($stringToRemoveFrom, $stringToRemove)
{
  return str_replace($stringToRemove, "", $stringToRemoveFrom);
}

/* FUNCTION:    replaceString
 * DESCRIPTION: Returns the input string with the other input string replaced by yet
 *              another input string.
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function replaceString($stringToRemoveFrom, $stringToReplace, $stringToReplaceWith)
{
  return str_replace($stringToRemove, $stringToReplaceWith, $stringToRemoveFrom);
}

/* FUNCTION:    saveToSystemLog
 * DESCRIPTION: Saves the input string to a system log file with the input label. For good
 *              practice, use "__FUNCTION__" for the label to keep track of the calling 
 *              method. This function organizes log messages into daily log files. 
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function saveToSystemLog($string, $label)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Log.php'; 
  saveToLog($string, $label, "System");
}

/* FUNCTION:    saveToErrorLog
 * DESCRIPTION: Saves the input string to an error log file with the input label. For good
 *              practice, use "__FUNCTION__" for the label to keep track of the calling
 *              method. This function organizes log messages into daily log files.
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function saveToErrorLog($string, $label)
{
  // IMPORT REQUIRED METHODS
  require_once $_SERVER['DOCUMENT_ROOT'] . '/BoppoServer/Functions/Log.php';
  saveToLog($string, $label, "Error");
}

?>