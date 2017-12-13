<?php
/**
 * tagAttach.php is non-specific. Can be used to add content tags, or to bind a review to a user, etc. Though that
 * may be a bad example, as the review code will probably do that during review creation.
 *
 * Requires input of a thingID (i.e. ContentID), and another thingID (i.e. tagID) to bind to the first thing, as well
 * as a UserID to both validate that the action is permitted and to log who took this action.
 *
 * Uses: FUNCTION v4l.TagUseInsert(theTagged BIGINT, theTag BIGINT, theUser BIGINT)
 */
include 'sessionStart.php';
if (!isset($pdo)) {
  $pdo = getDBPDO();
}

// Debug Kluge...
if (isset($_GET["theTagged"]) && ($_GET["theTagged"] > 0)) {
  $_POST["theTagged"] = $_GET["theTagged"];
  $_POST["theTag"] = $_GET["theTag"];
  $_POST["theUser"] = $_GET["theUser"];
}
if (!((isset($_POST["theTagged"])) || ($_POST["theTagged"] > 0))) {
  exit("theTagged is invalid. Cannot insert tag.");
}
if (!((isset($_POST["theTag"])) || ($_POST["theTag"] > 0))) {
  exit("theTag is invalid. Cannot insert tag.");
}
if (!((isset($_POST["theUser"])) || ($_POST["theUser"] > 0))) {
  exit("theUser is invalid. Cannot insert tag.");
}

// $sql = 'SELECT TagUseInsert(' . $_POST["theTagged"] . ', ' . $_POST["theTag"] . ', ' . $_POST["theUser"] . ')';
$stmt = $pdo->prepare('SELECT TagUseInsert(?, ?, ?)');
$stmt->execute([$_POST["theTagged"], $_POST["theTag"], $_POST["theUser"]]);
$result = $stmt->fetch();
$stmt->closeCursor();
exit();
?>

