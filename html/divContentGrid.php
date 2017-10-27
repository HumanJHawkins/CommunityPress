<?php
include_once 'sessionStart.php';
if (!isset($pdo)) {
  $pdo = getDBPDO();
}
?>

<div id="contentGrid">
  <table id="contentEdit"
         class="table table-striped table-bordered table-hover table-condensed table-responsive sortable">
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
    if (isset($_SESSION['userID']) && ($_SESSION['userID'] > 0)) {
      $sql = "CALL procViewContent(0," . $_SESSION['userID'] . ")";
    } else {
      $sql = "CALL procViewContent(0,0)";
    }
    
    $result = getOnePDOTable($pdo, $sql);
    foreach ($result as $key => $value) {
      echo
        '<tr>' .
        '<td data-value="1">' . $value['contentID'] . '</td>' .
        '<td data-value="2">' . $value['contentTitle'] . '</td>' . '<td data-value="3"><a href="' .
        $value['contentSummary'] . '">' . $value['contentSummary'] . '</td>' .
        '<td data-value="4">';

      if (($value['canEdit']) && ($_SESSION['isContentEditor'] || $_SESSION['isSuperuser'])) {
        echo
          '<div style="white-space:nowrap;"><a href="./contentEdit.php?action=edit&pageContentID=' . $value['contentID'] .
          '" class="btn btn-default btn-xs">&nbsp;&nbsp;Edit&nbsp;&nbsp;</a>&nbsp;' .
          '<a href="./contentEdit.php?action=delete&pageContentID=' . $value['contentID'] .
          '" class="btn btn-default btn-xs" onclick="return confirm(\'Are you sure you wish to delete this Record?\');">Delete</a>&nbsp;';
      }

      echo '<div style="white-space:nowrap;text-align:center;"><a href="./contentView.php?&pageContentID=' .
            $value['contentID'] .
          '" class="btn btn-default btn-xs">&nbsp;&nbsp;View&nbsp;&nbsp;</a>&nbsp;';
      
      echo '</div>' .
        '</td>' .
        '<td data-value="5">' .
        '<div style="white-space: nowrap;">' .
        $value['updateBy'] .
        '</div>' .
        '</td>' .
        '<td data-value="6">' .
        '<div style="white-space: nowrap;">' .
        $value['updateTime'] .
        '</div>' .
        '</td>' .
        '</tr>';
    }
    
    
    ?>
    </tbody>
  </table>
</div>
