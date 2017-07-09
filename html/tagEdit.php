<?php include 'pageHeader.php';

if(isset($_GET['TagID'])!="") {
    $TagID = $_GET['TagID'];
    $Action = $_GET['Action'];

    if($Action=='Delete') {
        $delete=mysqli_query($connection,"DELETE FROM Tag WHERE TagID='$TagID'");
        if($delete) {
            header("Location:tagEdit.php");
        } else {
            echo mysqli_error($connection);
        }
    } else {
        $SQL = 'SELECT Tag, TagDescription FROM Tag WHERE TagID = ' . $TagID;
        $Edit = mysqli_query($connection, $SQL);
    }
}

if($userrow=mysqli_fetch_array($Edit))
{
    // Already have $TagID
    $Tag=trim($userrow['Tag']);
    $TagDescription=trim($userrow['TagDescription']);
} else
{
    $TagID=0;
    $Tag='Enter New Tag';
    $TagDescription='Enter Tag Description.';
}
// ob_end_flush();
htmlStart('Edit Metadata');
?>
    <form action="tagInsert.php" method="post" name="insertform">
        <input type="hidden" name="TagID" value="<?php echo $TagID ?>" />
        <table id="TagInsert" width="598">
            <tr>
                <td width="1px">Tag: </td>
                <td><input type="text" name="Tag"
                    <?php
                        if($TagID == 0) {
                            echo 'required placeholder="'.$Tag;
                        } else {
                            echo 'value="'.$Tag;
                        }
                    ?>
                    " id="inputid" style="width: 80ch"/></td>
            <tr>
                <td>Description: </td>
                <td><textarea name="TagDescription" rows="5" cols="80"
                    <?php
                    if($TagID == 0) {
                        echo ' required placeholder="'.$TagDescription.'" id="inputid"></textarea>';
                    } else {
                        echo ' id="inputid">'.$TagDescription.'</textarea>';
                    }
                    ?>
                    </td>
            </tr>
            <tr>
                <td></td><td></td>
            </tr>
            <tr>
                <td></td>
                <td><?php
                    if($TagID == 0) {
                        echo '<input type="submit" name="insert" value=" Add Tag " id="inputid1" />';
                    } else {
                        echo '<input type="submit" name="update" value=" Update " id="inputid1" /> ';
                        echo '<input type="button" name="cancel" value=" Cancel " onClick="window.location=\'tagEdit.php\';" />';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </form>
    <br />
    <hr/>
    <table id="TagView" class="WideOutput">
        <col width="60">
        <col>
        <col width="60">
        <col width="60">
        <col width="190">
        <col width="190">
        <thead>
        <tr>
            <th width="60px">Tag</th>
            <th>Description</th>
            <th width="60px"></th>
            <th width="60px"></th>
            <th width="190px">Creation Date</th>
            <th width="190px">Update Date</th>
        </tr>
        </thead>
        <tfoot>
        <tr>
            <th>Tag</th>
            <th>Description</th>
            <th></th>
            <th></th>
            <th>Creation Date</th>
            <th>Update Date</th>
        </tr>
        </tfoot>
        <tbody>
        <?php
        $select=mysqli_query($connection,"SELECT * FROM Tag WHERE TagID > 0 ORDER BY Tag");

         while($userrow=mysqli_fetch_array($select))
        {
            $TagID=$userrow['TagID'];
            $Tag=$userrow['Tag'];
            $TagDescription=$userrow['TagDescription'];
            $CreateDate=$userrow['CreateDate'];
            $UpdateDate=$userrow['UpdateDate'];
            echo
                '<tr>'.
                '<td>'.$Tag.'</td>'.
                '<td>'.$TagDescription.'</td>'.
                '<td><span><a href="tagEdit.php?Action=Edit&TagID='.$TagID.'" class="buttongrid">&nbsp;&nbsp;Edit&nbsp;&nbsp;</a></span></td>'.
                '<td><span><a href="tagEdit.php?Action=Delete&TagID='.$TagID.'" class="buttongrid" onclick="return '.
                'confirm(\'Are you sure you wish to delete this Record?\');">&nbsp;Delete&nbsp;</a></span></td>'.
                '<td>'.$CreateDate.'</td>'.
                '<td>'.$UpdateDate.'</td>'.
                '</tr>';
        } ?>
    </tbody>
</table>

</body>
</html>


