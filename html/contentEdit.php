<?php
include 'pageHeader.php';
debugOut('**************************************************************** Beginning contentEdit.php');
$pdo = getDBPDO();

// Action determined from GET directly, else via POST. Will be:
//  Update or Insert (Same function): Data is set, so update DB.
//  Edit:   Load an existing contentTitle for editing.
//  Delete: Delete from DB
if ((isset($_POST["update"])) && ($_POST["update"] != '')) {
  $action = 'update';
} else if ((isset($_POST["insert"])) && ($_POST["insert"] != '')) {
  $action = 'insert';
} else if ((isset($_GET["action"])) && ($_GET["action"] != '')) {
  $action = $_GET["action"];
} else {
  $action = '';
}
debugOut('$action', $action);

// pageContentID for Edit/Delete comes from $_GET. For Update and insert comes from $_POST. Consolidate.
consolidatePageContentID();

// UserID needed for validating permission to edit.
if ((!isset($_SESSION["userID"])) || ($_SESSION["userID"] < 1)) {
  $_SESSION["userID"] = 0;
}
debugOut('$_SESSION["userID"]', $_SESSION["userID"]);

$contentFilename = 'Select File';
$contentFileGraphic = 'Select Graphic';
$contentTitle = '';
$contentDescription = '';
$contentExcerpt = '';
$contentSummary = '';
$existingAvatarID = 0;
$existingAvatarPath = '';
$existingAvatarFile = '';
$canEdit = false;

if ($_POST["pageContentID"] > 0) {
  $sql = 'CALL procViewContent(?, ?)';
  $sqlParamsArray = [$_POST["pageContentID"], $_SESSION["userID"]];
  $row = getOnePDORow($pdo, $sql, $sqlParamsArray, PDO::FETCH_ASSOC);
  if (!empty($row)) {
    $contentTitle = $row['contentTitle'];
    $contentSummary = $row['contentSummary'];
    $contentExcerpt = $row['contentExcerpt'];
    $contentDescription = $row['contentDescription'];
    $existingAvatarID = $row['contentAvatarID'];
    $canEdit = $row['canEdit'];
  }

  if ($existingAvatarID > 0) {
    $sql = 'SELECT uploadFilePath, uploadFileName  FROM uploadFile WHERE uploadFileID = ?';
    $sqlParamsArray = [$existingAvatarID];
    $row = getOnePDORow($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
    $existingAvatarPath = $row[0];
    $existingAvatarFile = $row[1];
  }
} else {
  $sql = 'SELECT userIsContentEditor(?)';
  $sqlParamsArray = [$_SESSION["userID"]];
  $canEdit = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
}

// Now, oveerids as needed from $_POST and $_GET
if ((isset($_POST["contentTitle"])) && ($_POST["contentTitle"] != '')) {
  $contentTitle = trim(stripslashes($_POST["contentTitle"]));
}
debugOut('$contentTitle', $contentTitle);

if ((isset($_POST["contentSummary"])) && ($_POST["contentSummary"] != '')) {
  $contentSummary = trim(stripslashes($_POST["contentSummary"]));
}
debugOut('contentSummary', $contentSummary);

if ((isset($_POST["contentExcerpt"])) && ($_POST["contentExcerpt"] != '')) {
  $contentExcerpt = trim(stripslashes($_POST["contentExcerpt"]));
}
debugOut('$contentExcerpt', $contentExcerpt);

if ((isset($_POST["contentDescription"])) && ($_POST["contentDescription"] != '')) {
  $contentDescription = trim(stripslashes($_POST["contentDescription"]));
}
debugOut('$contentDescription', $contentDescription);

debugOut('****************************************************************************************************');
debugOut('****************************************************************************************************');
debugOut('*** contentEdit.php ********************************************************************************');

debugOut('$contentFilename', $contentFilename);
debugOut('$contentFileGraphic', $contentFileGraphic);
debugOut('$contentTitle', $contentTitle);
debugOut('$contentDescription', $contentDescription);
debugOut('$contentExcerpt', $contentExcerpt);
debugOut('$contentSummary', $contentSummary);
debugOut('$existingAvatarID', $existingAvatarID);
debugOut('$existingAvatarPath', $existingAvatarPath);
debugOut('$existingAvatarFile', $existingAvatarFile);
debugOut('$canEdit', $canEdit);

// Set variables for input form and continue to display.
if ($action == 'delete' && $canEdit) {
  debugOut('************************************************************************************************');
  debugOut('************************************************************************************************');
  debugOut('*** In content delete! *************************************************************************');
  // SQL contentDelete function handles delete of content record, uploadFile records, and thingTag records.
  // First delete the files, then delete the records.
  $sql = 'CALL procGetContentFiles(?, ?)';
  $sqlParamArray = [$_POST["pageContentID"], $_SESSION['userID']];
  $result = getOnePDOTable($pdo, $sql, $sqlParamArray, PDO::FETCH_ASSOC);
  debugOut('Array of files to delete');
  outputArray($result);
  foreach ($result as $index => $row) {
    unlink(realpath($row["uploadFilePath"] . strval($row['uploadFileID'])));

    // The content avatar is in this list, so does not need to be handled separately.
    // $contentAvatarPathName = realpath($existingAvatarPath . strval($existingAvatarID));
    // unlink($contentAvatarPathName);
  }

  $sql = 'SELECT contentDelete(?, ?)';
  $sqlParamsArray = [$_POST["pageContentID"], $_SESSION["userID"]];
  $result = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
  debugOut('$result (of delete)', $result);
  header('Location: ' . '/content.php');
  exit();
} else if ($action == 'insert' || $action == 'update') {
  $sql = 'SELECT contentInsertUpdate(?, ?, ?, ?, ?, ?)';
  $sqlParamsArray =
      [$_POST["pageContentID"], $contentTitle, $contentDescription, $contentExcerpt, $contentSummary, $_SESSION["userID"]];
  $contentRecordID = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
  debugOut('$contentRecordID', $contentRecordID);

  // Handles delete of existing after validating new avatar, then uploads new avatar.
  handleUploadAvatar($pdo, $contentRecordID, $existingAvatarPath, $existingAvatarID);

  // Need delete content mechanism.
  $contentFileID = handleUploadContent($pdo);
  if ($contentFileID > 0) {
    $sql = 'SELECT tagAttach(?, ?, ?)';
    $sqlParamsArray = [$contentRecordID, $contentFileID, $_SESSION["userID"]];
    $contentFileRelationshipID = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);

    // TO DO: Do we need to tag content files as being downloads for this content record? Or can this be assumed from
    //   the relationship?
    // $sqlParamsArray = [$contentFileRelationshipID, 'Need Tag to indicate content file', $_SESSION["userID"]];
    // $cntntFileRelRelID = getOnePDOValue($pdo, $sql, $sqlParamsArray, PDO::FETCH_NUM);
  }

  $_SESSION['lastURL'] = 'contentEdit.php?action=edit&pageContentID=' . $contentRecordID;
  header('Location: ' . $_SESSION['lastURL']);
  exit();
}

// If we are still here, we are displaying the content edit screen. Values loaded at the top of this file should
// be valid. So continue.

abstract class ViewMode {
  const View = 0;
  const Create = 1;
  const Update = 2;
}


$ViewMode = ViewMode::View;
if ($_POST["pageContentID"] < 1) {
  $ViewMode = ViewMode::Create;
} else {
  if (isset($canEdit) && ($canEdit)) {
    $ViewMode = ViewMode::Update;
  } else {
    $ViewMode = ViewMode::View;
  }
}

htmlStart('Content View');
?>

<script type="text/javascript">
    tinyMCE.init({
        //mode : "textareas",
        mode: "specific_textareas",
        editor_selector: "html5EditControl",
        width: "900",
        plugins: 'print preview fullpage searchreplace autolink directionality visualblocks visualchars fullscreen image link media template codesample table charmap hr pagebreak nonbreaking anchor toc insertdatetime advlist lists textcolor wordcount imagetools contextmenu colorpicker textpattern help',
        toolbar1: 'formatselect | bold italic strikethrough forecolor backcolor | link | alignleft aligncenter alignright alignjustify  | numlist bullist outdent indent  | removeformat',
        image_advtab: true,
        theme: "modern",
        branding: false
    });
</script>
<div class="container">
  <?php include 'divButtonGroupMain.php'; ?>
    <br/>
  <?php include 'divV4LBanner.php'; ?>
    <br/>
  <?php
  debugSectionOut("Edit Content");
  debugOut('$action', $action);
  debugOut('$_SESSION["userID"]', $_SESSION["userID"]);
  debugOut('$contentTitle', $contentTitle);
  debugOut('$contentDescription', $contentDescription);
  debugOut('$contentExcerpt', $contentExcerpt);
  debugOut('$contentSummary', $contentSummary);
  debugOut('$contentFilename', $contentFilename);
  debugOut('$sql', $sql);
  ?>

    <form enctype="multipart/form-data" action="contentEdit.php" method="post" name="contentEditForm">
        <table id="contentEditTable" style="border-spacing:5em;">

            <tr>
                <td>ID:</td>
                <td>
                  <?php
                  if ($ViewMode == ViewMode::Create) {
                    // echo '<input type="text" name="contentID" value="Auto-generated" rows="1" cols="80" readonly/>';
                    echo '<textarea name="pageContentID" placeholder="ID" id="pageContentID" readonly>Auto-generated</textarea>';
                  } else {
                    // echo '<input type="text" name="contentID" value="' . $_POST["pageContentID"] . '" readonly/>';
                    echo '<textarea name="pageContentID" placeholder="ID" id="pageContentID" readonly>' .
                        $_POST["pageContentID"] . '</textarea>';
                  }
                  ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td>Content Avatar: <br/>
                    <img class="img-thumbnail left avatar" style="max-width:100px;max-height:100px;"
                         src="./userImage/<?= $existingAvatarID ?>"/>
                </td>
                <td>
                  <?php
                  if ($ViewMode == ViewMode::Create || $ViewMode == ViewMode::Update) {
                    // <!-- MAX_FILE_SIZE must precede the file input field -->
                    echo '<input type="hidden" name="MAX_FILE_SIZE" value="' . $GLOBALS["CONTENT_IMAGE_MAX_FILESIZE"] .
                        '" "/>';
                    // <!-- Name of input element determines name in $_FILES array -->
                    echo '<input name="contentFileGraphic" type="file" /> ';
                  } else {
                    echo '<textarea name="contentFileGraphic" rows = "1" cols = "80"';
                    if (is_null($contentFileGraphic) || $contentFileGraphic == '') {
                      echo ' placeholder="Content Filename" id="inputContentFilename"></textarea>';
                    } else {
                      echo ' placeholder="Content Filename" id="inputContentFilename"></textarea>';
                    }
                  }
                  ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="contentInputLabel"><?= $GLOBALS['CONTENT_TITLE_LABEL'] ?>:</td>
                <td><textarea name="contentTitle" class="form-control" style="min-width: 80%"
                  <?php
                  if ($contentTitle == '') {
                    echo ' id="inputContentTitle"></textarea>';
                  } else {
                    echo ' id="inputContentTitle">' . $contentTitle . '</textarea>';
                  }
                  ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="contentInputLabel"><?= $GLOBALS['CONTENT_SUMMARY_LABEL'] ?>:&nbsp;</td>
                <td><textarea name="contentSummary" class="html5EditControl" rows="15" cols="80"
                  <?php if ($contentSummary == '') {
                    echo ' id="inputcontentSummary"></textarea>';
                  } else {
                    echo ' id="inputcontentSummary">' . $contentSummary . '</textarea>';
                  } ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="contentInputLabel"><?= $GLOBALS['CONTENT_EXCERPT_LABEL'] ?>:</td>
                <td><textarea name="contentExcerpt" class="html5EditControl" rows="15" cols="80"
                  <?php if ($contentExcerpt == '') {
                    echo ' id="inputcontentExcerpt"></textarea>';
                  } else {
                    echo ' id="inputcontentExcerpt">' . $contentExcerpt . '</textarea>';
                  } ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
            </tr>
            <tr>
                <td class="contentInputLabel"><?= $GLOBALS['CONTENT_DESCRIPTION_LABEL'] ?>:</td>
                <td><textarea name="contentDescription" class="html5EditControl" rows="5" cols="80"
                  <?php if ($contentDescription == '') {
                    echo ' id="inputContentDescription"></textarea>';
                  } else {
                    echo ' id="inputContentDescription">' . $contentDescription . '</textarea>';
                  } ?>
                </td>
            </tr>
            <tr>
                <td>&nbsp;</td>
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
                    echo '<input type="hidden" name="MAX_FILE_SIZE" value="' . $GLOBALS["CONTENT_STORE_MAX_FILESIZE"] .
                        '" "/>';
                    // <!-- Name of input element determines name in $_FILES array -->
                    echo '<input name="contentFile" type="file" /> ';
                  } else {
                    echo '<textarea name="contentFilename" rows = "1" cols = "80"';
                    if (is_null($contentFilename) || $contentFilename == '') {
                      echo ' placeholder="Content Filename" id="inputContentFilename"></textarea>';
                    } else {
                      echo ' id="inputContentFilename">' . $contentFilename . '</textarea>';
                    }
                  }
                  ?>
                </td>
            </tr>
            <tr>
                <td></td>
                <td>
                    <br/>
                  <?php
                  if ($ViewMode == ViewMode::Create) {
                    echo '<input type="submit" class="btn btn-primary" name="insert" value=" Add Content " id="inputid1" /> ';
                  } else {
                    // if ($ViewMode == ViewMode::Update) {
                    echo '<input type="submit" class="btn btn-primary" name="update" value="Save Changes" id="inputid1" /> &nbsp; ';
                    echo '<a href="./contentEdit.php?action=delete&pageContentID=' . $_POST["pageContentID"] .
                        '" class="btn btn-primary" style="float: right" onclick="return confirm(\'Are you sure you wish to delete this Record?\');"> &nbsp; Delete &nbsp; </a>&nbsp;';
                  }
                  echo '<input type="button" class="btn btn-default" name="back" value="&nbsp;&nbsp;&nbsp;&nbsp;Back&nbsp;&nbsp;&nbsp;&nbsp;" onClick="window.location=\'./content.php\';" />';
                  ?>
                </td>
            </tr>
        </table>
    </form>
    <script>
        $('#myform').submit(function () {
            tinyMCE.triggerSave();
        });
    </script>


    <br/>
    <!-- Here we should conditionally (if editing) add or remove tags. -->
  <?php
  // if ($_POST["pageContentID"] > 0) {
  if (isset($_POST["pageContentID"]) && ($_POST["pageContentID"] > 0)) {
    include 'divContentTagsEdit.php';
    include 'divContentTags.php';
  }
  ?>

</div>
</body>
</html>
































