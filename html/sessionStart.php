<?php
include_once("config.php");
include_once("functions.php");

// Put a blank line in the log at the top of each page.
debugOut('', '', false, false, false);

// If session_start created a new session, log it.
if (session_start()) {
  $_SESSION['ipAddress'] = ipAddress();
  debugOut('$_SESSION[\'ipAddress\']', $_SESSION['ipAddress']);
  
  // We won't have user ID at this point, so log the session without it. On login, update
  //  the session record.
  if (!isset($pdo)) {
    $pdo = getDBPDO();
  }
  $sql = 'SELECT addOrUpdateSession(?, ?, ?)';
  $sqlParamArray = [session_id(), $_SESSION['ipAddress'], session_encode()];
  $row = getOnePDORow($pdo, $sql, $sqlParamArray);
  
  // Store these in the session, but allow refresh once per hour just to be safe.
  if (isset($_SESSION['sessionTimestamp'])) {
    $sessionStartTime = new DateTime($_SESSION['sessionTimestamp']);
  } else {
    $sessionStartTime = new DateTime('2000-01-01');
  }
  $timeDiff = $sessionStartTime->diff(new DateTime());
  $sessionAge = ($timeDiff->days * 1440) + ($timeDiff->h * 60) + ($timeDiff->i);
  debugOut('sessionAge', $sessionAge . ' minutes and ' . ($timeDiff->s) . 'seconds');
  
  if (
    !isset($_SESSION["tagCategoryTagID"]) ||
    ($_SESSION["tagCategoryTagID"] == '') ||
    ($sessionAge > 30)
  ) {
    $sql = 'CALL procServerConfig()';
    $arrayParams = null;
    $row = getOnePDORow($pdo, $sql, $arrayParams, PDO::FETCH_ASSOC);
    if (!empty($row)) {
      foreach ($row as $key => $val) {
        $_SESSION[$key] = $val;
      }
    }
  }
};

setLastURL();