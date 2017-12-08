<?php
/*
 * fnTagAttachmentEdit.php is non-specific. Can be used to add content tags, or to bind a review to a user, etc. Though that may be a bad
 * example, as the review code will probably do that during review creation.
 *
 * Requires input of a thingID (i.e. ContentID), and another thingID (i.e. tagID) to bind to the first thing, as well as
 * a UserID to both validate that the action is permitted and to log who took this action.
 *
 * Uses:
 *   FUNCTION v4l.tagAttach(theTagged BIGINT, theTag BIGINT, theUser BIGINT)
 *   FUNCTION v4l.tagRemove(theTagged BIGINT, theTag BIGINT, theUser BIGINT)
*/
include_once 'sessionStart.php';
if (!isset($pdo)) {
  $pdo = getDBPDO();
}

if (
  (!((isset($_POST["action"]))    && (($_POST["action"] == 'attach') || ($_POST["action"] == 'remove')))) ||
  (!((isset($_POST["theTagged"])) && ($_POST["theTagged"] > 0))) ||
  (!((isset($_POST["theTag"]))    && ($_POST["theTag"] > 0))) ||
  (!((isset($_POST["theUser"]))   && ($_POST["theUser"] > 0)))
  ) {
  $ErrorMsg = 'Parameters invalid. Cannot proceed without valid action, theTagged, theTag, and the User values.';
  debugOut($ErrorMsg);
  exit($ErrorMsg);
}

if($_POST['action'] == 'attach') {
  $sql = 'SELECT tagAttach(?, ?, ?)';
} elseif ($_POST['action'] == 'remove') {
  $sql = 'SELECT tagRemove(?, ?, ?)';
} else {
  $ErrorMsg = 'fnTagAttachmentEdit.php Error: Invalid $_POST["action].';
  debugOut($ErrorMsg);
  exit($ErrorMsg);
}

$sqlParamsArray = [$_POST["theTagged"], $_POST["theTag"], $_POST["theUser"]];
$result = getOnePDORow($pdo,$sql,$sqlParamsArray);
exit();
?>

