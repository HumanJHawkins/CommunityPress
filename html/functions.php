<?php

require './mailgun-php/vendor/autoload.php';
use Mailgun\Mailgun;

function mailgunSend($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML = null,
                     $mailCC = null, $mailBCC = null, $mailAttachmentsArray = null)
{
  $sendArray['from'] = $mailFrom;
  $sendArray['to'] = $mailTo;
  $sendArray['subject'] = $mailSubject;
  $sendArray['text'] = $mailText;
  if ($mailHTML != null) {
    $sendArray['html'] = $mailHTML;
  }
  if ($mailCC != null) {
    $sendArray['cc'] = $mailCC;
  }
  if ($mailBCC != null) {
    $sendArray['bcc'] = $mailBCC;
  }
  if ($mailAttachmentsArray != null) {
    $sendArray['attachment'] = $mailAttachmentsArray;
  }
  
  $mg = new Mailgun($GLOBALS['MAILGUN_API_KEY']);
  $mg->sendMessage($GLOBALS['MAILGUN_MAIL_DOMAIN'], $sendArray);
}


function tagCategorySelector($connection)
{
  $sql = 'SELECT DISTINCT tagCategoryID, tagCategory FROM vTag';
  $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
  echo '<select name="tagCatSelect" id="tagCatSelect">';
  echo '<option value="0">Select Category...</option>';
  while ($rows = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    outputArray($rows);
    echo '<option ';
    if (isset($_POST["tagCategory"]) && $_POST["tagCategory"] == $rows['tagCategory']) {
      echo 'selected="selected" ';
    }
    echo 'value="' . $rows['tagCategoryID'] . '">' . $rows['tagCategory'] . '</option>';
  }
  echo'</select>';
}


function tagSelector($connection, $tagCategoryID)
{
  $sql = 'SELECT DISTINCT tagID, tag FROM vTag';
  if($tagCategoryID) {
    $sql = $sql . ' WHERE tagCategoryID = ' . $tagCategoryID;
  }
  
  $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
  echo '<select name="tagSelect" id="tagSelect">';
  while ($rows = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
    outputArray($rows);
    echo '<option ';
    if (isset($_POST["tag"]) && $_POST["tag"] == $rows['tag']) {
      echo 'selected="selected" ';
    }
    echo 'value="' . $rows['tagID'] . '">' . $rows['tag'] . '</option>';
  }
  echo'</select>';
}


function getStoredProcResults($connection, $sql)
{
  if (!$connection->multi_query($sql)) {
    debugOut("$connection->errno", $connection->errno, true);
    debugOut("$connection->error", $connection->error, true);
  }
  
  $resultSet[] = '';
  $resultNum = 0;

  do {
    if ($result = $connection->store_result()) {
      while ($row = $result->fetch_assoc()) {
        $resultSet[$resultNum][] = $row;
      }
      $result->free();
      $resultNum++;
  } else {
    if ($connection->errno) {
      debugOut("$connection->errno", $connection->errno, true);
      debugOut("$connection->error", $connection->error, true);
    }
  }
} while ($connection->more_results() && $connection->next_result()) ;

debugOut("getStoredProcResults() Array Dump:");
outputArray($resultSet);

return $resultSet;
}

function getOneStoredProcTable($connection, $sql)
{
  return getStoredProcResults($connection, $sql)[0];
}

function getOneStoredProcRow($connection, $sql)
{
  return getStoredProcResults($connection, $sql)[0][0];
}

/*
function getOneStoredProcRow($connection, $sql)
{
  if (!$connection->multi_query($sql)) {
    debugOut("$connection->errno", $connection->errno, true);
    debugOut("$connection->error", $connection->error, true);
  }
  
 do {
    if ($result = $connection->store_result()) {
      $row = $result->fetch_assoc();
      $result->free();
    } else {
      if ($connection->errno) {
        debugOut("$connection->errno", $connection->errno, true);
        debugOut("$connection->error", $connection->error, true);
      }
    }
  } while ($connection->more_results() && $connection->next_result());
  
  debugOut("getOneStoredProcRow() Array Dump:");
  outputArray($row);
  
  return $row;
}
*/

function sendEmail($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML = null,
                   $mailCC = null, $mailBCC = null, $mailAttachmentsArray = null)
{
  // Abstracted here to allow easy switch to other mail services.
  // Write and use an AmazonSESSend() function for example, if needed.
  mailgunSend($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML, $mailCC, $mailBCC, $mailAttachmentsArray);
}


function outputArray($theArray, $echo = false, array $arrayBreadcrumbs = NULL, $showRowCount = true)
{
  // Will this make the world explode? Let's see...
  // ksort($GLOBALS);
  
  $rowCount = 0;
  $prefix = '';
  
  if (is_array($theArray)) {
    foreach ($theArray as $key => $val) {
      $rowCount++;
      
      if($arrayBreadcrumbs) {
        $prefix = implode('|', $arrayBreadcrumbs);
      }
      
      if ($prefix != '') {
        $prefix = $prefix . '|';
      }
      
      if (is_object($val)) {
        $arrayBreadcrumbs[] = $key;
        $val = get_object_vars($val);
        debugOut($prefix . $key, 'Object (row count: ' . count($val) . ')', $echo);
      } elseif (is_array($val)) {
        $arrayBreadcrumbs[] = $key;
        debugOut($prefix . $key, 'Array  (row count: ' . count($val) . ')', $echo);
      } else {
        // Avoid output of DB_PASSWORD, etc.
        if (strpos(strtolower($key), 'password') !== false) {
          $val = '*******************';
        }
        debugOut($prefix . $key, $val, $echo);
      }
      
      if (is_array($val)) {
        if ($theArray == $val) { // Arrays can contain references to themselves. Prevent endless recursion
          debugOut($prefix . $key, 'Not shown. Recursing this would create an infinite loop', $echo);
        } else {
          $rowCount += outputArray($val, $echo, $arrayBreadcrumbs);
        }
      }
      
      if($arrayBreadcrumbs) {
        array_pop($arrayBreadcrumbs);
      }
    }
  }
  return $rowCount;
}

function debugPrefix()
{
  $debugPrefix = ltrim($_SERVER['DOCUMENT_URI'], '/');
  if (isset($_SESSION['loginStep'])) {
    $debugPrefix = $debugPrefix . ':' . $_SESSION['loginStep'];
  }
  return $debugPrefix;
}

function debugTimestamp()
{
  $time = new DateTime();
  return $time->format('YmdHis');
}

function debugOut($heading = '', $detail = '', $echo = false, $prefix = true, $timestamp = true)
{
  if ($prefix) {
    if ($heading == '') {
      $heading = debugPrefix();
    } else {
      $heading = debugPrefix() . ':' . $heading;
    }
  }
  
  if ($timestamp) {
    if ($heading == '') {
      $heading = debugTimestamp();
    } else {
      $heading = debugTimestamp() . ':' . $heading;
    }
  }
  
  if ($detail != '') {
    $heading = $heading . '=' . $detail;
  }
  
  if ($echo) {
    echo '<br />' . $heading;
  }
  
  error_log($heading . PHP_EOL, 3, $GLOBALS['LOG_FILE_PATH']);
}

function debugSectionOut($sectionTitle)
{
  debugOut('', '', false, false, false);
  debugOut('***** ' . $sectionTitle . ':');
  debugOut('*****   $_SESSION:');
  outputArray($_SESSION);
  debugOut('*****   $_POST:');
  outputArray($_POST);
}

function getDBConnection()
{
  $connection = mysqli_connect($GLOBALS['DB_SERVER'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_DATABASE'], $GLOBALS['DB_PORT']);
  if (!$connection) {
    echo "Error: Unable to connect to MySQL.<br />";
    echo "Debugging errno: " . mysqli_connect_errno() . '<br />';
    echo "Debugging error: " . mysqli_connect_error() . '<br />';
    return null;
  }
  return $connection;
}


function getDBPDO()
{
  $pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_OPTIONS']);
  return $pdo;
}


function logout()
{
  destroySession();
  header('Location: ' . $GLOBALS['SITE_URL']);
  exit();
}


function destroySession()
{
  // Unset all of the session variables.
  $_SESSION = array();
  
  // Delete session cookie.
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
      $params["path"], $params["domain"],
      $params["secure"], $params["httponly"]
    );
  }
  
  session_destroy();
}

function getSaltHashTimeCost($iterations)
{
  $timeStart = round(microtime(true) * 1000);
  password_hash('TestPassword', PASSWORD_DEFAULT, ["cost" => $iterations]);
  $timeEnd = round(microtime(true) * 1000);
  return $timeEnd - $timeStart;
}

function ipAddress()
{
  if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_CLIENT_IP'];
  elseif (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_X_FORWARDED_FOR'];
  else return $_SERVER['REMOTE_ADDR'];
}

// Adapted from Stephen Watkins answer at https://stackoverflow.com/questions/4356289/php-random-string-generator
function verifyCode($length = 4)
{
  $charSet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Don't use ambiguous characters (O vs. 0, etc.)
  $charSetLen = strlen($charSet);
  $code = '';
  for ($i = 0; $i < $length; $i++) {
    $code .= $charSet[rand(0, $charSetLen - 1)];
  }
  return $code;
}

function htmlStart($string, $showBody = true)
{
  echo '<!DOCTYPE html>';
  echo '<html lang="en">';
  echo '<head>';
  echo '<meta charset="utf-8">';
  echo '<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">';
  echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
  
  // Normalize first, so framework and my changes are not overridden.
  echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.css" ' .
    'integrity="sha256-sxzrkOPuwljiVGWbxViPJ14ZewXLJHFJDn0bv+5hsDY=" crossorigin="anonymous" />';
  
  echo '<link rel="apple-touch-icon" sizes="57x57" href="icon/apple-icon-57x57.png">';
  echo '<link rel="apple-touch-icon" sizes="60x60" href="icon/apple-icon-60x60.png">';
  echo '<link rel="apple-touch-icon" sizes="72x72" href="icon/apple-icon-72x72.png">';
  echo '<link rel="apple-touch-icon" sizes="76x76" href="icon/apple-icon-76x76.png">';
  echo '<link rel="apple-touch-icon" sizes="114x114" href="icon/apple-icon-114x114.png">';
  echo '<link rel="apple-touch-icon" sizes="120x120" href="icon/apple-icon-120x120.png">';
  echo '<link rel="apple-touch-icon" sizes="144x144" href="icon/apple-icon-144x144.png">';
  echo '<link rel="apple-touch-icon" sizes="152x152" href="icon/apple-icon-152x152.png">';
  echo '<link rel="apple-touch-icon" sizes="180x180" href="icon/apple-icon-180x180.png">';
  echo '<link rel="icon" type="image/png" sizes="192x192"  href="icon/android-icon-192x192.png">';
  echo '<link rel="icon" type="image/png" sizes="32x32" href="icon/favicon-32x32.png">';
  echo '<link rel="icon" type="image/png" sizes="96x96" href="icon/favicon-96x96.png">';
  echo '<link rel="icon" type="image/png" sizes="16x16" href="icon/favicon-16x16.png">';
  echo '<link rel="manifest" href="icon/manifest.json">';
  echo '<meta name="msapplication-TileColor" content="#ffffff">';
  echo '<meta name="msapplication-TileImage" content="icon/ms-icon-144x144.png">';
  echo '<meta name="theme-color" content="#ffffff">';
  
  // Bootstrap and jQuery. Order is important, so both here...
  echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" ' .
    'integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">';
  echo '<script src="https://code.jquery.com/jquery-3.2.1.min.js" ' .
    'integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>';
  echo '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" ' .
    'integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>';
  // Optional Bootstrap theme
  echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"' .
    ' integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">';
  
  // Font-awesome
  echo '<link href="https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" ' .
    'rel="stylesheet" integrity="sha384-wvfXpqpZZVQGK6TAh5PVlGOfQNHSoD2xbE+QkPxCAFlNEevoEH3Sl0sibVcOQVnN" crossorigin="anonymous">';
  
  // modalEffects support
  echo '<link type="text/css" media="all" rel="stylesheet" href="../css/modalEffect.css">';
  
  // Site specific styles
  echo '<link type="text/css" media="all" rel="stylesheet" href="../css/v4lStyle.css">';
  
  
  // sorttable.js (see: https://www.kryogenix.org/code/browser/sorttable/)
  echo '<script src="../js/sorttable.js"></script>';
  
  // ModalEffects support
  // Need to be at bottom of page?
  // echo '<script src="js/modalEffects.js"></script>';
  
  echo '<title>' . $string . '</title>';
  echo '</head>';
  
  if ($showBody) {
    echo '<body>';
    if (!(substr($_SERVER['REQUEST_URI'], -9) == 'index.php')) {
      echo '<p align="left"><a href="../index.php">Home</a>';
    }
    
    echo '<span style="float:right;">';
    if (isset($_SESSION['userID']) && $_SESSION['userID'] > 0) {
      echo 'Hi ' . $_SESSION['userName'] . '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="../profile.php">Profile</a>' .
        '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="login.php?action=logout">Logout</a></span></p>';
      
      if (!$_SESSION['isActive']) {
        echo '<div class="form-inline">';
        echo '<form action="login.php" method="post">';
        echo '<input type="hidden" name="loginStep" value="LOGIN_VERIFY_CODE" />';
        echo '<label for="verifyCode">Please Verify Your Account:&nbsp;</label>';
        echo '<input type="text" class="form-control" placeholder="Enter Verification Code" name="verifyCode" required>&nbsp;&nbsp;';
        echo '<button type="submit" class="btn btn-primary btn-xs">Verify</button>';
        echo '<button type="button" class="btn btn-info btn-xs" onclick="location.href=\'login.php?action=resend\'">Re-send Code</button>';
        echo '</form>';
        echo '</div>';
      }
    } else {
      echo 'Not logged in.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="login.php">Login</a></span></p>';
    }
    echo '<hr />';
    debugOut($_SESSION['lastURL'], $_SESSION['lastURL']);
    echo '<br />';
  }
}

function htmlEnd($showFooter = true)
{
  // Footer TBD.
  echo '</body></html>';
}

?>

