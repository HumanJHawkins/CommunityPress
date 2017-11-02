<?php
require './mailgun-php/vendor/autoload.php';

use Mailgun\Mailgun;

function mailgunSend($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML = null, $mailCC = null, $mailBCC = null, $mailAttachmentsArray = null) {
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

function fileUploadGraphic($pdo) {
  // Is this needed here?
  // header('Content-Type: text/plain; charset=utf-8');

  try {
    // Consider rejecting cases where multiple files are in the graphic section of the upload.

    switch ($_FILES['contentFileGraphic']['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        throw new RuntimeException('Error: UPLOAD_ERR_NO_FILE');
      case UPLOAD_ERR_INI_SIZE:
        throw new RuntimeException('Error: UPLOAD_ERR_INI_SIZE');
      case UPLOAD_ERR_FORM_SIZE:
        throw new RuntimeException('Error: UPLOAD_ERR_FORM_SIZE (File exceeds allowed size.)');
      default:
        throw new RuntimeException('Unknown error in $_FILES["file_upload"]["error"]');
    }

    // TO DO: Also check filesize here.
    // if ($_FILES[contentFileGraphic]['size'] > 1000000) {
    //   throw new RuntimeException('Exceeded filesize limit.');
    // }

    // Confirm MIME type.
    $imageTypeConfirmed = false;
    if ($_FILES['contentFileGraphic']['type'] == 'image/png') {
      if (imagecreatefrompng($_FILES['contentFileGraphic']['tmp_name'])) {
        $imageTypeConfirmed = true;
      }
    } else if ($_FILES['contentFileGraphic']['type'] == 'image/jpeg') {
      if (imagecreatefromjpeg($_FILES['contentFileGraphic']['tmp_name'])) {
        $imageTypeConfirmed = true;
      }
    }

    if (!$imageTypeConfirmed) {
      if ($_FILES['contentFileGraphic']['type'] == 'image/png' ||
          $_FILES['contentFileGraphic']['type'] == 'image/jpeg'
      ) {
        debugOut('*** Image corrupt or malicious ***');
      } else {
        debugOut('*** Image format not supported ***');
      }
    }

    // Reconstruct the name using basename, to prevent potential injection-type attack.
    $theFileName = basename($_FILES['contentFileGraphic']['name']);
    $theFileSize = $_FILES['contentFileGraphic']['size'];
    $theMimeType = $_FILES['contentFileGraphic']['type'];
    $theFilePath = $GLOBALS['CONTENT_IMAGE_DIRECTORY'];
    $theUser = $_SESSION['userID'];

    $sql = 'SELECT uploadFileInsert(?, ?, ?, ?, ?)';
    $sqlParamArray = [$theFileName, $theFileSize, $theMimeType, $theFilePath, $theUser];
    $uploadFileID = getOnePDOValue($pdo, $sql, $sqlParamArray, PDO::FETCH_NUM);
    debugOut('$uploadFileID', $uploadFileID);

    $theFilePathName = $theFilePath . strval($uploadFileID);
    debugOut('$theFilePathName', $theFilePathName);

    // Use of user's filename can create a security risk. Name by ID and restore on download.
    if (move_uploaded_file($_FILES['contentFileGraphic']['tmp_name'], $theFilePathName)) {
      ; // It worked.
    } else {
      throw new RuntimeException('Failed to move uploaded file.');
    }

    return $uploadFileID;
  } catch (RuntimeException $e) {
    debugOut('*** Graphic upload exception ********************************************************************************');
    debugOut($e->getMessage());
    debugOut();
    echo $e->getMessage();
  }
}

function fileUploadContent($pdo) {
  try {
    // Consider rejecting cases where multiple files are in the graphic section of the upload.

    switch ($_FILES['contentFile']['error']) {
      case UPLOAD_ERR_OK:
        break;
      case UPLOAD_ERR_NO_FILE:
        throw new RuntimeException('Error: UPLOAD_ERR_NO_FILE');
      case UPLOAD_ERR_INI_SIZE:
        throw new RuntimeException('Error: UPLOAD_ERR_INI_SIZE');
      case UPLOAD_ERR_FORM_SIZE:
        throw new RuntimeException('Error: UPLOAD_ERR_FORM_SIZE (File exceeds allowed size.)');
      default:
        throw new RuntimeException('Unknown error in $_FILES["file_upload"]["error"]');
    }

    // TO DO: Also check filesize here.
    // if ($_FILES[contentFile]['size'] > 1000000) {
    //   throw new RuntimeException('Exceeded filesize limit.');
    // }

    // Confirm MIME type.
    // TO DO: Need to identify which formats we support and add a filter to block the rest.

    // Reconstruct the name using basename, to prevent potential injection-type attack.
    $theFileName = basename($_FILES['contentFile']['name']);
    $theFileSize = $_FILES['contentFile']['size'];
    $theMimeType = $_FILES['contentFile']['type'];
    $theFilePath = $GLOBALS['CONTENT_STORE_DIRECTORY'];
    $theUser = $_SESSION['userID'];

    $sql = 'SELECT uploadFileInsert(?, ?, ?, ?, ?)';
    $sqlParamArray = [$theFileName, $theFileSize, $theMimeType, $theFilePath, $theUser];
    $uploadFileID = getOnePDOValue($pdo, $sql, $sqlParamArray, PDO::FETCH_NUM);
    debugOut('$uploadFileID', $uploadFileID);

    $theFilePathName = $theFilePath . strval($uploadFileID);
    debugOut('$theFilePathName', $theFilePathName);

    // Use of user's filename can create a security risk. Name by ID and restore on download.
    if (move_uploaded_file($_FILES['contentFile']['tmp_name'], $theFilePathName)) {
      ; // It worked.
    } else {
      throw new RuntimeException('Failed to move uploaded file.');
    }

    return $uploadFileID;
  } catch (RuntimeException $e) {
    debugOut('*** Graphic upload exception ********************************************************************************');
    debugOut($e->getMessage());
    debugOut();
    echo $e->getMessage();
  }
  /*
  if ($_FILES['file_upload']['error'] == UPLOAD_ERR_OK) {

      // $uploadfile = $GLOBALS['CONTENT_STORE_DIRECTORY'] . basename($_FILES['userUpload']['name']);
      // $path = $_FILES['image']['name'];
      // $ext = pathinfo($path, PATHINFO_EXTENSION);
      $uploadfile = $GLOBALS['CONTENT_STORE_DIRECTORY'] . $newID . '.' .
          pathinfo($_FILES['userUpload']['name'], PATHINFO_EXTENSION);
      if (move_uploaded_file($_FILES['userUpload']['tmp_name'], $uploadfile)) {
        ;  // Success. Do nothing here.
      } else {
        echo '<pre><br />File upload error. File array dump follows. <br />';
        outputArray($_FILES, true);
        echo "<script>alert('Upload error. Press OK to return to page.')</script>";
      }
    } else {
      return $_FILES['file_upload']['error'];
    }
  }
  */
}


function consolidatePageContentID() {
  if ((isset($_GET["pageContentID"])) && ($_GET["pageContentID"] > 0)) {
    debugOut('$_GET["pageContentID"]', $_GET["pageContentID"]);
    $_POST["pageContentID"] = $_GET["pageContentID"];
  } elseif ((isset($_POST["pageContentID"]) && $_POST["pageContentID"] > 0)) {
    ; // Do nothing.
  } else {
    $_POST["pageContentID"] = 0;
  }
  debugOut('$_POST["pageContentID"]', $_POST["pageContentID"]);
}


function tagCategorySelector($pdo) {
  $sql = 'SELECT DISTINCT tagCategoryID, tagCategory FROM vTag';
  $result = getOnePDOTable($pdo, $sql, null, PDO::FETCH_ASSOC);
  echo '<select name="tagCategoryIDSelector" id="tagCategoryIDSelector">';
  echo '<option value="0">Select Category...</option>';
  foreach ($result as $key => $value) {
    echo '<option ';
    if (isset($_POST["tagCategory"]) && $_POST["tagCategory"] == $value["tagCategory"]) {
      echo 'selected="selected" ';
    }
    echo 'value="' . $value['tagCategoryID'] . '">' . $value['tagCategory'] . '</option>';
  }
  echo '</select>';
}


function tagSelector($pdo, $tagCategoryID) {
  $sql = 'SELECT DISTINCT tagID, tag FROM vTag';
  if ($tagCategoryID) {
    $sql = $sql . ' WHERE tagCategoryID = ' . $tagCategoryID;
  }
  debugOut("**************************************************************** tagSelector");
  debugOut("$sql", $sql);
  $result = getOnePDOTable($pdo, $sql, null, PDO::FETCH_ASSOC);
  echo '<select name="tagSelect" id="tagSelect">';
  foreach ($result as $key => $value) {
    echo '<option ';
    if (isset($_POST["tag"]) && $_POST["tag"] == $value['tag']) {
      echo 'selected="selected" ';
    }
    echo 'value="' . $value['tagID'] . '">' . $value['tag'] . '</option>';
  }
  echo '</select>';
}


function getMySQLiConnection() {
  // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
  $connection =
      mysqli_connect($GLOBALS['DB_SERVER'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_DATABASE'], $GLOBALS['DB_PORT']);
  if (!$connection) {
    echo "Error: Unable to connect to MySQL.<br />";
    echo "Debugging errno: " . mysqli_connect_errno() . '<br />';
    echo "Debugging error: " . mysqli_connect_error() . '<br />';

    return null;
  }

  return $connection;
}


function getDBPDO() {
  $pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_OPTIONS']);

  return $pdo;
}


function getPDOResults($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH) {
  $statement = $pdo->prepare($sql);
  try {
    $statement->execute($sqlParamArray);
  } catch (PDOException $exception) {
    debugOut("PDOException", $exception->getMessage());
    // This catch just to make the error visible via tail. Throw to make sure
    // it also receives normal handling.
    throw $exception;
  }
  do {
    $resultSet[] = $statement->fetchAll($arrayType);
  } while ($statement->nextRowset());
  // Calling closeCursor() should not be necessary after fetch all from each Rowset.
  // It's possibly mildly counter-productive.
  // $statement->closeCursor();
  return $resultSet;
}


function getOnePDOTable($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH) {
  return getPDOResults($pdo, $sql, $sqlParamArray, $arrayType)[0];
}


function getOnePDORow($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH) {
  return getPDOResults($pdo, $sql, $sqlParamArray, $arrayType)[0][0];
}


function getOnePDOValue($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH) {
  return getPDOResults($pdo, $sql, $sqlParamArray, $arrayType)[0][0][0];
}


function getMySQLiResults($connection, $sql) {
  // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
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
  } while ($connection->more_results() && $connection->next_result());
  debugOut("getMySQLiResults() Array Dump:");
  outputArray($resultSet);

  return $resultSet;
}


function getOneMySQLiTable($connection, $sql) {
  // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
  return getMySQLiResults($connection, $sql)[0];
}


function getOneMySQLiRow($connection, $sql) {
  // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
  return getMySQLiResults($connection, $sql)[0][0];
}


function sendEmail($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML = null, $mailCC = null, $mailBCC = null, $mailAttachmentsArray = null) {
  // Abstracted here to allow easy switch to other mail services.
  // Write and use an AmazonSESSend() function for example, if needed.
  mailgunSend($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML, $mailCC, $mailBCC, $mailAttachmentsArray);
}


function outputArray($theArray, $echo = false, array $arrayBreadcrumbs = null, $showRowCount = true) {
  // Will this make the world explode? Let's see...
  // ksort($GLOBALS);
  $rowCount = 0;
  $prefix = '';
  if (is_array($theArray)) {
    foreach ($theArray as $key => $val) {
      $rowCount++;
      if ($arrayBreadcrumbs) {
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
      if ($arrayBreadcrumbs) {
        array_pop($arrayBreadcrumbs);
      }
    }
  }

  return $rowCount;
}


function debugPrefix() {
  $debugPrefix = ltrim($_SERVER['DOCUMENT_URI'], '/');
  if (isset($_SESSION['loginStep'])) {
    $debugPrefix = $debugPrefix . ':' . $_SESSION['loginStep'];
  }

  return $debugPrefix;
}


function debugTimestamp() {
  $time = new DateTime();

  return $time->format('YmdHis');
}


function debugOut($heading = '', $detail = '', $echo = false, $prefix = true, $timestamp = true) {
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


function debugSectionOut($sectionTitle) {
  debugOut('', '', false, false, false);
  debugOut('***** ' . $sectionTitle . ':');
  debugOut('*****   $_SESSION:');
  outputArray($_SESSION);
  debugOut('*****   $_POST:');
  outputArray($_POST);
}


function logout() {
  destroySession();
  header('Location: ' . $GLOBALS['SITE_URL']);
  exit();
}


function destroySession() {
  // Unset all of the session variables.
  $_SESSION = [];
  // Delete session cookie.
  if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() -
        42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
  }
  session_destroy();
}


function getSaltHashTimeCost($iterations) {
  $timeStart = round(microtime(true) * 1000);
  password_hash('TestPassword', PASSWORD_DEFAULT, ["cost" => $iterations]);
  $timeEnd = round(microtime(true) * 1000);

  return $timeEnd - $timeStart;
}


function ipAddress() {
  if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_CLIENT_IP']; elseif (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_X_FORWARDED_FOR'];
  else return $_SERVER['REMOTE_ADDR'];
}


// Adapted from Stephen Watkins answer at https://stackoverflow.com/questions/4356289/php-random-string-generator
function verifyCode($length = 4) {
  $charSet = '23456789ABCDEFGHJKLMNPQRSTUVWXYZ'; // Don't use ambiguous characters (O vs. 0, etc.)
  $charSetLen = strlen($charSet);
  $code = '';
  for ($i = 0; $i < $length; $i++) {
    $code .= $charSet[rand(0, $charSetLen - 1)];
  }

  return $code;
}


function bytesToMegabytes($bytes) {
  $megabytes = $bytes / 1048576;
  if ($megabytes <= .001) {
    $megabytes = .001;
  } else if ($megabytes <= .01) {
    $megabytes = round($megabytes, 3);
  } else if ($megabytes <= .1) {
    $megabytes = round($megabytes, 2);
  } else if ($megabytes <= 1) {
    $megabytes = round($megabytes, 1);
  } else if ($megabytes < 10) {
    $megabytes = round($megabytes, 0);
  }

  return $megabytes;
}

function htmlStart($string, $showBody = true) {
  include 'divHTMLHead.php';
  if ($showBody) {
    include 'divHTMLBodyTop.php';
  }
}


function htmlEnd($showFooter = true) {
  // Footer TBD.
  echo '</body></html>';
}


?>

