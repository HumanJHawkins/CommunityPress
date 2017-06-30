<hr/>
<table id="ContentTagView" class="WideOutput">
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
    include('DBConnect.php');
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

