<?php
include 'pageHeader.php';
$connection = getDBConnection();

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

// pageContentID for Edit/Delete comes from $_GET. For Update and insert comes from $_POST.
//  Move all to _POST for consistency.
if ((isset($_GET["pageContentID"])) && ($_GET["pageContentID"] > 0)) {
  $_POST["pageContentID"] = $_GET["pageContentID"];
  unset($_GET["pageContentID"]);
}

// UserID needed for validating permission to edit.
if ((isset($_SESSION["userID"])) && ($_SESSION["userID"] > 0)) {
  $userID = $_SESSION["userID"];
} else {
  $userID = 0;
}

if ((isset($_POST["contentTitle"])) && ($_POST["contentTitle"] != '')) {
  $contentTitle = trim(mysqli_real_escape_string($connection, $_POST["contentTitle"]));
} else {
  $contentTitle = '';
}

if ((isset($_POST["contentDescription"])) && ($_POST["contentDescription"] != '')) {
  $contentDescription = trim(mysqli_real_escape_string($connection, $_POST["contentDescription"]));
} else {
  $contentDescription = '';
}

if ((isset($_POST["contentText"])) && ($_POST["contentText"] != '')) {
  $contentText = trim(mysqli_real_escape_string($connection, $_POST["contentText"]));
} else {
  $contentText = '';
}

if ((isset($_POST["contentURL"])) && ($_POST["contentURL"] != '')) {
  $contentURL = trim(mysqli_real_escape_string($connection, $_POST["contentURL"]));
} else {
  $contentURL = '';
}


// Set variables for input form and continue to display.
$sql = '';
if ($action == 'delete') {
  $sql = 'SELECT contentDelete(\'' . $_POST["pageContentID"] . '\', \'' . $userID . '\')';
  
  // Our redirect to last requested URL will cause a failure due to the $_GET paramaters passed in. So, strip them.
  $_SESSION['lastURL'] = strtok($_SESSION['lastURL'], '?');
  
} else if ($action == 'insert') {
  $sql = 'SELECT contentInsert("' . $contentTitle . '","' . $contentDescription . '","' . $contentText .
    '","' . $contentURL . '",' . $userID . ')';
} else if ($action == 'update') {
  $sql = 'SELECT contentUpdate(' . $_POST["pageContentID"] . ',"' . $contentTitle . '","' . $contentDescription .
    '","' . $contentText . '","' . $contentURL . '",' . $userID . ')';
}

// If we have SQL at this point, we are updating the DB via stored function. So run SQL and exit.
if ($sql != '') {
  $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
  header('Location: ' . $_SESSION['lastURL']);
  exit();
}

// If we are still here, we are displaying the content edit screen... So, if editing,
//  load the content to edit. Otherwise just continue with defaults.
if ($action == 'edit') {
  // $sql = 'SELECT contentTitle, contentDescription, contentText, contentURL FROM vContent WHERE contentID = ' . $_POST["pageContentID"];
  // $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
  // if ($userrow = mysqli_fetch_array($result)) {
  if (isset($_SESSION['userID']) && ($_SESSION['userID'] > 0)) {
    $sql = "CALL procViewContent(" . $_POST["pageContentID"] . "," . $_SESSION['userID'] . ")";
  } else {
    $sql = "CALL procViewContent(" . $_POST["pageContentID"] . ", 0)";
  }
  $row = getOneStoredProcRow($connection, $sql);
  
  outputArray($row);
  
  if (!empty($row)) {
    $contentTitle = trim($row['contentTitle']);
    $contentDescription = trim($row['contentDescription']);
    $contentText = trim($row['contentText']);
    $contentURL = trim($row['contentURL']);
    $canEdit = $row['canEdit'];
  } else {
    $contentTitle = 'Title';
    $contentDescription = 'Description.';
    $contentText = 'Text';
    $contentURL = 'URL';
    // $canEdit = true;
  }
}

if (isset($_POST["pageContentID"]) && $_POST["pageContentID"] != 0) {
  // Placeholder... Slice some of this off where contentID not present when stable.
}

htmlStart('Edit Content');

debugSectionOut("Edit Content");
debugOut('$action', $action);
debugOut('$userID', $userID);
debugOut('$contentTitle', $contentTitle);
debugOut('$contentDescription', $contentDescription);
debugOut('$contentText', $contentText);
debugOut('$contentURL', $contentURL);
debugOut('$sql', $sql);
?>

<form action="contentView.php" method="post" name="contentViewForm">
  <table id="contentViewTable">

    <tr>
      <td>ID:</td>
      <td>
        <?php
        if (!isset($_POST["pageContentID"]) || $_POST["pageContentID"] == '' || $_POST["pageContentID"] == 0) {
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
      <td>Description:</td>
      <td><textarea name="contentDescription" rows="5" cols="80"
        <?php if ($contentDescription == '') {
          echo 'required placeholder="Description" id="inputContentDescription"></textarea>';
        } else {
          echo ' id="inputContentDescription">' . $contentDescription . '</textarea>';
        } ?>
      </td>
    </tr>
    <tr>
      <td>Text:</td>
      <td><textarea name="contentText" rows="5" cols="80"
        <?php if ($contentText == '') {
          echo 'required placeholder="Content Text" id="inputContentText"></textarea>';
        } else {
          echo ' id="inputContentText">' . $contentText . '</textarea>';
        } ?>
      </td>
    </tr>
    <tr>
      <td>URL:</td>
      <td><textarea name="contentURL" rows="5" cols="80"
        <?php if ($contentURL == '') {
          echo 'required placeholder="Fully Qualified URL (i.e. http://www.example.com)"></textarea>';
        } else {
          echo ' id="inputContentURL">' . $contentURL . '</textarea>';
        } ?>
      </td>
    </tr>
    <tr>
      <td></td>
      <td></td>
    </tr>
    <tr>
      <td></td>
      <td><?php
        if (!isset($_POST["pageContentID"]) || $_POST["pageContentID"] == '' || $_POST["pageContentID"] == 0) {
          echo '<input type="submit" class="btn btn-primary" name="insert" value=" Add Content " id="inputid1" />';
        } else {
          if (isset($canEdit) && ($canEdit)) {
            echo '<input type="submit" class="btn btn-primary" name="update" value="Save Changes" id="inputid1" /> ';
          }
          echo '<input type="button" class="btn btn-default" name="cancel" value="   Cancel   " onClick="window.location=\'./content.php\';" />';
        }
        ?>
      </td>
    </tr>
  </table>
</form>
<br/>

<?php
/*
if ($_POST["pageContentID"] == 0) {
  echo '<form>';
  echo '<select name="tagCategory" id="tagCategory">';
    $sql = 'SELECT DISTINCT tagCategoryID, tagCategory FROM vTag';
    $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
      while ($rows = mysqli_fetch_array($result)) {
        $tagCategoryID = $rows['tagCategoryID'];
        $tagCategory = $rows['tagCategory'];
        if ($tagCategory == $tagCategory) {
          echo '<option selected="selected" value="' . $tagCategoryID . '">' . $tagCategory . '</option>';
        } else {
          echo '<option value="' . $tagCategoryID . '">' . $tagCategory . '</option>';
        }
      }
  echo '</select>';
  
  if($tagCategory) {
    echo '<select name="tag" id="tag">';
    $sql = 'SELECT DISTINCT tagID, tag FROM vTag WHERE ';
    $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
    while ($rows = mysqli_fetch_array($result)) {
      $tagCategoryID = $rows['tagCategoryID'];
      $tagCategory = $rows['tagCategory'];
      if ($tagCategory == $tagCategory) {
        echo '<option selected="selected" value="' . $tagCategoryID . '">' . $tagCategory . '</option>';
      } else {
        echo '<option value="' . $tagCategoryID . '">' . $tagCategory . '</option>';
      }
    }
    echo '</select>';
  
  }

}
*/
?>

<!-- Here we should conditionally (if editing) add or remove tags. -->
<?php
if (isset($_POST["pageContentID"]) && ($_POST["pageContentID"] > 0)) {
  include 'divContentTagsEdit.php';
  include 'divContentTags.php';
}
?>

</body>
</html>

































