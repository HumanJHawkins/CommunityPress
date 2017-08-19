<?php
// Login pages should just directly include sessionStart.php to avoid overwriting lastURL.
include_once 'sessionStart.php';

// TO DO: Something is misconfigured, causing header links and favicon to get stored in the
//  $_SERVER['REQUEST_URI']. Figure it out and fix it, then remove this massive kluge.
$lastThreeChars = substr($_SERVER['REQUEST_URI'],0,-3);
if (
    $lastThreeChars == 'ico' ||
    $lastThreeChars == 'css' ||
    $lastThreeChars == '.js' ||
    strpos ( $_SERVER['REQUEST_URI'], 'icon/' ) > 0
  ) {
  ; // Do nothing
} else {
  $_SESSION['lastURL'] = $_SERVER['REQUEST_URI'];
}

?>

