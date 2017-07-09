<?php
function ipAddress()
{
    if(filter_var(@$_SERVER['HTTP_CLIENT_IP'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_CLIENT_IP'];
    elseif(filter_var( @$_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    else return $_SERVER['REMOTE_ADDR'];
}

function htmlStart($string, $bShowBody = true) {
    echo '<!DOCTYPE html><html lang="en"><head><meta charset="utf-8">'.
        '<link type="text/css" media="all" rel="stylesheet" href="v4lStyle.css">';

    // TO DO: Add local fallback to Bootstrap CDN as described here:
    //   https://stackoverflow.com/questions/26192897/should-i-use-bootstrap-from-cdn-or-make-a-copy-on-my-server

    // Bootstrap 3.3.7 via CDN
    echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"' .
        ' integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">'.
        '<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"'.
        ' integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>';
    // <!-- Optional Bootstrap theme -->
    // echo '<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"'.
    //    ' integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">';

    // jQuery 3
    echo '<script src="https://code.jquery.com/jquery-3.2.1.min.js"'.
        ' integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>';

    echo '<title>'.$string.'</title></head>';

    if($bShowBody) {
        echo '<body><p align="left">&nbsp;&nbsp;<a href="index.php">Home</a><span style="float:right;">';
        if($_SESSION['UserID'] > 0) {
            echo 'Hi ' . $_SESSION['UserName'] .
                '.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="profile.php">Profile</a>'.
                '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="logout.php">Logout</a>&nbsp;&nbsp;</span></p>';
        } else {
            echo 'Not logged in.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="login.php">Login</a>&nbsp;&nbsp;</span></p>';
        }
    }
}

include("dbConnect.php");
// If session_start created a new session, log it.
if(session_start()) {
    $_SESSION['ipAddress'] = ipAddress();

    // We won't have user ID at this point, so log the session without it. On login, update
    //  the session record.
    $sql = 'SELECT fnLogSession(\''.session_id().'\', \''.$_SESSION['ipAddress'].'\', NULL)';
    $result = mysqli_query($connection, $sql) or die("Error: " . $sql. PHP_EOL . mysqli_error($connection));
};

?>
