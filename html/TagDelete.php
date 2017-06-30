<?php
ob_start();
include("DBConnect.php");
if(isset($_GET['ContentTagID'])!="")
{
    $delete=$_GET['ContentTagID'];

    $delete=mysqli_query($connection,"DELETE FROM ContentTag WHERE biContentTagID='$delete'");
    if($delete)
    {
        header("Location:TagEdit.php");
    }
    else
    {
        echo mysqli_error($connection);
    }
} else {
    echo "Get didn't get";
}
ob_end_flush();
?>


