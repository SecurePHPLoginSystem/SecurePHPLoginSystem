<?php
    define('VALID_PAGE', true);
    
    // First we execute our common code to connection to the database and start the session
    require("common.php");

    $db->commonCode();
    
    $slugs = $db->giveSlugs(array('logout_message'));
    $_SESSION['system_message'] .= $slugs['slugs']['logout_message'][$db->giveLangName()];
    
    // We remove the user's data from the session
    unset($_SESSION['user']);
    unset($_SESSION['action_token']);

    // We redirect them to the login page
    header("Location: login.php");
    exit;
