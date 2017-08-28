<?php
include 'pageHeader.php';
htmlStart('Site Admin');
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

</body>
</html>
