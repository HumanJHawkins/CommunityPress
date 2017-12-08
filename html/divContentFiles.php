<?php
include_once 'sessionStart.php';
consolidatePageContentID();
if (!isset($pdo)) {
  $pdo = getDBPDO();
}

$sql = 'CALL procGetContentFiles(?, ?)';
if ((isset($_SESSION['userID'])) && ($_SESSION['userID'] > 0)) {
  $sqlParamArray = [$_POST["pageContentID"], $_SESSION['userID']];
} else {
  $sqlParamArray = [$_POST["pageContentID"], 0];
}

$result = getOnePDOTable($pdo, $sql, $sqlParamArray, PDO::FETCH_ASSOC);
?>

<div id="contentFiles">
    <table class="table table-striped table-bordered table-hover table-condensed table-responsive sortable">
        <thead>
        <tr>
            <th data-defaultsign="AZ" width="1%">File ID</th>
            <th data-defaultsign="AZ">Filename</th>
            <th data-defaultsign="AZ">Size (MB)</th>
            <th data-defaultsign="AZ" width="1%">Action(s)</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($result as $index => $row) {
          if ($row['uploadFileID'] != $contentAvatarID) {
            echo '<tr>' . '<td data-value="1">' . $row['uploadFileID'] . '</td>' . '<td data-value="2">' .
                $row['uploadFileName'] . '</td>' . '<td data-value="3">' . bytesToMegabytes($row['uploadFileSize']) .
                '</td>' . '<td data-value="4">';
            echo '<div style="white-space:nowrap;">';
            // if (($row['canEdit']) && ($_SESSION['isContentEditor'] || $_SESSION['isSuperuser'])) {
            //   echo '<a href="./contentEdit.php?action=edit&pageContentID=' . $row['contentID'] .
            //       '" class="btn btn-default btn-xs">&nbsp;&nbsp;Edit&nbsp;&nbsp;</a>&nbsp;';
            // }
            echo '<a href="' . $row["uploadFilePath"] . '/' . $row["uploadFileID"] . '" download="' .
                $row["uploadFileName"] . '">Download</a>';

            echo '</div>' . '</td></tr>';
          }
        }
        ?>
        </tbody>
    </table>
</div>