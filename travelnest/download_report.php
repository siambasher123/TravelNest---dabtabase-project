<?php
/*
------------------------------------------------------------
File: download_report.php
Purpose: Allow admin to download the generated report file
------------------------------------------------------------
*/

$file = __DIR__ . '/report_output.txt';

if (file_exists($file)) {
  header('Content-Description: File Transfer');
  header('Content-Type: text/plain');
  header('Content-Disposition: attachment; filename="TravelNest_Report.txt"');
  header('Expires: 0');
  header('Cache-Control: must-revalidate');
  header('Pragma: public');
  header('Content-Length: ' . filesize($file));
  readfile($file);
  exit;
} else {
  echo "<h3 style='color:red; text-align:center;'>⚠️ Report file not found.<br>Please generate it first from the Admin Reports page.</h3>";
}
?>
