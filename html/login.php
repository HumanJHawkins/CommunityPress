<!--
  Login method based on:
  https://www.w3schools.com/howto/tryit.asp?filename=tryhow_css_login_form_modal
-->

<?php
include 'sessionStart.php';

// If returning here after adding a user, we'll have the password temporarily stored
//  in a session variable. Move to POST and delete, to allow handling login of the new user.
if ((isset($_SESSION["Password"])) && ($_SESSION["Password"] != '')) {
    $_POST["username"] = $_SESSION["UserName"];
    $_POST["password"] = $_SESSION["Password"];
    $_SESSION["Password"] = '';
    unset($_SESSION["Password"]);
}

$_SESSION["UserID"]         = 0;
$_SESSION["UserName"]       = '';
$_SESSION["UserBirthday"]   = NULL;
$_SESSION["UserMail"]       = '';
$_SESSION["UserReputation"] = 0;
$_SESSION["UserStatus"]     = 0;

$PasswordValid = false;

if ((isset($_POST["username"])) && (trim($_POST["username"]) != '')) {
    $sql = 'CALL procGetUserByName(\'' . mysqli_real_escape_string($connection, trim($_POST["username"])) . '\')';
    $result = mysqli_query($connection, $sql) or die("Error: " . $sql . PHP_EOL . mysqli_error($connection));

//   if($result) {
//        // Should just be one username, but need to loop to avoid 'Commands out of sync' error.
//        while ($row = $result->fetch_object()){
//            $UserNames[] = $row;
//            echo '<br>'.$UserNames[0];
//        }
//        // Free result set
//        $result->close();
//        $connection->next_result();
//    }

    echo '<br>Have username: '.$_POST["username"];

    if ((isset($_POST["password"])) && ($_POST["password"] != '')) {

        echo '<br>Have password: '.substr($_POST["password"],0,2).'********';

        if($row=mysqli_fetch_array($result)) {

            echo '<br>Fetched result.';



     //       if ($result = mysqli_store_result($link)) {
     //           while ($row = mysqli_fetch_row($result)) {
     //               printf("%s\n", $row[0]);
     //           }
     //           mysqli_free_result($result);
     //       }


            if (password_verify($_POST["password"], $row['PasswordSaltHash'])) {

                echo '<br>Verified password.';

                $PasswordValid = true;  // Probably not going to use this...
                // $_SESSION["PasswordSaltHash"] = $row['PasswordSaltHash'];    // Not likely needed elsewhere.
                $_SESSION["UserID"]           = $row['UserID'];
                $_SESSION["UserName"]         = $row['UserName'];
                $_SESSION["UserBirthday"]     = $row['Birthday'];
                $_SESSION["UserMail"]         = $row['eMail'];
                $_SESSION["UserReputation"]   = $row['Reputation'];
                $_SESSION["UserStatus"]       = $row['Status'];

                // Update session log with user ID now that we have it.
                $sql = 'SELECT fnLogSession(\''.session_id().'\', \''.$_SESSION['ipAddress'].'\','.$_SESSION["UserID"].')';
                $result = mysqli_query($connection2, $sql) or die("Error: " . $sql. '<br>' . mysqli_error($connection2));

                // User is validated. Redirect to wherever they were bounced here from.
                header('Location: ' . $_SESSION['LastRequestedURL']);
                exit();
            } else {
                // Handle invalid password.

                echo '<br>Password didn\'t match.';

            }
        } else {

            echo '<br>User does not exist.';

            // Handle User does not exist.
            $SaltHash = password_hash($_POST["password"], PASSWORD_DEFAULT,["cost" => 8]);

            // To Do: Probably don't need to escape the hash... Check and remove if safe.
            $sql = 'SELECT fnAddUser(\'' . $_POST["username"] . '\',\''.mysqli_real_escape_string($connection, $SaltHash).'\')';
            $result = mysqli_query($connection3, $sql) or die('<br>Error: ' .  mysqli_error($connection3). '<br>SQL was: ' .$sql);
            if ($row = mysqli_fetch_array($result)) {
                // User added. So, bounce back to login with the previously entered user and pass,
                //  allowing login function to handle variables and redirect.
                $_SESSION["UserName"] = $_POST["username"];
                $_SESSION["Password"] = $_POST["password"];
                header('Location: ' . 'login.php');
                exit();
            } else {
                echo "<br>No result returned. Handle error.";
            }
         }
    } else {
        // Handle had user, but no password. Equivalent to invalid password.
    }
} else {
    // Handle non-input (just display the form.
}

htmlStart('Login or Register',false);
?>
<body onload="document.getElementById('id01').style.display='block'">
<!-- <button onclick="document.getElementById('id01').style.display='block'" style="width:auto;">Login</button> -->
<div id="id01" class="modal">
    <form class="modal-content animate" action="/login.php" method="post">
        <div class="imgcontainer">
            <span onclick="document.getElementById('id01').style.display='none'" class="close" title="Close Modal">&times;</span>
            <img src="./image/circle.png" alt="Avatar" class="avatar">
        </div>

        <div class="container">
            <label><b>Username</b></label>
            <input type="text" placeholder="Enter Username" name="username" required>

            <label><b>Password</b></label>
            <input type="password" placeholder="Enter Password" name="password" required>

            <button type="submit" class="button btnLogin">Login (or Register)</button>
            <input type="checkbox" checked="checked"> Remember me
        </div>

        <div class="container" style="background-color:#f1f1f1">
            <button type="button" onclick="document.getElementById('id01').style.display='none'" class="cancelbtn">Cancel</button>
            <span class="psw">Forgot <a href="#">password?</a></span>
        </div>
    </form>
</div>

</body>
</html>
