<?php

    require("common.php");
    require("lib/password.php");
    require('lib/functions.php');
    $submitted_username = "";
    $submitted_email = "";

    if(!empty($_POST))
    {
        // recaptcha
        require_once('lib/recaptcha/recaptchalib.php');
        $resp = recaptcha_check_answer (RECAPTCHA_PRIVATE_KEY,
          $_SERVER["REMOTE_ADDR"],
          $_POST["recaptcha_challenge_field"],
          $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
          // What happens when the CAPTCHA was entered incorrectly
          die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
               "(reCAPTCHA said: " . $resp->error . ")");
        } else {
          // Your code here to handle a successful verification


        // end recaptcha
            if(!empty($_POST['username']))
            {
                $submitted_username = $_POST['username'];                   // is there has been filled in something, not regarding the password, then we'll have to place that back into the form, so that the user doesn't have to fill in all the data again.
                if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                {
                    $submitted_email = $_POST['email'];
                }
                if(!empty($_POST['password']))
                {
                    if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                    {
                        $submitted_email = $_POST['email'];
                        $user_exist_stmt = $db->prepare('
                            SELECT
                                1
                            FROM users
                            WHERE
                                username = :username
                        ');


                        $user_exist_stmt->execute(array(
                            ':username' => $_POST['username']
                        ));

                        $user_exist = $user_exist_stmt->fetch();

                        if(!$user_exist)
                        {
                            $email_exist_stmt = $db->prepare('
                                SELECT
                                    1
                                FROM users
                                WHERE
                                    email = :email
                            ');

                            $email_exist_stmt->execute(array(
                                ':email' => $_POST['email']
                            ));

                            $email_exist = $email_exist_stmt->fetch();

                            if(!$email_exist)
                            {

                                $register_stmt = $db->prepare('
                                INSERT INTO users (
                                    username,
                                    password,
                                    email,
                                    start_date
                                ) VALUES (
                                    :username,
                                    :password,
                                    :email,
                                    NOW()
                                )');
                                $hash = password_hash($_POST['password'], PASSWORD_BCRYPT, array("cost" => 10));

                                $register_stmt->execute(array(
                                    ':username' => $_POST['username'],
                                    ':password' => $hash,
                                    ':email' => $_POST['email']
                                ));

                                header("Location: login.php");
                                exit;
                            }
                            else {
                                $message = "This email address is already registered";
                            }
                        }
                        else {
                            $message = "This username is already in use";
                        }
                    }
                    else {
                        $message = "Invalid E-Mail Address";
                    }
                }
                else {
                    $message = "Please enter a password.";
                }
            }
            else {
                $message = "Please enter a username.";
            }
        }
    }

?>

<html>
    <body>
        <h1>Register</h1>
        <?php echo $message; ?>
        <form action="register.php" method="post">
            Username:
                <input type="text" name="username" value="<?php echo html_escape($submitted_username);?>" />
            <br /><br />
            E-Mail:
                <input type="text" name="email" value="<?php echo html_escape($submitted_email);?>" />
            <br /><br />
            Password:
                <input type="password" name="password" value="" />
            <br /><br />
            <?php
                require_once('lib/recaptcha/recaptchalib.php');
                echo recaptcha_get_html(RECAPTCHA_PUBLIC_KEY);
            ?>
            <input type="submit" value="Register" />
        </form>
    </body>
</html>
