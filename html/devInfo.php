<?php
  include 'pageHeader.php';
  // Show the ID, etc.
  htmlStart('Dev Info');
  echo '<span style="font-family:\'Courier New\',monospace">';
  if ($_SESSION['isSiteDeveloper'] || $_SESSION['isSuperUser']) {
    // print_r($GLOBALS);
    debugOut('BEGIN FUNCTIONS OR DATA', '', true, false, false);
    debugOut('get_include_path()', get_include_path(), true);
    debugOut('session_id()', session_id(), true);
    debugOut('ipAddress()', ipAddress(), true);
    debugOut('saltHash Time Cost', getSaltHashTimeCost($GLOBALS['PASSWORD_HASH_COST']) . 'ms', true);
    debugOut('verifyCode Time Cost', getSaltHashTimeCost($GLOBALS['VERIFYCODE_HASH_COST']) . 'ms', true);
    debugOut('END SELECTED VARIABLES AND FUNCTIONS', '', true, false, false);
    debugOut('', '', true, false, false);
    debugOut('BEGIN $GLOBALS ARRAY, INCLUSIVE OF $_SESSION, $_SERVER, ETC.', '', true, false, false);
    outputArray($GLOBALS, true);
    debugOut('END $GLOBALS ARRAY', '', true, false, false);
    debugOut('', '', true, false, false);
    echo '</span>';
    phpinfo();
  }
?>

</body>
</html>
