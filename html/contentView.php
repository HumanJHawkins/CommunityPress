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


abstract class ViewMode
{
  const View = 0;
  const Create = 1;
  const Update = 2;
}


$ViewMode = ViewMode::View;
if (!isset($_POST["pageContentID"]) || $_POST["pageContentID"] == '' || $_POST["pageContentID"] == 0) {
  $ViewMode = ViewMode::Create;
} else {
  if (isset($canEdit) && ($canEdit)) {
    $ViewMode = ViewMode::Update;
  }
}

htmlStart('Content View');
?>
<div class="container">
  <?php include 'divButtonGroupMain.php'; ?>
  <br/>
  <?php include 'divV4LBanner.php'; ?>
  <br/>
  <?php
  debugSectionOut("Edit Content");
  debugOut('$action', $action);
  debugOut('$userID', $userID);
  debugOut('$contentTitle', $contentTitle);
  debugOut('$contentDescription', $contentDescription);
  debugOut('$contentExcerpt', $contentExcerpt);
  debugOut('$contentSummary', $contentSummary);
  debugOut('$contentFilename', $contentFilename);
  debugOut('$sql', $sql);
  ?>

  <form enctype="multipart/form-data" action="contentEdit.php" method="post" name="contentEditForm">
    <table id="contentEditTable">

      <tr>
        <td>ID:</td>
        <td>
          <?php
          if ($ViewMode == ViewMode::Create) {
            // echo '<input type="text" name="contentID" value="Auto-generated" rows="1" cols="80" readonly/>';
            echo '<textarea name="pageContentID" rows="1" cols="80" required placeholder="ID" id="pageContentID" readonly>Auto-generated</textarea>';
          } else {
            // echo '<input type="text" name="contentID" value="' . $_POST["pageContentID"] . '" readonly/>';
            echo '<textarea name="pageContentID" rows="1" cols="80" required placeholder="ID" id="pageContentID" readonly>' . $_POST["pageContentID"] . '</textarea>';
          }
          ?>
        </td>
      </tr>
      <tr>
        <td>Title:</td>
        <td><textarea name="contentTitle" rows="1" cols="80"
          <?php
          if ($contentTitle == '') {
            echo 'required placeholder="Title" id="inputContentTitle"></textarea>';
          } else {
            echo ' id="inputContentTitle">' . $contentTitle . '</textarea>';
          }
          ?>
        </td>
      </tr>
      <tr>
        <td>Description:&nbsp;</td>
        <td><textarea name="contentDescription" rows="3" cols="80"
          <?php if ($contentDescription == '') {
            echo 'required placeholder="Description" id="inputContentDescription"></textarea>';
          } else {
            echo ' id="inputContentDescription">' . $contentDescription . '</textarea>';
          } ?>
        </td>
      </tr>
      <tr>
        <td>Text:</td>
          <td><textarea name="contentExcerpt" rows="20" cols="80"
            <?php if ($contentExcerpt == '') {
              echo 'required placeholder="Content Text" id="inputcontentExcerpt"></textarea>';
          } else {
              echo ' id="inputcontentExcerpt">' . $contentExcerpt . '</textarea>';
          } ?>
        </td>
      </tr>
      <tr>
        <td>URL:</td>
          <td><textarea name="contentSummary" rows="1" cols="80"
            <?php if ($contentSummary == '') {
            echo 'required placeholder="Fully Qualified URL (i.e. http://www.example.com)"></textarea>';
          } else {
              echo ' id="inputcontentSummary">' . $contentSummary . '</textarea>';
          } ?>
        </td>
      </tr>
      <tr>
        <td></td>
        <td></td>
      </tr>
      <tr>
        <td>
          <?php if ($ViewMode == ViewMode::Create) {
            echo 'File to upload:';
          } elseif ($ViewMode == ViewMode::Update) {
            echo 'New (Replacement) File:';
          } else {
            echo 'Filename: ';
          }
          ?>
        </td>
        <td>
          <?php
          if ($ViewMode == ViewMode::Create || $ViewMode == ViewMode::Update) {
            // <!-- MAX_FILE_SIZE must precede the file input field -->
            echo '<input type="hidden" name="MAX_FILE_SIZE" value="16777216"/>';
            // <!-- Name of input element determines name in $_FILES array -->
            echo '<input name="userUpload" type="file"/>';
          } else {
            echo '< textarea name = "contentFilename" rows = "1" cols = "80"';
            if ($contentFilename == '') {
              echo ' required placeholder="Content Filename" id="inputcontentExcerpt"></textarea>';
            } else {
              echo ' id="inputcontentExcerpt">' . $contentFilename . '</textarea>';
            }
          }
          ?>
        </td>
      </tr>
      <tr>
        <td></td>
        <td><?php
          if ($ViewMode == ViewMode::Create) {
            echo '<input type="submit" class="btn btn-primary" name="insert" value=" Add Content " id="inputid1" /> ';
          } else {
            if ($ViewMode == ViewMode::Update) {
              echo '<input type="submit" class="btn btn-primary" name="update" value="Save Changes" id="inputid1" /> ';
              echo '<input type="button" class="btn btn-default" name="cancel" value="   Cancel   " onClick="window.location=\'./content.php\';" />';
            } else {
              echo '<input type="button" class="btn btn-default" name="back" value="    Back    " onClick="window.location=\'./content.php\';" />';
            }
          }
          ?>
        </td>
      </tr>
    </table>
  </form>
  <br/>

  <!-- Here we should conditionally (if editing) add or remove tags. -->
  <?php
  if (isset($_POST["pageContentID"]) && ($_POST["pageContentID"] > 0)) {
    include 'divContentTagsEdit.php';
    include 'divContentTags.php';
  }
  ?>

</div>
</body>
</html>

































