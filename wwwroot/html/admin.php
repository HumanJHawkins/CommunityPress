<?php
include 'sessionStart.php';
htmlStart('Site Admin');
?>

<div class="container">
    <div class="btn-group btn-group-justified">
        <?php
        if ($_SESSION['isTagEditor'] || $_SESSION['isSuperuser']) {
            echo '<a href="tagEdit.php" class="btn btn-default">Add or Edit Tags</a>';
        }

        if ($_SESSION['isSiteAdmin'] || $_SESSION['isSuperuser']) {
            echo '<a href="./userEdit.php" class="btn btn-default">Edit Users</a>';
            echo '<a href="devInfo.php" class="btn btn-default">DevInfo (Temp)</a>';
        }

        ?>
    </div>
    <br/>
</div>

</body>
</html>
