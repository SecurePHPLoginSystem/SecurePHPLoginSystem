<?php
    require("common.php");
    require("lib/password.php");

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
?> <html>
    <body>
        <h1>Forgot password</h1>
        <?php
            if ($reset_echo) {
                if ($reset_successful) {
                    echo 'Your password has been reset!';
                } else {
                    echo 'This token has already been used, expired or inactive, please request a new one';
                }
            }
        ?>
        <form action="password_reset.php" method="post">
            New password:
                <input type="text" name="password" value="" />
                <br /><br />
            <input type="hidden" name="reset_key" value="<?php echo $reset_key; ?>" />
            <input type="hidden" name="user" value="<?php echo $user; ?>" />
            <input type="hidden" name="password_token" value="<?php echo $password_token; ?>" />
            <input type="submit" value="Login" />
        </form>
    </body>
</html>
