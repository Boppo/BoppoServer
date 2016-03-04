<?php

/* FUNCTION: booleanToChar
 * DESCRIPTION: Converts a boolean value into a character value.
 * --------------------------------------------------------------------------------
 * ================================================================================
 * -------------------------------------------------------------------------------- */
function booleanToChar($inputBoolean)
{
	$outputChar = "";
	
	if ($inputBoolean == false)
		$outputChar = 0;
	elseif ($inputBoolean == true)
		$outputChar = 1;
	
	return $outputChar;
}

function charToBoolean($inputChar)
{
	$outputBoolean = null;
	
	if ($inputChar === "0" || $inputChar === "N") 
		$outputBoolean = "false";
	elseif ($inputChar === "1" || $inputChar === "Y") 
		$outputBoolean = "true";
	
	return $outputBoolean;
}
?>