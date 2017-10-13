<?php
  include 'sessionStart.php';
// Remember where we need to go after killing the session
  $url = $_SESSION['lastURL'];
// debugOut('$url',$url);
// Should have been saving states/data when changed. So just destroy the session.
  destroySession();
// Back to wherever the user was, but logged out.
  header('Location: ' . $url);
  exit();
?>