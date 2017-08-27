<?php
include_once 'sessionStart.php';
if(!isset($connection)) {
  $connection = getDBConnection();
}
?>

<div id="contentGrid">
  <table id="contentView" class="table table-striped table-bordered table-hover table-condensed table-responsive sortable">
    <thead>
      <tr>
        <th data-defaultsign="AZ" width="1%">ID</th>
        <th data-defaultsign="AZ">Title</th>
        <th data-defaultsign="AZ">URL</th>
        <th data-defaultsign="AZ" width="1%">Actions</th>
        <th data-defaultsign="AZ" width="1%">Update By</th>
        <th data-defaultsign="month" width="1%">Update Date</th>
      </tr>
    </thead>
    <tfoot>
      <tr>
        <th>ID</th>
        <th>Title</th>
        <th>URL</th>
        <th>Actions</th>
        <th>Update By</th>
        <th>Update Date</th>
      </tr>
    </tfoot>
    <tbody>
    <?php
    if(isset($_SESSION['userID']) && ($_SESSION['userID'] > 0)) {
      $sql = "CALL procViewContent(0," . $_SESSION['userID'] . ")";
    } else {
      $sql = "CALL procViewContent(0,0)";
    }
    
    if (!$connection->multi_query($sql)) {
      debugOut("$connection->errno", $connection->errno, true);
      debugOut("$connection->error", $connection->error, true);
    }
    
    do {
      if ($result = $connection->store_result()) {
        while ($row = $result->fetch_assoc()) {
          echo
            '<tr>' .
            '<td data-value="1">' . $row['contentID'] . '</td>' .
            '<td data-value="2">' . $row['contentTitle'] . '</td>' .
            '<td data-value="3"><a href="' . $row['contentURL'] . '">' . $row['contentURL'] . '</td>' .
            '<td data-value="4">';
          
          if ($row['canEdit']) {
            echo
              '<div style="white-space:nowrap;"><a href="./contentView.php?action=edit&pageContentID=' . $row['contentID'] .
              '" class="btn btn-default btn-xs">&nbsp;&nbsp;Edit&nbsp;&nbsp;</a>&nbsp;' .
              '<a href="./contentView.php?action=delete&pageContentID=' . $row['contentID'] .
              '" class="btn btn-default btn-xs" onclick="return confirm(\'Are you sure you wish to delete this Record?\');">Delete</a>';
          } else {
            echo
              '<div style="white-space:nowrap;text-align:center;"><a href="./contentView.php?action=edit&pageContentID=' . $row['contentID'] .
              '" class="btn btn-default btn-xs">&nbsp;&nbsp;View&nbsp;&nbsp;</a>&nbsp;';
          }
          
          echo '</div>' .
            '</td>' .
            '<td data-value="5">' .
            '<div style="white-space: nowrap;">' .
            $row['updateBy'] .
            '</div>' .
            '</td>' .
            '<td data-value="6">' .
            '<div style="white-space: nowrap;">' .
            $row['updateTime'] .
            '</div>' .
            '</td>' .
            '</tr>';
        }
        $result->free();
      } else {
        if ($connection->errno) {
          debugOut("$connection->errno", $connection->errno, true);
          debugOut("$connection->error", $connection->error, true);
        }
      }
    } while ($connection->more_results() && $connection->next_result());
    
    
    ?>
    </tbody>
  </table>
</div>