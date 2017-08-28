<?php

include_once 'sessionStart.php';
if (!isset($connection)) {
  $connection = getDBConnection();
}
// procGetContentTags(theContentID BIGINT);
// Test w/ 100238... Should at least get createBy.

if ((isset($_SESSION['userID'])) && ($_SESSION['userID'] > 0)) {
  $sql = 'CALL procGetContentTags(' . $_POST['pageContentID'] . ', ' . $_SESSION['userID'] . ')';
} else {
  $sql = 'CALL procGetContentTags(' . $_POST['pageContentID'] . ', 0)';
}
if (!$connection->multi_query($sql)) {
  debugOut("$connection->errno", $connection->errno, true);
  debugOut("$connection->error", $connection->error, true);
}
$currentTagCategory = '';
echo '<div id="contentTags">';

do {
  if ($result = $connection->store_result()) {
    while ($row = $result->fetch_assoc()) {
      if ($currentTagCategory != $row['tagCategory']) {
        $currentTagCategory = $row['tagCategory'];
        
        echo '<br /><strong>' . $row['tagCategory'] . ':</strong> ';
    
        if ($row['canEdit']) {
          // First one has different punctuation, so keep inside the conditional
          echo '<a href = "" class="btn btn-default btn-xs" onclick="return confirm(\'Remove this tag?\');">' . $row['tag'] . '&nbsp;&#9745;</a>';
        } else {
          echo $row['tag'];
        }
    
    
      } else {
        if ($row['canEdit']) {
          // First one has different punctuation, so keep inside the conditional
          echo '&nbsp;<a href = "" class="btn btn-default btn-xs" onclick="return confirm(\'Remove this tag?\');">' . $row['tag'] . '&nbsp;&#9745;</a>';
        } else {
          echo ', ' . $row['tag'];
        }
      }
    }
    $result->free();
  } else {
    if ($connection->errno) {
      debugOut("$connection->errno", $connection->errno, true);
      debugOut("$connection->error", $connection->error, true);
    }
  }
} while ($connection->more_results() && $connection->next_result());

echo '</div>';

?>
