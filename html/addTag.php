<?php
/**
 * addTag.php is non-specific. Can be used to add content tags, or to bind a review to a user, etc. Though that may be a bad
 * example, as the review code will probably do that during review creation.
 *
 * Requires input of a thingID (i.e. ContentID), and another thingID (i.e. tagID) to bind to the first thing, as well as
 * a UserID to both validate that the action is permitted and to log who took this action.
 *
 * Uses: FUNCTION v4l.TagUseInsert(theTagged BIGINT, theTag BIGINT, theUser BIGINT)
 */

include_once 'sessionStart.php';

if (!((isset($_POST["theTagged"])) || ($_POST["theTagged"] > 0))) {
  exit("theTagged is invalid. Cannot insert tag.");
}

if (!((isset($_POST["theTag"])) || ($_POST["theTag"] > 0))) {
  exit("theTag is invalid. Cannot insert tag.");
}

if (!((isset($_POST["theUser"])) || ($_POST["theUser"] > 0))) {
  exit("theUser is invalid. Cannot insert tag.");
}

$connection = getDBConnection();
$sql = 'SELECT TagUseInsert(' . $_POST["theTagged"] . ', ' . $_POST["theTag"] . ', ' . $_POST["theUser"] . ')';
$result = mysqli_query($connection, $sql) or die("<br />Error:<br /> " . $sql . '<br /> ' . mysqli_error($connection));
exit();


