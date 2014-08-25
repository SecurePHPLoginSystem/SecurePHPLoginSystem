<?php
    define('VALID_PAGE', true);
    
    require("common.php");
    require("lib/password.php");
    require('lib/functions.php');
    
    $db->commonCode();
    
    $given_slugs = $db->giveSlugs(array('register', 'username', 'email', 'password'));
    
    $submitted_username = "";
    $submitted_email = "";

    if(!empty($_POST))
    {
        $submitted_username = $_POST['username'];                   // if there has been filled in something, not regarding the password, then we'll have to place that back into the form, so that the user doesn't have to fill in all the data again.
        $submitted_email = $_POST['email'];
        
        $db->register($_POST);
    }

?>

<html>
    <head>
        <?php $db->commonCodeHead(); ?>
    </head>
    <body>
        <?php $db->commonCodeUpperBody(); ?>
        <h1><?php echo html_escape($given_slugs['slugs']['register'][$db->giveLangName()]);?></h1>
        <?php $db->SystemMessage(); ?>
        <form action="register.php" method="post">
            <?php echo html_escape($given_slugs['slugs']['username'][$db->giveLangName()]);?>:
                <input type="text" name="username" value="<?php echo html_escape($submitted_username);?>" />
            <br /><br />
            <?php echo html_escape($given_slugs['slugs']['email'][$db->giveLangName()]);?>:
                <input type="text" name="email" value="<?php echo html_escape($submitted_email);?>" />
            <br /><br />
            <?php echo html_escape($given_slugs['slugs']['password'][$db->giveLangName()]);?>:
                <input type="password" name="password" value="" />
            <br /><br />
            <?php $db->giveReCaptcha(); ?>
            <input type="submit" value="<?php echo html_escape($given_slugs['slugs']['register'][$db->giveLangName()]);?>" />
        </form>
    </body>
</html>
