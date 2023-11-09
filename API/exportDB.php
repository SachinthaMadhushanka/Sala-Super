<?php

include_once '../API/connectdb.php';
include '../configs/configs.php';

session_start();

$dbHost = dbHost;
$dbUsername = dbUsername;
$dbPassword = dbPassword;
$dbName = dbName;

// Create a unique filename with the current time
//$currentTime = date("Y-m-d");
//$backupFile = backupFolder . "/" . $currentTime . ".sql";
$backupFile = backupFolder;

// Make sure the backup directory exists
//if (!file_exists(backupFolder)) {
//  mkdir(backupFolder, 0777, true);
//}

// Command to run the mysqldump utility
$command = "mysqldump --opt -h$dbHost -u$dbUsername -p'$dbPassword' $dbName > $backupFile";

// Disable time limit to avoid script timing out
set_time_limit(0);

// Execute the command and capture the output and return value
exec($command, $output, $return_var);
// Clear all other output before this point


// Check the return value for success
if ($return_var === 0) {
  // No errors, backup succeeded
  echo json_encode(['success' => true, 'output' => $output]);
} else {
  // There was an error, read the error file
  echo json_encode(['success' => false,  'output' => $output]);
}






