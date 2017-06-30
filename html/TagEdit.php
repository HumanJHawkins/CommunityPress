<?php
ob_start();
include("DBConnect.php");
if(isset($_GET['ContentTagID'])!="") {
    $ContentTagID = $_GET['ContentTagID'];
    $SQL = 'SELECT vchContentTag, txtContentTagDescription FROM ContentTag WHERE biContentTagID = ' . $ContentTagID;
    $Edit = mysqli_query($connection, $SQL);
}

if($userrow=mysqli_fetch_array($Edit))
{
    // Already have $ContentTagID
    $ContentTag=trim($userrow['vchContentTag']);
    $ContentTagDescription=trim($userrow['txtContentTagDescription']);
} else
{
    $ContentTagID=0;
    $ContentTag='Enter New Tag';
    $ContentTagDescription='Enter Tag Description.';
}
ob_end_flush();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Tags</title>
    <link type="text/css" media="all" rel="stylesheet" href="v4lStyle.css">
</head>
<body>
    <form action="TagInsert.php" method="post" name="insertform">
        <input type="hidden" name="ContentTagID" value="<?php echo $ContentTagID ?>" />
        <table id="ContentTagInsert" width="598">
            <tr>
                <td width="1px">Tag: </td>
                <td><input type="text" name="vchContentTag"
                    <?php
                        if($ContentTagID == 0) {
                            echo 'required placeholder="'.$ContentTag;
                        } else {
                            echo 'value="'.$ContentTag;
                        }
                    ?>
                    " id="inputid" style="width: 80ch"/></td>
            <tr>
                <td>Description: </td>
                <td><textarea name="txtContentTagDescription" rows="5" cols="80"
                    <?php
                    if($ContentTagID == 0) {
                        echo ' required placeholder="'.$ContentTagDescription.'" id="inputid"></textarea>';
                    } else {
                        echo ' id="inputid">'.$ContentTagDescription.'</textarea>';
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
                    if($ContentTagID == 0) {
                        echo '<input type="submit" name="insert" value=" Add Tag " id="inputid1" />';
                    } else {
                        echo '<input type="submit" name="update" value=" Update " id="inputid1" />';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </form>
    <br />
    <hr/>
    <table id="ContentTagView" class="WideOutput">
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
        $select=mysqli_query($connection,"SELECT * FROM ContentTag ORDER BY vchContentTag");
        // $i=1;
        while($userrow=mysqli_fetch_array($select))
        {
            $ContentTagID=$userrow['biContentTagID'];
            $ContentTag=$userrow['vchContentTag'];
            $ContentTagDescription=$userrow['txtContentTagDescription'];
            $Create=$userrow['tsCreate'];
            $Update=$userrow['tsUpdate'];
            echo
                '<tr>'.
                '<td>'.$ContentTag.'</td>'.
                '<td>'.$ContentTagDescription.'</td>'.
                '<td><span><a href="TagEdit.php?ContentTagID='.$ContentTagID.'" class="button">&nbsp;&nbsp;Edit&nbsp;&nbsp;</a></span></td>'.
                '<td><span><a href="TagDelete.php?ContentTagID='.$ContentTagID.'" class="button" onclick="return '.
                'confirm(\'Are you sure you wish to delete this Record?\');">&nbsp;Delete&nbsp;</a></span></td>'.
                '<td>'.$Create.'</td>'.
                '<td>'.$Update.'</td>'.
                '</tr>';
        } ?>
    </tbody>
</table>

</body>
</html>


