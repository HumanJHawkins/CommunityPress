<?php

include_once 'sessionStart.php';
$connection = getDBConnection();

$sql = 'SELECT DISTINCT tagID, tag FROM vTag WHERE tagCategoryID=' . $_GET['tagCatID'];
$result = mysqli_query($connection, $sql) or die("<br />Error: " . $sql . '<br />' . mysqli_error($connection));
$tags = array();
while ($rows = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
  $tags[] = $rows;
}
header('Content-type: application/json');
echo json_encode($tags);
