<?php
include_once 'sessionStart.php';
consolidatePageContentID();
if (!isset($pdo)) {
    $pdo = getDBPDO();
}

$sql = 'CALL procGetContentTags(?, ?)';

if ((isset($_SESSION['userID'])) && ($_SESSION['userID'] > 0)) {
    $sqlParamArray = [$_POST["pageContentID"], $_SESSION['userID']];
} else {
    $sqlParamArray = [$_POST["pageContentID"], 0];
}

$currentTagCategory = '';
$result = getOnePDOTable($pdo, $sql, $sqlParamArray, PDO::FETCH_ASSOC);
echo '<div id="contentTags">';
foreach ($result as $key => $value) {
    if ($value['tag'] != '') {
        if ($currentTagCategory != $value['tagCategory']) {
            $currentTagCategory = $value['tagCategory'];
            echo '<br /><strong>' . $value['tagCategory'] . ':</strong> ';
            if ($value['canEdit']) {
                // First one has different punctuation, so keep inside the conditional
                echo '<button class="btn btn-default btn-xs" onclick="if(confirm(\'Remove this tag?\')){ fnTagDelete(' .
                    $_POST["pageContentID"] . ', ' . $value['tagID'] . ', ' . $_SESSION['userID'] . ');}">' . $value['tag'] .
                    '&nbsp;&#9745;</button>';
            } else {
                echo $value['tag'];
            }
        } else {
            if ($value['canEdit']) {
                // First one has different punctuation, so keep inside the conditional
                echo '&nbsp;<button class="btn btn-default btn-xs" onclick="if(confirm(\'Remove this tag?\')) {fnTagDelete(' .
                    $_POST["pageContentID"] . ', ' . $value['tagID'] . ', ' . $_SESSION['userID'] . ');}">' . $value['tag'] .
                    '&nbsp;&#9745;</button>';
            } else {
                echo ', ' . $value['tag'];
            }
        }
    }
}
echo '</div>';
?>

<script>
    // $_POST["theTagged"], $_POST["theTag"], $_POST["theUser"]
    function fnTagDelete(taggedID, tagID, userID) {
        if (taggedID > 0 && tagID > 0 && userID > 0) {
            var httpRequest = new XMLHttpRequest();
            var url = "fnTagAttachmentEdit.php";
            var params = "theTagged=" + taggedID + "&theTag=" + tagID + "&theUser=" + userID + "&action=remove";
            alert(params);
            httpRequest.open("POST", url, true);
            httpRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");

            httpRequest.onreadystatechange = function () {
                if (httpRequest.readyState === 4 && httpRequest.status === 200) {
                    // TO DO: This is creating a race condition. Asynchronous DB call may not be completed before
                    //   page reload. Negative ramifications likely rare and less painful than making this synchronous.
                    //   So, in the backlog to fix for now.
                    location.reload(true);
                }
            };

            httpRequest.send(params);
        }
    }

</script>
