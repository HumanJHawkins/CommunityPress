<?php
include 'pageHeader.php';
$pdo = getDBPDO();

// View $_POST["pageContentID"]
consolidatePageContentID();

// Get Content to display
$sql = 'CALL procViewContent(?, ?)';
if (isset($_SESSION['userID']) && ($_SESSION['userID'] > 0)) {
  $sqlParamsArray = [$_POST["pageContentID"], $_SESSION['userID']];
} else {
  $sqlParamsArray = [$_POST["pageContentID"], 0];
}
$row = getOnePDORow($pdo, $sql, $sqlParamsArray);
outputArray($row);
if (!empty($row)) {
  $contentTitle = trim($row['contentTitle']);
  $contentDescription = trim($row['contentDescription']);
  $contentExcerpt = trim($row['contentExcerpt']);
  $contentSummary = trim($row['contentSummary']);
  $contentFilename = trim($row['contentFilename']);
  $canEdit = $row['canEdit'];
} else {
  $contentTitle = 'Title';
  $contentDescription = 'Description.';
  $contentExcerpt = 'Excerpt';
  $contentSummary = 'URL';
  $contentFilename = 'Select Filename with Browse Button.';
  // $canEdit = true;
}

htmlStart('Content View');

echo '<div class="container">';
include 'divButtonGroupMain.php';
echo '<br/>';
// include 'divV4LBanner.php';
// echo '<br/>';
debugSectionOut("Edit Content");
debugOut('$contentTitle', $contentTitle);
debugOut('$contentDescription', $contentDescription);
debugOut('$contentExcerpt', $contentExcerpt);
debugOut('$contentSummary', $contentSummary);
debugOut('$contentFilename', $contentFilename);
debugOut('$sql', $sql);

echo '<h1>' . $contentTitle . '</h1>';
echo '<br/><br/>';

echo '<h3>' . $GLOBALS['CONTENT_SUMMARY_LABEL'] . '</h3>';
echo $contentSummary;
echo '<br/><hr/>';

echo '<h3>' . $GLOBALS['CONTENT_EXCERPT_LABEL'] . '</h3>';
echo $contentExcerpt;
echo '<br/><hr/>';

echo '<h3>' . $GLOBALS['CONTENT_DESCRIPTION_LABEL'] . '</h3>';
echo $contentDescription;
echo '<br/><hr/>';

echo '<h3>Download</h3>';
echo $contentFilename;
echo '<br/><hr/>';

if (isset($_POST["pageContentID"]) && ($_POST["pageContentID"] > 0)) {
  include 'divContentTags.php';
}

echo '<br /><hr /><input type="button" class="btn btn-default" name="back" value="    Back    " onClick="window.location=\'./content.php\';" />';

?>

</div>
</body>
</html>

































