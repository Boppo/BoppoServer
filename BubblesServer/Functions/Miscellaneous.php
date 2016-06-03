<?php

/* FUNCTION: strBoolToChar
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

/* FUNCTION: charToStrBool
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

/* FUNCTION: compareDates
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

function compareDateDifferences($diff1, $diff2)
{
	if ($diff1 > $diff2)
		return "The time difference is out of bounds.";
	else
		return "The time difference is within bounds.";
}


?>