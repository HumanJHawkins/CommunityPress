<?php
// Login pages should just directly include sessionStart.php to avoid overwriting LastRequestedURL.
include 'sessionStart.php';

// Store URL to enable return on interrupt (i.e. Login, etc.)
$_SESSION['LastRequestedURL'] = $_SERVER['REQUEST_URI'];
?>

