<?php
  include_once 'sessionStart.php';
  $pdo = getDBPDO();
  $sql = 'SELECT DISTINCT tagID, tag FROM vTag WHERE tagCategoryID=?';
  $sqlParamsArray = [$_GET['tagCatID']];
  $result = getOnePDOTable($pdo, $sql, $sqlParamsArray);
  $tags = [];
  foreach ($result as $key => $value) {
    $tags[] = $value;
  }
  header('Content-type: application/json');
  echo json_encode($tags);
