<?php
include 'sessionStart.php';

// Remember where we need to go after killing the session
$url = $_SESSION['LastRequestedURL'];
echo '<br>URL: '.$url;
// echo '<br>URL: '.$_SERVER['REQUEST_URI'];

// Unset all of the session variables.
$_SESSION = array();

// If it's desired to kill the session, also delete the session cookie.
// Note: This will destroy the session, and not just the session data!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finally, destroy the session.
session_destroy();

// Back to wherever the user was, but logged out.
header('Location: ' . $url);
exit();
?>