<?php
// ob_start();
include("DBConnect.php");
if ((isset($_POST['insert'])!="") || (isset($_POST['update'])!=""))
{
    $ContentTag=trim(mysqli_real_escape_string($connection,$_POST['vchContentTag']));
    $ContentTagDescription=trim(mysqli_real_escape_string($connection,$_POST['txtContentTagDescription']));

    if($_POST['ContentTagID']==0) {
    // if (isset($_POST['insert'])!="") {
        $SQL = 'INSERT INTO ContentTag (vchContentTag, txtContentTagDescription) VALUES ("' . $ContentTag . '","' . $ContentTagDescription . '")';
    } else {
        $SQL = 'UPDATE ContentTag SET vchContentTag="'.$ContentTag . '", txtContentTagDescription="'.$ContentTagDescription.'" WHERE biContentTagID = '.$_POST['ContentTagID'];
    }
    echo $SQL;
    $update=mysqli_query($connection,$SQL);

    if($update)
    {
        $msg="Successfully Updated!!";
        echo "<script type='text/javascript'>alert('$msg');</script>";
        header('Location:TagEdit.php');
    }
    else
    {
        $errormsg="Something went wrong, Try again";
        echo "<script type='text/javascript'>alert('$errormsg');</script>";
    }
}
// ob_end_flush();
?>