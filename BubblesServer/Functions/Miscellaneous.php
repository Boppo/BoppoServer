<?php

/* FUNCTION: booleanToChar
 * DESCRIPTION: Converts a boolean value into a character value.
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

function charToStrBool($char)
{
	$strBool = "";
	
	if ($char === "0" || $char === "N") 
		$strBool = "false";
	elseif ($char === "1" || $char === "Y") 
		$strBool = "true";
	
	return $strBool;
}
?>