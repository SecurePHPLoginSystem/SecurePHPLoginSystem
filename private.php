<?php

    require("common.php"); 

    if(empty($_SESSION['user']))
    {
        header("Location: login.php");
        exit;
    }
?>
login succeded, congrats!
<br><br>
From here on the files beginning with this code:

require("common.php");

if(empty($_SESSION['user']))
{
    header("Location: login.php");
    exit;
}

will be protected by the script, note this piece of code has to be on EVERY page you want to protect. This code is also visable in the file 'private.php'
