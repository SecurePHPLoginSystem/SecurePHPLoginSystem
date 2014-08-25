<?php
    define('VALID_PAGE', true);
    
    require("common.php");   
    require("lib/rnum.php");
    require("lib/mail.php");
    require("lib/password.php");
    
    $db->commonCode();
    
    $submitted_email = '';
    if(!empty($_POST))
    {
        $submitted_email = $_POST['email'];
        $db->forgotPassword($_POST);
    }
    
    $given_slugs = $db->giveSlugs(array('forgot password', 'email', 'recover password'))
?> <html>
    <head>
        <?php $db->commonCodeHead(); ?>
    </head>
    <body>
        <?php $db->commonCodeUpperBody(); ?>
        <h1><?php echo html_escape($given_slugs['slugs']['forgot password'][$db->giveLangName()]);?></h1>
        <?php $db->SystemMessage(); ?>
        <form action="forgot_password.php" method="post">
            <?php echo html_escape($given_slugs['slugs']['email'][$db->giveLangName()]);?>:
            <input type="text" name="email" value="<?php echo $submitted_email; ?>" />
            <br /><br />
            <?php $db->giveReCaptcha(); ?>
            <input type="submit" value="<?php echo html_escape($given_slugs['slugs']['recover password'][$db->giveLangName()]);?>" />
        </form>
    </body>
</html>
