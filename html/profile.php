<?php
include 'pageHeader.php';
htmlStart('User Profile');
?>

<div class="container">
  <?php include 'divButtonGroupMain.php'; ?>
  <br/>
  <?php include 'divV4LBanner.php'; ?>
  <br/>
<?php

// Need to be able to add grade tags, for grades taught (or, grades of interest... so same for student and teacher.)
// Maybe select from combo and either add or remove?
// Seperate display update.

// This should all be AJAX... Learn and do, or just PHP?

// On Superuser, allow selecting user at the top...
//  So, on load, copy user into variable. Act on variable user, not current user.
//  Superuser can change the variable. Others don't see the UX to do that.
// Consider reasonable limits on what Superuser can do via the web UX.

// Consider other tags and data...
//    What do we need to know about users?
//    What should we avoid knowing?
//    What do users want to add about themselves?
// Support Avatar or Gravatar?
// Look at Facebook, Google, LinkedIn, DDO forum, LesPaul Forum, etc.

// Consider enhancing tag design... Is there a third column for primary key on binding? (theThing, theTag, and theContext?)

// Run a backup


echo '<h1>'.$_SESSION["userName"].'</h1>';

// echo 'userID: '.$_SESSION["userID"];
echo '<br />Email: '.$_SESSION["userEmail"];
// echo '<br />Birthday: '.$_SESSION["userBirthday"];
// echo '<br />Reputation: '.$_SESSION["userReputation"];
echo '<p></p>';

IF (!$_SESSION["isActive"]) {
    echo '<p>Registration is inactive. Please check your e-mail for a verification code.</p>';
} else {
    echo '<p>Registration is active with verified email.</p>'.
        '<p>All active users are encouraged to create and share content, comment '.
        'and discuss, write reviews, and join in our community.</p>';
  IF ($_SESSION["isSuperuser"]) {
        echo '<p>Additionally, you are a superuser, permitted for all functions.</p>';
    } elseif ($_SESSION["isTagEditor"] || $_SESSION["isUserEditor"] || $_SESSION["isSiteDeveloper"]) {
      echo '<p>Additionally, you have the following permissions:</p><ul>';
      if($_SESSION["isTagEditor"]) {echo 'You are a Tag Editor, permitted to create and modify tags.';}
      if($_SESSION["isUserEditor"]) {echo 'You are a User Editor, permitted to modify user accounts.';}
      if($_SESSION["isSiteDeveloper"]) {echo 'You are a Site Developer, permitted to view and change most data.';}
      echo '</ul>';
    }

 //   IF (!$_SESSION["isLicensed"]) {
 //       echo '<p>User has accepted the site license agreement.</p>';
 //   }
}
?>
<br />
Please periodically review our <a href="termsandconditions.php">Terms and Conditions</a> and
<a href="privacypolicy.php">Privacy Policy</a>.
<p>
  <a href="login.php?action=reset">Reset Password</a>
</p>
</div>
</body>
</html>
