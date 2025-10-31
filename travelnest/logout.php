<?php
/*
------------------------------------------------------------
File: logout.php
Purpose: Destroy session and redirect safely to homepage
------------------------------------------------------------
*/
session_start();

// ✅ Destroy all session data
session_unset();
session_destroy();

// ✅ Redirect to homepage
header('Location: index.php?page=home');
exit;
?>
