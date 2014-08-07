<?php

    require("common.php");   
    require("lib/rnum.php");
    require("lib/mail.php");
    require("lib/password.php");

    if(!empty($_POST))
    {
        $submitted_email = $_POST['email'];
        ///// begin recaptcha
        require_once('lib/recaptcha/recaptchalib.php');
        $privatekey = "6LdtJvgSAAAAAE3ZFqqAECgvdR1Of4rUCELCQ7KF";
        $resp = recaptcha_check_answer ($privatekey,
          $_SERVER["REMOTE_ADDR"],
          $_POST["recaptcha_challenge_field"],
          $_POST["recaptcha_response_field"]);

        if (!$resp->is_valid) {
          // What happens when the CAPTCHA was entered incorrectly
          die ("The reCAPTCHA wasn't entered correctly. Go back and try it again." .
               "(reCAPTCHA said: " . $resp->error . ")"); //change this if you want to edit the page, for example: $msg = "Captcha wasn't entered correctly"; and display it later in the script
        } else {
        ///// end recaptcha
            if(filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
            {

                $hash = hash('sha256', $_POST['email']);
                $time = new DateTime('24 hours ago');
                $time_formatted = $time->format('Y-m-d H:i:s');

                $count_stmt = $db->prepare('
                    SELECT COUNT(*)  as count
                    FROM sent_emails
                    WHERE email_address = :email_address AND timestamp >= :time
                ');
                $count_stmt->execute(array(
                    ':email_address' => $hash,
                    ':time' => $time_formatted
                ));

                $times = $count_stmt->fetch();
                $email = $_POST['email'];
                $user_stmt = $db->prepare('
                    SELECT
                        user_id
                    FROM
                        users
                    WHERE
                        email = :email
                ');
                $user_stmt->execute(array(
                    ':email' => $_POST['email']
                ));
                $user_id = $user_stmt->fetchColumn();
                if($user_id)  // is the mail of a user?
                {
                    if($times['count'] < 10)
                    {
                        $deactivation_stmt = $db->prepare('
                        UPDATE
                            responses
                        SET
                            active = 0
                        WHERE
                            user = :user
                        ');
                        $deactivation_stmt->execute(array(
                        ':user' => $user_id
                        ));

                        $password_token = rnum();

                        $query = "
                        INSERT INTO responses (
                            reset_key,
                            user,
                            secret,
                            request_timestamp,
                            request_ip
                        ) VALUES (
                            :reset_key,
                            :user,
                            :secret,
                            NOW(),
                            :request_ip
                        )";

                        $secret = password_hash($password_token, PASSWORD_BCRYPT, array("cost" => 10));
                        $reset_key = rnum();
                        $request_ip = getenv('REMOTE_ADDR');

                        $query_params = array(
                            ':reset_key' => $reset_key,
                            ':user' => $user_id,
                            ':secret' => $secret,
                            ':request_ip' => $request_ip
                        );

                        $stmt = $db->prepare($query);
                        $result = $stmt->execute($query_params);

                        $mail_to      = $email;
                        $mail_subject = 'Forgot password';
                        $mail_body = "Hallo,
                            <br><br>
                            you or somebody else requested a password reset for your user account at http://domain.com/.
                            <br><br>
                            To set a new password, please visit this link:
                            <br><br>
                            http://www.domain.com/password_reset.php?reset_key=" . $reset_key . "&user=" . $user_id . "&password_token=" . $password_token ."
                            <br><br>
                            Do not share the secret code in this link until you've used it. The code will expire in 30 minutes.
                            <br><br>
                            If the request was not from you, simply ignore this email. Your password will _not_ be changed.
                            <br><br>
                            Do you have further questions? Please contact us at info@domain.com.
                            <br><br>
                            Best regards,
                            <br><br>
                            domain.com";


                        if(mail_f ($mail_to, $mail_subject, $mail_body) == 1)
                        {
                            $new_stmt = $db->prepare('
                            INSERT INTO sent_emails (
                            email_address,
                            timestamp
                            ) VALUES (
                            :email_address,
                            NOW()
                            )');
                            $new_stmt->execute(array(
                            ':email_address' => $hash
                            ));
                        }
                    }
                }
                else{ // is the mail not in the system
                    if($times['count'] < 1)
                    {
                        $email_adress = $_POST["email"];
                        $hash = hash('sha256', $email_adress);
                        # the following is for an unregistered address that hasn't reached its request limit yet

                        # you only need one query
                        $unsub_data_stmt = $db->prepare('
                            SELECT
                                unsubscribed
                                , email_key
                            FROM
                                unsubscribed_email_addresses
                            WHERE
                                email_address = :hash
                        ');
                        $unsub_data_stmt->execute(array(
                            ':hash' => $hash
                        ));
                        $unsub_data = $unsub_data_stmt->fetchColumn();

                        // If we don't have a record of the address yet, or if the address isn't unsubscribed,
                        // send an email; in case of a new record, generate a new token, otherwise, use the old one;
                        // $valid_token determines whether the newly generated token has been stored and can actually
                        // be used; if not, it shouldn't be in the mail
                        $send_mail = $valid_token = false;
                        if ( $unsub_data === false )
                        {
                            $send_mail = true;
                            $unsub_token = rnum();
                            $unsubscribe_stmt = $db->prepare('
                                INSERT INTO unsubscribed_email_addresses (
                                    email_address
                                    , email_key
                                ) VALUES (
                                    :email_key
                                    , :email_address
                                )
                            ');
                            $valid_token = $unsubscribe_stmt->execute(array(
                                ':email_address' => $hash
                                , ':email_key' => $unsub_token
                            ));
                        }
                        elseif ( !$unsub_data['unsubscribed'] )
                        {
                            $send_mail = $valid_token = true;
                            $unsub_token = $unsub_data['email_key'];
                        }

                        if ( $send_mail )
                        {
                            $email_adress = $_POST["email"];
                            $mail_subject = "Forgot password";
                            $mail_body = "Hallo,
                                <br><br>
                                you or somebody else entered your email address into the password reset form at http://domain.com, but your address is not registered in our system.
                                <br><br>
                                If you have an account on our website, you must have used a different email address. Please try again with your other addresses.
                                <br><br>
                                If you did not use our form, we apologize for this email. Please ignore it. If you never want to receive the email again, you can mark your address as blocked in our system:
                                <br><br>
                                http://www.domain.com/no_mail.php?email_key=" . $unsub_token . "
                                <br><br>
                                Do you have further questions? Please contact us at info@domain.com.
                                <br><br>
                                Best regards,
                                <br><br>
                                domain.com";

                            # put the mail text into an external template; only append the token if $valid_token
                            if( mail_f($email_adress, $mail_subject, $mail_body) )
                            {
                                $sent_stmt = $db->prepare('
                                    INSERT INTO sent_emails (
                                        email_address
                                        , timestamp
                                    ) VALUES (
                                        :email_address
                                        , NOW()
                                    )
                                ');
                                $sent_stmt->execute(array(
                                    ':email_address' => $hash
                                ));
                            }
                        }
                    }
                }
                echo "Unless your limit has been reached, we'll send you an email";
            }
            else{
                echo "This emailadress is invalid!";
            }
        }
    }
?> <html>
    <body>
        <h1>Forgot password</h1>
        <form action="forgot_password.php" method="post">
            Email:
            <input type="text" name="email" value="<?php echo $submitted_email; ?>" />
            <br /><br />
            <?php
                require_once('lib/recaptcha/recaptchalib.php');
                $publickey = "6LdtJvgSAAAAAKIduRTSEBdOGyvyYw6xcL3J5R9J"; // you got this from the signup page
                echo recaptcha_get_html($publickey);
            ?>
            <input type="submit" value="Recover" />
        </form>
    </body>
</html>
