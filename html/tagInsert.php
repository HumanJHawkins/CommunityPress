<?php
// ob_start();
include("dbConnect.php");
if ((isset($_POST['insert'])!="") || (isset($_POST['update'])!=""))
{
    $Tag=trim(mysqli_real_escape_string($connection,$_POST['Tag']));
    $TagDescription=trim(mysqli_real_escape_string($connection,$_POST['TagDescription']));

    if($_POST['TagID']==0) {
    // if (isset($_POST['insert'])!="") {
        $SQL = 'INSERT INTO Tag (Tag,TagDescription) VALUES ("' . $Tag . '","' . $TagDescription . '")';
    } else {
        $SQL = 'UPDATE Tag SET Tag="'.$Tag . '",TagDescription="'.$TagDescription.'" WHERE TagID = '.$_POST['TagID'];
    }
    echo $SQL;
    $update=mysqli_query($connection,$SQL);

    if($update)
    {
        $msg="Successfully Updated!!";
        echo "<script type='text/javascript'>alert('$msg');</script>";
        header('Location:tagEdit.php');
    }
    else
    {
        $errormsg="Something went wrong, Try again";
        echo "<script type='text/javascript'>alert('$errormsg');</script>";
    }
}
// ob_end_flush();
?>