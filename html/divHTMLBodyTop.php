<?php
echo '<body><div class="wrap">';
if (!(substr($_SERVER['REQUEST_URI'], -9) == 'index.php')) {
  echo '<div class="left"><a href="index.php">Home</a></div>';
}

echo '<span style="float:right;">';
// echo '<div class="right">';
if (isset($_SESSION['userID']) && $_SESSION['userID'] > 0) {
  echo 'Hi <a href="profile.php">' . $_SESSION['userName'] . '</a>';
  
  if (isset($_SESSION["isSuperUser"]) && ($_SESSION["isSuperUser"])) {
    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="admin.php">Admin</a>';
  }
  
  echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="login.php?action=logout">Logout</a></div>';
  
  
  
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
  echo 'Not logged in.&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="login.php">Login or Register</a></div>';
}
echo '<hr />';
debugOut($_SESSION['lastURL'], $_SESSION['lastURL']);
echo '<br />';
?>