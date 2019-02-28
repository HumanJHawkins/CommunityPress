<?php
include 'sessionStart.php';
if (!isset($pdo)) {
    $pdo = getDBPDO();
}

$sql = 'SELECT DISTINCT tagID, tag FROM vTag WHERE tagCategoryID=?';
$sqlParamsArray = [$_GET['tagCatID']];
$result = getOnePDOTable($pdo, $sql, $sqlParamsArray, PDO::FETCH_ASSOC);
$tags = [];
foreach ($result as $key => $value) {
    $tags[] = $value;
}
header('Content-type: application/json');
echo json_encode($tags);
