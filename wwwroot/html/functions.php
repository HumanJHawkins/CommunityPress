<?php
require '../lib/mailgun-php/vendor/autoload.php';

use Mailgun\Mailgun;

function mailgunSend($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML = null, $mailCC = null, $mailBCC = null, $mailAttachmentsArray = null)
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

    outputArray($sendArray);

    $mg = new Mailgun($GLOBALS['MAILGUN_API_KEY']);
    $mg->sendMessage($GLOBALS['MAILGUN_MAIL_DOMAIN'], $sendArray);
}


function handleDeleteAvatar($pdo, $existingAvatarPath, $existingAvatarID)
{
    $result = 0;
    if ($existingAvatarID > 0) {
        unlink(realpath($existingAvatarPath . strval($existingAvatarID)));

        $sql = 'uploadFileDelete(?, ?)';
        $sqlParamsArray = [$existingAvatarID, $_SESSION["userID"]];
        $result = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
        debugOut('Rows deleted', $result);
    }

    return $result;
}


function handleUploadAvatar($pdo, $contentRecordID, $existingAvatarPath, $existingAvatarID)
{
    /* -----------------------------------------------------------------------------
    -- Author       Jeff Hawkins
    -- Created      2017/11/04
    -- Purpose      Handle upload of content avatar graphic.
    -- Copyright © 2017, Jeff Hawkins.
    --
    -- RETURN VALUES:
    --   SUCCESS: uploadFileID of uploaded file.
    --   FAILURE:
    --     0 : No content to handle.
    --     -1 to -9 : See return values of handleUploadFile.
    --     -11: File exceeds configured limit for avatar files.
    --     -12: Image corrupt or malicious.
    --     -13: Image format not supported.
    --
    -- -----------------------------------------------------------------------------
    -- Modification History
    --
    -- 2017/11/03  Jeff Hawkins
    --      Initial version. Replaces separate functions for graphic and content
    --      files.
    -- ---------------------------------------------------------------------------*/
    if (isset($_FILES['contentFileGraphic']['size'])) {
        if ($_FILES['contentFileGraphic']['size'] > $GLOBALS['CONTENT_IMAGE_MAX_FILESIZE']) {
            return -11; // File exceeds configured limit for avatar files.
        }
    } else {
        return 0; // No content to handle.
    }

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
        if ($_FILES['contentFileGraphic']['type'] == 'image/png' || $_FILES['contentFileGraphic']['type'] == 'image/jpeg') {
            debugOut('*** Image corrupt or malicious ***');

            return -12; // Image corrupt or malicious.
        } else {
            debugOut('*** Image format not supported ***');

            return -13; // Image format not supported.
        }
    }

    handleDeleteAvatar($pdo, $existingAvatarPath, $existingAvatarID);
    $graphicFileID = handleUploadFile($pdo, 'contentFileGraphic', $GLOBALS['CONTENT_IMAGE_DIRECTORY']);

    // If we uploaded the graphic, tag the content with it.
    if ($graphicFileID > 0) {
        $sql = 'SELECT tagAttach(?, ?, ?)';
        $sqlParamsArray = [$contentRecordID, $graphicFileID, $_SESSION["userID"]];
        $contentGraphicRelationshipID = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);

        // And, tag the relationship between content and graphic to indicate this is the avatar graphic for this content.
        $sql = 'SELECT tagAttach(?, tagIDFromText(?), ?)';
        $sqlParamsArray = [$contentGraphicRelationshipID, 'ContentAvatar', $_SESSION["userID"]];

        // TO DO: Add error check here.
        getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
    }

    return $graphicFileID;
}


function handleUploadContent($pdo)
{
    /* -----------------------------------------------------------------------------
    -- Author       Jeff Hawkins
    -- Created      2017/11/04
    -- Purpose      Handle upload of content file. (Handle multiple not supported
    --              yet.
    -- Copyright © 2017, Jeff Hawkins.
    --
    -- RETURN VALUES:
    --   SUCCESS: uploadFileID of uploaded file.
    --   FAILURE:
    --     0 : No content to handle.
    --     -1 to -9 : See return values of handleUploadFile.
    --     -10: File exceeds configured limit for content files.
    --
    -- -----------------------------------------------------------------------------
    -- Modification History
    --
    -- 2017/11/03  Jeff Hawkins
    --      Initial version.
    -- ---------------------------------------------------------------------------*/
    if (isset($_FILES['contentFile']['size'])) {
        if ($_FILES['contentFile']['size'] > $GLOBALS['CONTENT_STORE_MAX_FILESIZE']) {
            return -10; // File exceeds configured limit for content files.
        }
    } else {
        return 0; // No content to handle.
    }

    return handleUploadFile($pdo, 'contentFile', $GLOBALS['CONTENT_STORE_DIRECTORY']);
}


function handleUploadFile($pdo, $theFormField, $theDestinationPath)
{
    /* -----------------------------------------------------------------------------
    -- Author       Jeff Hawkins
    -- Created      2017/11/04
    -- Purpose      Handle upload of user files.
    -- Copyright © 2017, Jeff Hawkins.
    --
    -- RETURN VALUES:
    --   SUCCESS: uploadFileID of uploaded file.
    --   FAILURE:
    --     0 : No file to handle.
    --     -1: HTTP file upload error. See debug log for specifics.
    --     -2: Creation of uploadFile record failed. uploadFileID is invalid or
    --         null. NOTE: Failed SQL is copied to debug output.
    --     -3: Failed to move uploaded file. Check permissions.
    --     Or: Return value of mysql uploadFileInsert (Could also be 0.)
    --
    -- -----------------------------------------------------------------------------
    -- Modification History
    --
    -- 2017/11/03  Jeff Hawkins
    --      Initial version. Replaces separate functions for graphic and content
    --      files.
    -- ---------------------------------------------------------------------------*/
    if (isset($_FILES[$theFormField]['name']) && $_FILES[$theFormField]['name'] != '') {
        if ($_FILES[$theFormField]['error'] == '' || $_FILES[$theFormField]['error'] == UPLOAD_ERR_OK) {
            debugOut('*** handleUploadFile *****************************************************************************');
            debugOut('tmp_name', $_FILES[$theFormField]['tmp_name']);
            debugOut('basename(name)', basename($_FILES[$theFormField]['name']));
            debugOut('size', $_FILES[$theFormField]['size']);
            debugOut('type', $_FILES[$theFormField]['type']);
            debugOut('$theDestinationPath', $theDestinationPath);
            debugOut('$_SESSION["userID"]', $_SESSION["userID"]);

            $sql = 'SELECT uploadFileInsert(?, ?, ?, ?, ?)';
            $sqlParamArray =
                [basename($_FILES[$theFormField]['name']), $_FILES[$theFormField]['size'], $_FILES[$theFormField]['type'], $theDestinationPath, $_SESSION["userID"]];
            debugOut('$sqlParamArray follows: ');
            outputArray($sqlParamArray);
            $uploadFileID = getOnePDOValue($pdo, $sql, $sqlParamArray, PDO::FETCH_NUM);
            debugOut('$uploadFileID', $uploadFileID);

            if ($uploadFileID > 0) {
                // Use of user's filename can create a security risk. Name by ID and restore on download.
                $theFilePathName = $theDestinationPath . strval($uploadFileID);
                debugOut('$theFilePathName', $theFilePathName);
                if (move_uploaded_file($_FILES[$theFormField]['tmp_name'], $theFilePathName)) {
                    return $uploadFileID; // It worked.
                } else {
                    debugOut('Failed to move uploaded file. Check permissions.');

                    return -3;
                }
            } else {
                debugOut('Creation of uploadFile record failed. uploadFileID is invalid or null.');
                $sql = 'SELECT uploadFileInsert(\'' . basename($_FILES[$theFormField]['name']) . '\', ' .
                    $_FILES[$theFormField]['size'] . ', \'' . $_FILES[$theFormField]['type'] . '\', \'' . $theDestinationPath .
                    '\', ' . $_SESSION["userID"] . ');';
                debugOut('The SQL was: ', $sql);

                return -2;
            }
        } else {
            debugOut('HTTP file upload error', $_FILES[$theFormField]['error']);

            return -1;
        }
    } else {
        // File upload is optional. Return if no file.
        debugOut('No file to handle.');

        return 0;
    }
}


function consolidatePageContentID()
{
    if ((isset($_GET["pageContentID"])) && ($_GET["pageContentID"] > 0)) {
        debugOut('$_GET["pageContentID"]', $_GET["pageContentID"]);
        $_POST["pageContentID"] = $_GET["pageContentID"];
    } elseif ((isset($_POST["pageContentID"]) && $_POST["pageContentID"] > 0)) {
        ; // Do nothing.
    } else {
        $_POST["pageContentID"] = null;
    }
    debugOut('$_POST["pageContentID"]', $_POST["pageContentID"]);
}


function tagCategorySelector($pdo)
{
    $sql = 'CALL procTagCategories()';
    $arrayParams = null;
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


function tagSelector($pdo, $tagCategoryID)
{
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


function getMySQLiConnection()
{
    // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
    try {
        $connection =
            mysqli_connect($GLOBALS['DB_SERVER'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_DATABASE'], $GLOBALS['DB_PORT']);
    } catch (connectException $e) {
        throw new Exception('"Could not connect to database."');
    }

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
    try {
        $pdo = new PDO($GLOBALS['DB_DSN'], $GLOBALS['DB_USERNAME'], $GLOBALS['DB_PASSWORD'], $GLOBALS['DB_OPTIONS']);
    } catch (PDOException $e) {
        // Catch default and throw new exception to prevent password exposure.
        throw new Exception('"Could not connect to database."');
    }
    return $pdo;
}


function getPDOResults($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH)
{
    debugOut('***************************************************************************************************');
    debugOut('***************************************************************************************************');
    debugOut('*** getPDOResults *********************************************************************************');
    debugOut('$sql', $sql);
    outputArray($sqlParamArray);
    $statement = $pdo->prepare($sql);
    try {
        $statement->execute($sqlParamArray);
    } catch (PDOException $exception) {
        debugOut("getPDOResults PDOException", $exception->getMessage());
        // This catch just to make the error visible via tail. Throw to make sure
        // it also receives normal handling.
        throw $exception;
    }
    do {
        $result[] = $statement->fetchAll($arrayType);
    } while ($statement->nextRowset());
    // Calling closeCursor() should not be necessary after fetch all from each Rowset.
    // It's possibly mildly counter-productive.
    // $statement->closeCursor();

    // Debug output the array...
    outputArray($result);

    return $result;
}


function getOnePDOTable($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH)
{
    $result = getPDOResults($pdo, $sql, $sqlParamArray, $arrayType);
    debugOut('Array returned via: getOnePDOTable as $result[0].');
    if ($result) {
        return $result[0];
    }
}


function getOnePDORow($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH)
{
    $result = getPDOResults($pdo, $sql, $sqlParamArray, $arrayType);
    debugOut('Array returned via: getOnePDORow as $result[0][0].');
    if ($result) {
        return $result[0][0];
    }
}


function getOnePDOValue($pdo, $sql, $sqlParamArray = null, $arrayType = PDO::FETCH_BOTH)
{
    $result = getPDOResults($pdo, $sql, $sqlParamArray, $arrayType);
    debugOut('Array returned via: getOnePDOValue as $result[0][0][0].');
    if (!empty($result)) {
        return $result[0][0][0];
    }
}


function getMySQLiResults($connection, $sql)
{
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


function getOneMySQLiTable($connection, $sql)
{
    // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
    return getMySQLiResults($connection, $sql)[0];
}


function getOneMySQLiRow($connection, $sql)
{
    // This and all other MySQLi use is deprecated in this repo. See PDO equivalents.
    return getMySQLiResults($connection, $sql)[0][0];
}


function sendEmail($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML = null, $mailCC = null, $mailBCC = null, $mailAttachmentsArray = null)
{
    // Abstracted here to allow easy switch to other mail services.
    // Write and use an AmazonSESSend() function for example, if needed.
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("From:", $mailFrom);
    debugOut("To:", $mailTo);
    debugOut("Subject:", $mailSubject);
    debugOut("Text:", $mailText);
    debugOut("HTML:", $mailHTML);
    debugOut("CC:", $mailCC);
    debugOut("BCC:", $mailBCC);
    debugOut("AttachmentsArray:", $mailAttachmentsArray);
    outputArray($mailAttachmentsArray);
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");
    debugOut("***********************--------------------------*************************-------------------------");

    mailgunSend($mailFrom, $mailTo, $mailSubject, $mailText, $mailHTML, $mailCC, $mailBCC, $mailAttachmentsArray);
}


function outputArray($theArray, $echo = false, array $arrayBreadcrumbs = null, $showRowCount = true, $debugLevel = 1)
{
    // Filter output based on config debug level.
    if ($GLOBALS['DEBUG_FILTER'] >= $debugLevel) {
        return;
    }

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
                debugOut($prefix . $key, 'Object (row count: ' . count($val) . ')', $echo, true, true, $debugLevel);
            } elseif (is_array($val)) {
                $arrayBreadcrumbs[] = $key;
                debugOut($prefix . $key, 'Array  (row count: ' . count($val) . ')', $echo, true, true, $debugLevel);
            } else {
                // Avoid output of DB_PASSWORD, etc.
                if (strpos(strtolower($key), 'password') !== false) {
                    $val = '*******************';
                }
                debugOut($prefix . $key, $val, $echo, true, true, $debugLevel);
            }
            if (is_array($val)) {
                if ($theArray == $val) { // Arrays can contain references to themselves. Prevent endless recursion
                    debugOut($prefix .
                        $key, 'Not shown. Recursing this would create an infinite loop', $echo, true, true, $debugLevel);
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


function debugPrefix()
{
    $debugPrefix = "Document URI (Temp workaround)"; // ltrim($_SERVER['DOCUMENT_URI'], '/');
    // Temp workaround for what?
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


function debugOut($heading = '', $detail = '', $echo = false, $prefix = true, $timestamp = true, $debugLevel = 1)
{
    // Filter output based on config debug level.
    if ($GLOBALS['DEBUG_FILTER'] >= $debugLevel) {
        return;
    }

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
        $heading = $heading . ' = ' . $detail;
    }
    if ($echo) {
        echo '<br />' . $heading;
    }
    error_log($heading . PHP_EOL, 3, $GLOBALS['LOG_FILE_PATH']);
}


function debugSectionOut($sectionTitle, $debugLevel = 1)
{
    // Filter output based on config debug level.
    if ($GLOBALS['DEBUG_FILTER'] >= $debugLevel) {
        return;
    }

    debugOut('', '', false, false, false, $debugLevel);
    debugOut('***** ' . $sectionTitle . ':', '', false, false, false, $debugLevel);
    debugOut('*****   $_SESSION:', '', false, false, false, $debugLevel);
    outputArray($_SESSION, false, null, true, $debugLevel);
    debugOut('*****   $_POST:', '', false, false, false, $debugLevel);
    outputArray($_POST, false, null, true, $debugLevel);
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
    $_SESSION = [];
    // Delete session cookie.
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() -
            42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
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
    if (filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_CLIENT_IP']; elseif (filter_var(@$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_X_FORWARDED_FOR'];
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


function bytesToMegabytes($bytes)
{
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


function htmlStart($string, $showBody = true)
{
    include 'divHTMLHead.php';
    if ($showBody) {
        include 'divHTMLBodyTop.php';
    }
}


function htmlEnd($showFooter = true)
{
        // Footer TBD.
    echo '</body></html>';
}


function loginDisplayLoginDialog()
{
    // Display the login dialog with password text conditional on error status.
    $placeholder = 'Enter Password';
    if (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_DIALOG_PASSWORD_INCORRECT)) {
        $placeholder = 'Password Invalid. Please try again.';
    }
    $dlgHTML = file_get_contents('loginDlgMain.html');
    $dlgHTML = str_replace("STRING_REPLACE_LAST_URL", $_SESSION['lastURL'], $dlgHTML);
    $dlgHTML = str_replace("STRING_REPLACE_PLACEHOLDER", $placeholder, $dlgHTML);

    htmlStart('Login or Register', false);
    echo $dlgHTML;
}


function loginDisplayVerifyDialog()
{
    // Display the verification dialog with verification code text conditional on error status.
    $placeholder = 'Enter Verification Code';
    if (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_VERIFY_DIALOG_CODE_INCORRECT)) {
        $placeholder = 'Verification Code Invalid. Please try again.';
    }
    $dlgHTML = file_get_contents('loginDlgVerify.html');
    $dlgHTML = str_replace("STRING_REPLACE_EMAIL", $_SESSION['userEmail'], $dlgHTML);
    $dlgHTML = str_replace("STRING_REPLACE_LAST_URL", $_SESSION['lastURL'], $dlgHTML);
    $dlgHTML = str_replace("STRING_REPLACE_PLACEHOLDER", $placeholder, $dlgHTML);

    htmlStart('Confirm Account', false);
    echo $dlgHTML;
}


function resetLoginProcess($errorText = '')
{
    destroySession();
    if ($errorText != '') {
        debugOut('ERROR: ' . $errorText);
    }

    // NOTE: Technically some cases might be LOGIN_DIALOG_PASSWORD_INCORRECT.
    //  However, we shouldn't get here without an error, so that would be a false message more often than not.
    $_SESSION["loginStep"] = LOGIN_DIALOG_STANDARD;
    returnToLogin();
    exit();
}


function getVerifyCodeEmailHTML($verifyCode, $htmlFile)
{
    $dlgHTML = file_get_contents($htmlFile);

    $dlgHTML = str_replace("PLACEHOLDER_SALUTATION", $_SESSION["userEmail"], $dlgHTML);
    $dlgHTML = str_replace("PLACEHOLDER_SITENAME_CASUAL", $GLOBALS["SITE_URL_CASUAL"], $dlgHTML);
    $dlgHTML = str_replace("PLACEHOLDER_CONFIRM_CODE", $verifyCode, $dlgHTML);
    $dlgHTML =
        str_replace("PLACEHOLDER_VERIFICATION_EMAIL_SIGNATURE", $GLOBALS["VERIFICATION_EMAIL_SIGNATURE"], $dlgHTML);

    return $dlgHTML;
}


function sendVerifyCode()
{
    debugOut('Creating and sending verification code.');
    $verifyCode = verifyCode();
    $_SESSION["verifyCodeHash"] =
        password_hash($verifyCode, PASSWORD_DEFAULT, ["cost" => $GLOBALS['VERIFYCODE_HASH_COST']]);

    // Creating both text and THML versions of the verification email, to support various browsers.

    // Must use doublequotes below, or PHP will not translate the linefeeds.
    $mailBodyText = "Dear " . $_SESSION["userEmail"] . ",\r\n\r\nThank you for registering at " .
        $GLOBALS["SITE_URL_CASUAL"] . ". We hope you will find many great educational resources, and " .
        "perhaps contribute some of your own.\r\n\r\n" .
        "Please verify your email by entering the following code where it says \"Enter Code Here: \" at " .
        $GLOBALS["SITE_URL_CASUAL"] . ": " . $verifyCode . "\r\n\r\n" .
        "See you at the site.\r\n\r\n" . $GLOBALS["VERIFICATION_EMAIL_SIGNATURE"];

    $mailBodyHTML = getVerifyCodeEmailHTML($verifyCode, 'verifyCodeEmail.html');

    // Using the following possibly better, but would require a redesign.rethink. So commented out for now.
    // $mailBodyText = file_get_contents('verifyCodeEmailText.text');

    // Uses Mailgun. Mailgun object created in config to keep security and access keys in the same file.
    //
    // TO DO:
    //   Add text message option.
    //   Switch to Amazon's mail solution?

    sendEmail($GLOBALS['VERIFICATION_EMAIL_FROM'], $_SESSION['userEmail'], $GLOBALS['VERIFICATION_EMAIL_SUBJECT'], $mailBodyText, $mailBodyHTML);
}


function returnToLogin()
{
    debugOut('function returnToLogin(), DOCUMENT_URI', "Document URI (Temp workaround)");  // $_SERVER['DOCUMENT_URI']);
    // Again, workaround for what? How temporary?
    header('Location: ' . $GLOBALS['SITE_URL'] . '/login.php');
}


function updateUserSession($pdo)
{
    // $pdo        = getDBPDO();
    $theUserID = 0;
    if ((isset($_SESSION["userID"])) && $_SESSION["userID"] > 1) {
        $theUserID = $_SESSION["userID"];
    }

    $tempPass = '';
    if (isset($_SESSION["password"])) {
        $tempPass = $_SESSION["password"];
        $_SESSION["password"] = '';
        unset($_SESSION["password"]);
    }

    $sql = 'SELECT addOrUpdateUser(
    \'' . $_SESSION["userEmail"] . '\', \'' . $_SESSION['saltHash'] . '\', \'' . session_id() . '\', \'' . ipAddress() .
        '\', \'' . session_encode() . '\',' . $theUserID . ')';
    debugOut($sql);

    if ($tempPass !== '') {
        $_SESSION["password"] = $tempPass;
        $tempPass = '';
        unset($tempPass);
    }

    return getOnePDORow($pdo, $sql);
}


function registerUser($pdo)
{
    $_SESSION['saltHash'] =
        password_hash($_POST["password"], PASSWORD_DEFAULT, ["cost" => $GLOBALS['PASSWORD_HASH_COST']]);
    debugOut('$_SESSION[\'saltHash\']', $_SESSION['saltHash']);

    $row = updateUserSession($pdo);
    if ($row) {
        // Temporarily save the password for a reload.
        $_SESSION["password"] = $_POST["password"];
        $_POST["password"] = '';
        unset($_POST["password"]);
    } else {
        debugOut('registerUser', 'No result returned. Handle error.');
    }
}


function getLastURL()
{
    return $_SESSION['lastURL'];
}


function setLastURL()
{
    $fileExtension = pathinfo($_SERVER['REQUEST_URI'], PATHINFO_EXTENSION);
    if ($fileExtension == '' || $fileExtension == 'ico' || $fileExtension == 'css' || $fileExtension == 'js' ||
        strpos($_SERVER['REQUEST_URI'], 'icon/') !== false || strpos($_SERVER['REQUEST_URI'], 'login.php') !== false ||
        strpos($_SERVER['REQUEST_URI'], 'logout.php') !== false
    ) {
        ; // Do nothing
    } else {
        $_SESSION['lastURL'] = $_SERVER['REQUEST_URI'];
    }
}




