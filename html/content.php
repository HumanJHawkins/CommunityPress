<?php
include 'pageHeader.php';
htmlStart('Stories and Lessons');
?>

<div class="container">
  <?php include 'divButtonGroupMain.php'; ?>
  <br />
  <?php include 'divV4LBanner.php'; ?>
  <br />
  <?php
  if ($_SESSION['isContentEditor'] || $_SESSION['isSuperuser']) {
    echo '<a href="contentEdit.php" class="btn btn-primary">&nbsp;&nbsp;&nbsp;New...&nbsp;&nbsp;&nbsp;</a><br />';
  }
  ?>
    <br/>
  <?php include 'divContentGrid.php'; ?>
</div>
</body>
</html>

