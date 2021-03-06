<?php
include 'sessionStart.php';
if (!isset($pdo)) {
    $pdo = getDBPDO();
}

const LOGIN_DIALOG_STANDARD = 'LOGIN_DIALOG_STANDARD';
const LOGIN_DIALOG_PASSWORD_INCORRECT = 'LOGIN_DIALOG_PASSWORD_INCORRECT';
const LOGIN_VERIFY_PASSWORD = 'LOGIN_VERIFY_PASSWORD';
const LOGIN_REGISTER_USER = 'LOGIN_REGISTER_USER';
const LOGIN_VERIFY_SEND_CODE = 'LOGIN_VERIFY_SEND_CODE';
const LOGIN_VERIFY_DIALOG_STANDARD = 'LOGIN_VERIFY_DIALOG_STANDARD';
const LOGIN_VERIFY_DIALOG_CODE_INCORRECT = 'LOGIN_VERIFY_DIALOG_CODE_INCORRECT';
const LOGIN_VERIFY_CODE = 'LOGIN_VERIFY_CODE';
const LOGIN_PASSWORD_RESET = 'LOGIN_PASSWORD_RESET';
const LOGIN_LOGOUT = 'LOGIN_LOGOUT';


$dlgHTML = '';
$sql = '';
$debugMsg = '';
$_SESSION["isConfirmed"] = false;

// Use $_POST consistently for passwords because because POST is
//   unset automatically if we forget.
if ((isset($_SESSION["password"])) && ($_SESSION["password"] != '')) {
    $_POST["password"] = $_SESSION["password"];
    $_SESSION["password"] = '';
    unset($_SESSION["password"]);
}
// Use $_SESSION consistently for other variables.
if ((isset($_POST["userEmail"])) && ($_POST["userEmail"] != '')) {
    $_SESSION["userEmail"] = $_POST["userEmail"];
}
if ((isset($_POST["loginStep"])) && ($_POST["loginStep"] != '')) {
    $_SESSION["loginStep"] = $_POST["loginStep"];
} else {
    // $_SESSION["loginStep"] = '';
}

// Allow $_GET to override $_POST from loginDlgVerify, etc. Allows handling of
//  various related functionality.
if (isset($_GET["action"])) {
    if ($_GET["action"] == 'resend') {
        $_SESSION["loginStep"] = 'LOGIN_VERIFY_SEND_CODE';
    }
    if ($_GET["action"] == 'reset') {
        $_SESSION["loginStep"] = 'LOGIN_PASSWORD_RESET';
    }
    if ($_GET["action"] == 'logout') {
        $_SESSION["loginStep"] = 'LOGIN_LOGOUT';
    }
}

if (
    (!isset($_SESSION["loginStep"]))
    || ($_SESSION["loginStep"] == LOGIN_DIALOG_STANDARD)
    || ($_SESSION["loginStep"] == LOGIN_DIALOG_PASSWORD_INCORRECT)
) {
    debugSectionOut('LOGIN_DIALOG_STANDARD || LOGIN_DIALOG_PASSWORD_INCORRECT');
    loginDisplayLoginDialog();
} elseif ($_SESSION["loginStep"] == LOGIN_VERIFY_PASSWORD) {
    debugSectionOut('LOGIN_VERIFY_PASSWORD');

    // If we get here without an email and password, it's an error. Log and revert to
    //  initial login.
    if (
        (!((isset($_SESSION["userEmail"])) && (trim($_SESSION["userEmail"]) != ''))) ||
        (!((isset($_POST["password"])) && (trim($_POST["password"]) != '')))
    ) {
        resetLoginProcess('At LOGIN_VERIFY_PASSWORD, without username and password. Returning to login screen.');
    }

    $sql = 'CALL procGetUserForLogin(\'' . trim($_SESSION["userEmail"]) . '\')';
    $row = getOnePDORow($pdo, $sql);

    if (!empty($row)) {
        if (password_verify($_POST["password"], $row['saltHash'])) {
            // Don't hold passwords any longer than you have to.
            $_POST["password"] = '';
            unset($_POST["password"]);
            debugOut('Password verified.');

            // User data should be stored in the $_SESSION array.
            // TO DO: Migrate more (all?) data to $_SESSION, so this is all that is necessary.
            session_decode($row['sessionData']);
            if (isset($_SESSION['password'])) {
                $_SESSION["password"] = '';
                unset($_SESSION["password"]);
            }
            unset($row['sessionData']);

            // Get everything not in $_SESSION. This should not be necessary.
            foreach ($row as $key => $val) {
                $_SESSION[$key] = $val;
            }

            // We just loaded the $_SESSION, so this really really should not be necessary.
            updateUserSession($pdo);

            if ($_SESSION['isConfirmed']) {
                if (isset($_SESSION["loginStep"])) {
                    unset($_SESSION["loginStep"]);
                }
                header('Location: ' . $_SESSION['lastURL']);
                exit();
            } else {
                debugOut('Password verified, but user unconfirmed).');
                $_SESSION["loginStep"] = LOGIN_VERIFY_SEND_CODE;
                returnToLogin();
                exit();
            }
        } else {
            // Don't hold passwords any longer than you have to.
            // (Even incorrect passwords could help a hacker guess the correct one.)
            $_POST["password"] = '';
            unset($_POST["password"]);

            debugOut('Password didn\'t match.');
            $_SESSION["loginStep"] = LOGIN_DIALOG_PASSWORD_INCORRECT;
            returnToLogin();
            exit();
        }
    } else {
        // This means the user is not in our system at all...
        debugOut("Email not in database.", '', true);

        // Temporarily save the password for a reload.
        $_SESSION['password'] = $_POST['password'];
        $_POST['password'] = '';
        unset($_POST['password']);

        debugOut('Saving user to database.');
        $_SESSION["loginStep"] = LOGIN_REGISTER_USER;
        returnToLogin();
        exit();
    }
} elseif (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_REGISTER_USER)) {
    debugSectionOut('LOGIN_REGISTER_USER');
    registerUser($pdo);
    $_SESSION["loginStep"] = LOGIN_VERIFY_PASSWORD; // Need to run through LOGIN_VERIFY_PASSWORD to load data for new user.
    returnToLogin();
    exit();
} elseif (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_VERIFY_SEND_CODE)) {
    debugSectionOut('LOGIN_VERIFY_SEND_CODE');

    // If we got here without email, it is an error. Revert to login.
    if (!(isset($_SESSION["userEmail"]) && (trim($_SESSION["userEmail"]) != ''))) {
        resetLoginProcess('Cannot send code without email.');
    }

    sendVerifyCode();

    $_SESSION["loginStep"] = LOGIN_VERIFY_DIALOG_STANDARD;
    returnToLogin();
    exit();
} elseif (isset($_SESSION["loginStep"]) &&
    (($_SESSION["loginStep"] == LOGIN_VERIFY_DIALOG_STANDARD) || ($_SESSION["loginStep"] == LOGIN_VERIFY_DIALOG_CODE_INCORRECT))
) {
    debugSectionOut('LOGIN_VERIFY_DIALOG_STANDARD || LOGIN_VERIFY_DIALOG_CODE_INCORRECT');
    loginDisplayVerifyDialog();
} elseif (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_VERIFY_CODE)) {
    debugSectionOut('LOGIN_VERIFY_CODE');
    if ((isset($_POST["verifyCode"])) && ($_POST["verifyCode"] != '')) {
        debugOut("In verifyCode branch.");
        if (password_verify($_POST["verifyCode"], $_SESSION["verifyCodeHash"])) {

            // verifyCode is effectively a temp password. Clear when no longer needed..
            $_POST["verifyCode"] = '';
            unset($_POST["verifyCode"]);

            debugOut('Email Verified.');

            // Confirm the user
            $sql = 'SELECT tagAttach(?, ?, ?)';
            $sqlParamsArray = [$_SESSION["userID"], $_SESSION["tagConfirmedID"], $_SESSION["userID"]];
            $result = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);

            $_SESSION['isConfirmed'] = true;

            // Update session
            // TO DO: This shouldn't be necessary... May not be already. Whether user is confirmed or not should be
            //  purely a tag thing.
            //      updateUserSession($pdo);
            //
            //      $sql = 'SELECT tagAttach(?, ?, ?)';
            //      $sqlParamsArray = [$_SESSION["userID"], $_SESSION["tagConfirmedID"], $_SESSION["userID"]];
            //      $result = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);

            header('Location: ' . $_SESSION['lastURL']);
            exit();
        } else {
            // Clear invalid code... Same as invalid passwords.
            $_POST["verifyCode"] = '';
            unset($_POST["verifyCode"]);

            debugOut('Verification code didn\'t match.');
            $_SESSION["loginStep"] = LOGIN_VERIFY_DIALOG_CODE_INCORRECT;
            returnToLogin();
            exit();
        }
    } else {
        debugOut("ERROR: Reached LOGIN_VERIFY_CODE, but have no code to verify.", '', true);
    }
} elseif (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_PASSWORD_RESET)) {
    ;

} elseif (isset($_SESSION["loginStep"]) && ($_SESSION["loginStep"] == LOGIN_LOGOUT)) {
    logout();
    exit(0);
} else {
    // We don't have e-mail. So have to revert to default
    $_SESSION["loginStep"] = LOGIN_DIALOG_STANDARD;
    returnToLogin();
    exit();
}

htmlEnd(false);

?>


