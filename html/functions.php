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


  function tagCategorySelector($pdo) {
    $sql = 'SELECT DISTINCT tagCategoryID, tagCategory FROM vTag';
    $result = getOnePDOTable($pdo, $sql);
    echo '<select name="tagCatSelect" id="tagCatSelect">';
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
    $result = getOnePDOTable($pdo, $sql);
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
    $connection = mysqli_connect($GLOBALS['DB_SERVER'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_DATABASE'], $GLOBALS['DB_PORT']);
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
      setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
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
