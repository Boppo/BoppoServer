<?php

/* FUNCTION:    saveToLog
 * DESCRIPTION: Saves the input string to a log file of input type with the input label. 
 *              For good practice, use "__FUNCTION__" for the label to keep track of the 
 *              calling method. This function organizes log messages into daily log files. 
 * ----------------------------------------------------------------------------------------
 * ========================================================================================
 * ---------------------------------------------------------------------------------------- */
function saveToLog($string, $label, $logTypeLabel)
{
  $currentDate = getdate();
  $logFileName =   str_pad($currentDate["year"], 4, "0", STR_PAD_LEFT) . "-" 
                 . str_pad($currentDate["mon"], 2, "0", STR_PAD_LEFT) . "-" 
                 . str_pad($currentDate["mday"], 2, "0", STR_PAD_LEFT) . ".txt"; 
  $LogEntryName = "- " . $label . " " . str_repeat("-", 60 - 4 + 1 - strlen($label));
  $logEntryTime = "- " 
                  . str_pad($currentDate["hours"], 2, "0", STR_PAD_LEFT) . ":" 
                  . str_pad($currentDate["minutes"], 2, "0", STR_PAD_LEFT) . ":" 
                  . str_pad($currentDate["seconds"], 2, "0", STR_PAD_LEFT) . " "
                  . str_repeat("-", 60 - 12 + 1);
  $logEntrySeparator = str_repeat("-", 60);
  if ($logTypeLabel == "System")
    $logFileDirectory = dirname($_SERVER['DOCUMENT_ROOT']) . "/log/system/";
  else if ($logTypeLabel == "Error")
    $logFileDirectory = dirname($_SERVER['DOCUMENT_ROOT']) . "/log/error/";
  file_put_contents($logFileDirectory . $logFileName, $logEntrySeparator . "\r\n", FILE_APPEND);
  file_put_contents($logFileDirectory . $logFileName, $LogEntryName . "\r\n", FILE_APPEND);
  file_put_contents($logFileDirectory . $logFileName, $logEntryTime . "\r\n", FILE_APPEND);
  file_put_contents($logFileDirectory . $logFileName, $logEntrySeparator . "\r\n", FILE_APPEND);
  file_put_contents($logFileDirectory . $logFileName, $string . "\r\n", FILE_APPEND);
  file_put_contents($logFileDirectory . $logFileName, "\r\n", FILE_APPEND);
}

?>