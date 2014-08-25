<?php
    define('VALID_PAGE', true);
    require("common.php");
    require("lib/password.php");

    $db->commonCode();

    $reset_key = $_GET["reset_key"];
    $user = $_GET["user"];
    $password_token = $_GET["password_token"];


    if(!empty($_POST))
    {
        $reset_echo = true;
        $reset_successful = false;
        $reset_key = $_POST['reset_key'];
        $user = $_POST['user'];
        $password_token = $_POST['password_token'];

        $response_stmt = $db->prepare('
            SELECT
                user
                , secret
                , request_timestamp
            FROM
                responses
            WHERE
                reset_key = :reset_key
                AND user = :user
                AND NOT used
                AND active
            ');

        $response_stmt->execute(array(
            ':reset_key' => $reset_key,
            ':user' => $user
        ));

        $response = $response_stmt->fetch();

        if($response)
        {
            $created = DateTime::createFromFormat('Y-m-d G:i:s', $response['request_timestamp']);
            if ( $created >= new DateTime('30 minutes ago') )
            {
                if ( password_verify($password_token, $response['secret']) )
                {
                    //disable used token

                    $disable_token_stmt = $db->prepare('
                        UPDATE responses
                        SET
                            used = :used
                        WHERE
                            reset_key = :reset_key
                            AND user = :user
                    ');
                    $disable_token_stmt->execute(array(
                        ':used' => '1'
                        ,':reset_key' => $reset_key
                        ,':user' => $user
                    ));

                    //change the password
                    $password_change_stmt = $db->prepare('
                        UPDATE users
                        SET
                            password = :password
                        WHERE
                            user_id = :user_id
                        ');

                    $hash = password_hash($_POST['password'], PASSWORD_BCRYPT, array("cost" => 10));

                    $password_change_stmt->execute(array(
                        ':password' => $hash,
                        ':user_id' => $response['user']
                    ));

                    $reset_successful = true;
                }
            }
        }
    }
    
    $given_slugs = $db->giveSlugs(array('forgot password', 'password reset success', 'password reset fail', 'new password', 'password reset submit'));
    
?> <html>
    <head>
        <?php $db->commonCodeHead(); ?>
    </head>
    <body>
        <?php $db->commonCodeUpperBody(); ?>
        <h1><?php echo html_escape($given_slugs['slugs']['forgot password'][$db->giveLangName()]);?></h1>
        <?php $db->SystemMessage();
            if ($reset_echo) {
                if ($reset_successful) {
                    echo html_escape($given_slugs['slugs']['password reset success'][$db->giveLangName()]);
                } else {
                    echo html_escape($given_slugs['slugs']['password reset fail'][$db->giveLangName()]);
                }
            }
        ?>
        <form action="password_reset.php" method="post">
            <?php echo html_escape($given_slugs['slugs']['new password'][$db->giveLangName()]);?>:
                <input type="text" name="password" value="" />
                <br /><br />
            <input type="hidden" name="reset_key" value="<?php echo $reset_key; ?>" />
            <input type="hidden" name="user" value="<?php echo $user; ?>" />
            <input type="hidden" name="password_token" value="<?php echo $password_token; ?>" />
            <input type="submit" value="<?php echo html_escape($given_slugs['slugs']['password reset submit'][$db->giveLangName()]);?>" />
        </form>
    </body>
</html>
