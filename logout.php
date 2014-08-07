<?php

    // First we execute our common code to connection to the database and start the session
    require("common.php");

    // We remove the user's data from the session
    unset($_SESSION['user']);
    unset($_SESSION['action_token']);

    // We redirect them to the login page
    header("Location: login.php");
    exit;
