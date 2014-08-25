<?php
    define('VALID_PAGE', true);
    
    // First we execute our common code to connection to the database and start the session
    require("common.php");

    require("lib/password.php");
    require("lib/functions.php");

    $db->commonCode(true);

    // This if statement checks to determine whether the edit form has been submitted
    // If it has, then the account updating code is run, otherwise the form is displayed
    if(!empty($_POST))
    {
        if (isset($_POST['action_token']) && isset($_SESSION['action_token']) && $_POST['action_token'] === $_SESSION['action_token'])
        {
            $db->editAccount($_POST);
        }
        else
        {
            echo 'invalid submission';
            trigger_error('possible CSRF attack', E_USER_ERROR);    // add details for logging like the user ID, the referrer (as a possible source of the attack) etc.
            exit;
        }
    }
    $given_slugs = $db->giveSlugs(array('edit account', 'username', 'email', 'new password', 'new password', 'edit acc new password notice', 'edit acc old password notice', 'update account'));
?>
<html>
    <head>
        <?php $db->commonCodeHead(); ?>
    </head>
    <body>
        <?php $db->commonCodeUpperBody(); ?>
        <h1><?php echo html_escape($given_slugs['slugs']['edit account'][$db->giveLangName()]);?></h1>
        <?php $db->SystemMessage(); ?>
        <form action="edit_account.php" method="post">
            <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">
            <?php echo html_escape($given_slugs['slugs']['username'][$db->giveLangName()]);?>:<br />
                <b><?php echo html_escape($_SESSION['user']['username']); ?></b>
                <br /><br />
            <?php echo html_escape($given_slugs['slugs']['email'][$db->giveLangName()]);?>:<br />
                <input type="text" name="email" value="<?php echo html_escape($_SESSION['user']['email']); ?>" />
                <br /><br />
            <?php echo html_escape($given_slugs['slugs']['new password'][$db->giveLangName()]);?>:<br />
                <input type="password" name="new_password" value="" /><br />
                <input type="password" name="new_password_repeat" value="" /><br />
                <i>(<?php echo html_escape($given_slugs['slugs']['edit acc new password notice'][$db->giveLangName()]);?>)</i>
                <br /><br />
            <?php echo html_escape($given_slugs['slugs']['edit acc old password notice'][$db->giveLangName()]);?>
            <br>
                <input type="password" name="old_password" value="" /><br />
                <br><br>
                <input type="submit" value="<?php echo html_escape($given_slugs['slugs']['update account'][$db->giveLangName()]);?>" />
        </form>
    </body>
<html>
