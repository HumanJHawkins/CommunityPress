<?php
include 'pageHeader.php';

$connection = getMySQLiConnection();

// Action determined from GET directly, else via POST. Will be:
//  Update or Insert (Same function): Data is set, so update DB.
//  Edit:   Load an existing tag for editing.
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

// tagID for Edit/Delete comes from $_GET. For Update and insert comes from $_POST.
if ((isset($_GET["pageTagID"])) && ($_GET["pageTagID"] > 0)) {
  $pageTagID = $_GET["pageTagID"];
} else if ((isset($_POST["tagID"])) && ($_POST["tagID"] > 0)) {
  $pageTagID = $_POST["tagID"];
} else {
  $pageTagID = 0;
}

// UserID needed for validating permission to edit.
if ((isset($_SESSION["userID"])) && ($_SESSION["userID"] > 0)) {
  $userID = $_SESSION["userID"];
} else {
  $userID = 0;
}

if ((isset($_POST["tag"])) && ($_POST["tag"] != '')) {
  $tag = trim(mysqli_real_escape_string($connection, $_POST["tag"]));
} else {
  $tag = '';
}

/*
if ((isset($_POST["tagCategory"])) && ($_POST["tagCategory"] != '')) {
  $tagCategory = trim(mysqli_real_escape_string($connection, $_POST["tagCategory"]));
} else {
  $tagCategory = '';
}
*/
if ((isset($_POST["tagCategory"])) && ($_POST["tagCategory"] != '')) {
  $_POST["tagCategory"] = trim(mysqli_real_escape_string($connection, $_POST["tagCategory"]));
}


if ((isset($_POST["tagDescription"])) && ($_POST["tagDescription"] != '')) {
  $tagDescription = trim(mysqli_real_escape_string($connection, $_POST["tagDescription"]));
} else {
  $tagDescription = '';
}

// Set variables for input form and continue to display.
$sql = '';
if ($action == 'delete') {
  $sql = 'SELECT TagDelete(\'' . $pageTagID . '\', \'' . $userID . '\')';
  
  // Our redirect to last requested URL will cause a failure due to the $_GET paramaters passed in. So, strip them.
  $_SESSION['lastURL'] = strtok($_SESSION['lastURL'], '?');
  
} else if ($action == 'insert') {
  $sql = 'SELECT TagInsert("' . $tag . '",' . $_POST["tagCategory"] . ',"' . $tagDescription . '","' . $userID . '")';
} else if ($action == 'update') {
  $sql = 'SELECT TagUpdate(' . $pageTagID . ',"' . $tag . '",' . $_POST["tagCategory"] . ',"' . $tagDescription . '","' . $userID . '")';
}

// If we have SQL at this point, we are updating the DB via stored function. So run SQL and exit.
if ($sql != '') {
  $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
  header('Location: ' . $_SESSION['lastURL']);
  exit();
}

// If we are still here, we are displaying the tag edit screen... So, if editing,
//  load the tag to edit. Otherwise just continue with defaults.
if ($action == 'edit') {
  $sql = 'SELECT tag, tagCategory, tagDescription FROM vTag WHERE tagID = ' . $pageTagID;
  $result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
  if ($userrow = mysqli_fetch_array($result)) {
    $tag = trim($userrow['tag']);
    $_POST["tagCategory"] = trim($userrow['tagCategory']);
    $tagDescription = trim($userrow['tagDescription']);
  } else {
    $tag = 'Tag';
    $_POST["tagCategory"] = 'Tag Category';
    $tagDescription = 'Tag Description.';
  }
}

if ($pageTagID != 0) {
  // Placeholder... Slice some of this off where tagID not present when done debugging.
}

htmlStart('Tag Edit');
?>

<div class="container">
  <div class="btn-group btn-group-justified">
    <?php
    if ($_SESSION['isTagEditor'] || $_SESSION['isSuperUser']) {
      echo '<a href="./tagEdit.php" class="btn btn-default">Add or Edit Tags</a>';
    }
    
    if ($_SESSION['isUserEditor'] || $_SESSION['isSuperUser']) {
      echo '<a href="./userEdit.php" class="btn btn-default">Edit Users</a>';
    }
    
    if ($_SESSION['isSiteDeveloper'] || $_SESSION['isSuperUser']) {
      echo '<a href="./devInfo.php" class="btn btn-default">DevInfo (Temp)</a>';
    }
    ?>
  </div>
  <br/>
</div>

<form action="tagEdit.php" method="post" name="tagEditForm">
  <input type="hidden" name="tagID" value="<?php echo $pageTagID ?>"/>
  <table id="tagEditTable">

    <tr>
      <td>Category:</td>
      <td>
        <?php
        tagCategorySelector($connection);
        ?>
      </td>
    </tr>
    <tr>
      <td>Tag:</td>
      <td><textarea name="tag" rows="1" cols="80"
        <?php
        if ($tag == '') {
          echo 'required placeholder="Tag" id="inputTag"></textarea>';
        } else {
          echo ' id="inputTag">' . $tag . '</textarea>';
        }
        ?>
      </td>
    </tr>
    <tr>
      <td>Description:</td>
      <td><textarea name="tagDescription" rows="5" cols="80"
        <?php if ($tagDescription == '') {
          echo 'required placeholder="Tag Description" id="inputTagDescription"></textarea>';
        } else {
          echo ' id="inputTagDescription">' . $tagDescription . '</textarea>';
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
        if ($pageTagID == 0) {
          echo '<input type="submit" class="btn btn-primary" name="insert" value=" Add Tag " id="inputid1" />';
        } else {
          echo '<input type="submit" class="btn btn-danger" name="update" value=" Update " id="inputid1" /> ';
          echo '<input type="button" class="btn btn-default" name="cancel" value=" Cancel " onClick="window.location=\'./tagEdit.php\';" />';
          echo '&nbsp;<span class="bg-danger">&nbsp;Careful. You are updating an existing tag, not adding a new one.&nbsp;</span>';
        }
        ?>
      </td>
    </tr>

  </table>
</form>
<br/>
<table id="tagView" class="table table-striped table-bordered table-hover table-condensed table-responsive sortable">
  <!--  -->

  <thead>
  <tr>
    <th data-defaultsign="AZ" width="1%">Category</th>
    <th data-defaultsign="AZ" width="1%">Tag</th>
    <th data-defaultsign="AZ">Description</th>
    <th data-defaultsign="AZ" width="1%">Actions</th>
    <th data-defaultsign="AZ" width="1%">Update By</th>
    <th data-defaultsign="month" width="1%">Update Date</th>
  </tr>
  </thead>
  <tfoot>
  <tr>
    <th>Category</th>
    <th>Tag</th>
    <th>Description</th>
    <th>Actions</th>
    <th>Update By</th>
    <th>Update Date</th>
  </tr>
  </tfoot>
  <tbody>
  <?php
  $select = mysqli_query($connection, "CALL procViewTags('')");
  
  while ($userrow = mysqli_fetch_array($select)) {
    $tagID = $userrow['tagID'];
    $tag = $userrow['tag'];
    $tagCategory = $userrow['tagCategory'];
    $tagDescription = $userrow['tagDescription'];
    $updateBy = $userrow['updateBy'];
    $updateTime = $userrow['updateTime'];
    echo
      '<tr>' .
      '<td data-value="1">' . $tagCategory . '</td>' .
      '<td data-value="2">' . $tag . '</td>' .
      '<td data-value="3">' . $tagDescription . '</td>' .
      '<td data-value="4">' .
      '<div style="white-space: nowrap;">' .
      '<a href="./tagEdit.php?action=edit&pageTagID=' . $tagID . '" class="btn btn-default btn-xs">&nbsp;&nbsp;Edit&nbsp;&nbsp;</a>&nbsp;' .
      '<a href="./tagEdit.php?action=delete&pageTagID=' . $tagID . '" class="btn btn-default btn-xs" onclick="return confirm(\'Are you sure you wish to delete this Record?\');">Delete</a>' .
      '</div>' .
      '</td>' .
      '<td data-value="5">' .
      '<div style="white-space: nowrap;">' .
      $updateBy .
      '</div>' .
      '</td>' .
      '<td data-value="6">' .
      '<div style="white-space: nowrap;">' .
      $updateTime .
      '</div>' .
      '</td>' .
      
      '</tr>';
  } ?>
  </tbody>
</table>

</body>
</html>
