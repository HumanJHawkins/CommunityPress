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
if ((isset($_SESSION["userID"])) && ($_SESSION["userID"] > 0)) {
  $userID = $_SESSION["userID"];
} else {
  $userID = 0;
}
debugOut('$userID', $userID);

if ((isset($_POST["contentTitle"])) && ($_POST["contentTitle"] != '')) {
  $contentTitle = trim($_POST["contentTitle"]);
} else {
  $contentTitle = '';
}
debugOut('$contentTitle', $contentTitle);

if ((isset($_POST["contentSummary"])) && ($_POST["contentSummary"] != '')) {
  $contentSummary = trim($_POST["contentSummary"]);
} else {
  $contentSummary = '';
}
debugOut('contentSummary', $contentSummary);

if ((isset($_POST["contentDescription"])) && ($_POST["contentDescription"] != '')) {
  $contentDescription = trim($_POST["contentDescription"]);
} else {
  $contentDescription = '';
}
debugOut('$contentDescription', $contentDescription);

if ((isset($_POST["contentExcerpt"])) && ($_POST["contentExcerpt"] != '')) {
  $contentExcerpt = trim($_POST["contentExcerpt"]);
} else {
  $contentExcerpt = '';
}
debugOut('$contentExcerpt', $contentExcerpt);

if ((isset($_FILES['contentFile']['name'])) && ($_FILES['contentFile']['name'] != '')) {
  $contentFilename = $_FILES['contentFile']['name'];
} else {
  $contentFilename = null;
}
debugOut('******************************** File Info');
outputArray($_FILES);
debugOut('****************');
if (isset($contentFile)) {
  outputArray($contentFile);
} else {
  debugOut('$contentFile is not set.');
}
debugOut('****************');
debugOut('$contentFilename', $contentFilename);
debugOut('********************************');

// Set variables for input form and continue to display.
$sql = '';
if ($action == 'delete') {
  // TO DO: Handle file delete too!
  $sql = 'SELECT contentDelete(?, ?)';
  $sqlParamsArray = [$_POST["pageContentID"], $userID];
  $result = getOnePDORow($pdo, $sql, $sqlParamsArray);
  header('Location: ' . '/content.php');
  exit();
} else if ($action == 'insert') {
  $sql = 'SELECT contentInsert(?, ?, ?, ?, ?, ?)';
  $sqlParamsArray = [$contentTitle, $contentDescription, $contentExcerpt, $contentSummary, $contentFilename, $userID];
  debugOut('******************************** SQL Info');
  debugOut('insert $sql', $sql);
  outputArray($sqlParamsArray);

  $newID = getOnePDOValue($pdo, $sql, $sqlParamsArray);
  debugOut('$newID', $newID);

  // TO DO: This needs to be a file upload function, taking the name of the file. (Note: $_FILES is superglobal.)
  // Same function for insert and update, with different input.
  if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] == UPLOAD_ERR_NO_FILE) {
    // $uploadfile = $GLOBALS['CONTENT_STORE_DIRECTORY'] . basename($_FILES['userUpload']['name']);
    // $path = $_FILES['image']['name'];
    // $ext = pathinfo($path, PATHINFO_EXTENSION);
    $uploadfile = $GLOBALS['CONTENT_STORE_DIRECTORY'] . $newID . '.' .
        pathinfo($_FILES['userUpload']['name'], PATHINFO_EXTENSION);

    if (move_uploaded_file($_FILES['userUpload']['tmp_name'], $uploadfile)) {
      ;  // Success. Do nothing here.
    } else {
      echo '<pre><br />File upload error. File array dump follows. <br />';
      outputArray($_FILES, true);
      echo "<script>alert('Upload error. Press OK to return to page.')</script>";
    }
  }

  $_SESSION['lastURL'] = 'contentEdit.php?action=edit&pageContentID=' . $newID;
  header('Location: ' . $_SESSION['lastURL']);
  exit();
} else if ($action == 'update') {
  $sql = 'SELECT contentUpdate(?, ?, ?, ?, ?, ?, ?)';
  if (isset($contentFile[name]) && $contentFile[name] != '') {
    $sqlParamsArray =
        [$_POST["pageContentID"], $contentTitle, $contentDescription, $contentExcerpt, $contentSummary, $contentFile[name], $userID];
  } else {
    $sqlParamsArray =
        [$_POST["pageContentID"], $contentTitle, $contentDescription, $contentExcerpt, $contentSummary, null, $userID];
  }
  debugOut('******************************** SQL Info');
  debugOut('update $sql', $sql);
  outputArray($sqlParamsArray);

  $result = getOnePDOValue($pdo, $sql, $sqlParamsArray);

  if (!isset($_FILES['file_upload']) || $_FILES['file_upload']['error'] == UPLOAD_ERR_NO_FILE) {
    // $uploadfile = $GLOBALS['CONTENT_STORE_DIRECTORY'] . basename($_FILES['userUpload']['name']);
    // $path = $_FILES['image']['name'];
    // $ext = pathinfo($path, PATHINFO_EXTENSION);

    $uploadfile = $GLOBALS['CONTENT_STORE_DIRECTORY'] . $_POST["pageContentID"] . '.' .
        pathinfo($_FILES['userUpload']['name'], PATHINFO_EXTENSION);

    // echo '<pre>';
    if (move_uploaded_file($_FILES['userUpload']['tmp_name'], $uploadfile)) {
      ;  // Success. Do nothing here.
    } else {
      echo '<pre><br />File upload error. File array dump follows. <br />';
      outputArray($_FILES, true);
      echo "<script>alert('Upload error. Press OK to return to page.')</script>";
    }
  }

  header('Location: ' . 'contentEdit.php?action=edit&pageContentID=' . $_POST["pageContentID"]);
  exit();
}

// action=edit&pageContentID=100257

// If we are still here, we are displaying the content edit screen... So, if editing,
//  load the content to edit. Otherwise just continue with defaults.
if ($action == 'edit') {
  $sql = 'CALL procViewContent(?, ?)';
  if (isset($_SESSION['userID']) && ($_SESSION['userID'] > 0)) {
    $sqlParamsArray = [$_POST["pageContentID"], $_SESSION['userID']];
  } else {
    $sqlParamsArray = [$_POST["pageContentID"], 0];
  }
  debugOut('$sql', $sql);
  outputArray($sqlParamsArray);

  $row = getOnePDORow($pdo, $sql, $sqlParamsArray, PDO::FETCH_ASSOC);
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
}


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
        theme: "modern"
        // theme : "simple"
        //setup: function (editor) {
        //    editor.on('change', function () {
        //        editor.save();
        //    });
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
                <td class="contentInputLabel">ID:</td>
                <td>
                  <?php
                  if ($ViewMode == ViewMode::Create) {
                    // echo '<input type="text" name="contentID" value="Auto-generated" rows="1" cols="80" readonly/>';
                    echo '<textarea name="pageContentID" required placeholder="ID" id="pageContentID" readonly>Auto-generated</textarea>';
                  } else {
                    // echo '<input type="text" name="contentID" value="' . $_POST["pageContentID"] . '" readonly/>';
                    echo '<textarea name="pageContentID" required placeholder="ID" id="pageContentID" readonly>' .
                        $_POST["pageContentID"] . '</textarea>';
                  }
                  ?>
                </td>
            </tr>
            <tr>
                <td class="contentInputLabel"><?= $GLOBALS['CONTENT_TITLE_LABEL'] ?>:</td>
                <td><textarea name="contentTitle" class="form-control html5EditControl" style="min-width: 80%"
                  <?php
                  if ($contentTitle == '') {
                    echo ' required id="inputContentTitle"></textarea>';
                  } else {
                    echo ' id="inputContentTitle">' . $contentTitle . '</textarea>';
                  }
                  ?>
                </td>
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
                <td class="contentInputLabel"><?= $GLOBALS['CONTENT_DESCRIPTION_LABEL'] ?>:</td>
                <td><textarea name="contentDescription" class="html5EditControl" rows="5" cols="80"
                  <?php if ($contentDescription == '') {
                    echo 'required id="inputContentDescription"></textarea>';
                  } else {
                    echo ' id="inputContentDescription">' . $contentDescription . '</textarea>';
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
































