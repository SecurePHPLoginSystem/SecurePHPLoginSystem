<?php

    // First we execute our common code to connection to the database and start the session
    require("common.php");

    require("lib/password.php");
    require("lib/functions.php");

    // At the top of the page we check to see whether the user is logged in or not
    if(empty($_SESSION['user']))
    {
        // If they are not, we redirect them to the login page.
        header("Location: login.php");

        // Remember that this die statement is absolutely critical.  Without it,
        // people can view your members-only content without logging in.
        exit;
    }

    // This if statement checks to determine whether the edit form has been submitted
    // If it has, then the account updating code is run, otherwise the form is displayed
    if(!empty($_POST))
    {
        if (isset($_POST['action_token']) && isset($_SESSION['action_token']) && $_POST['action_token'] === $_SESSION['action_token'])
        {
            $password_check_stmt = $db->prepare('
                SELECT password
                FROM users
                WHERE user_id = :user_id
            ');
            $password_check_stmt->execute(array(
                ':user_id' => $_SESSION['user']['user_id']
            ));
            $password_check = $password_check_stmt->fetchcolumn();

            if(password_verify($_POST['old_password'], $password_check)) {
                // Make sure the user entered a valid E-Mail address
                if(!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL))
                {
                    exit;
                }

                // If the user is changing their E-Mail address, we need to make sure that
                // the new value does not conflict with a value that is already in the system.
                // If the user is not changing their E-Mail address this check is not needed.
                if($_POST['email'] != $_SESSION['user']['email'])
                {
                    // Define our SQL query
                    $email_check_stmt = $db->prepare('
                        SELECT
                            1
                        FROM users
                        WHERE
                            email = :email
                    ');

                    // Define our query parameter values
                    $email_check_stmt->execute(array(
                        ':email' => $_POST['email']
                    ));


                    // Retrieve results (if any)
                    $email_check = $email_check_stmt->fetch();
                    if($email_check)
                    {
                        die("This E-Mail address is already in use");
                    }
                }

                // If the user entered a new password, we need to hash it and generate a fresh salt
                // for good measure.
                if(!empty($_POST['new_password']) && !empty($_POST['new_password_repeat']))
                {
                    if($_POST['new_password'] === $_POST['new_password_repeat']) {
                        $password = password_hash($_POST['new_password'], PASSWORD_BCRYPT, array("cost" => 10));
                    } else {
                        die("The two passwords didn't match");
                    }

                }
                else
                {
                    // If the user did not enter a new password we will not update their old one.
                    $password = null;
                }

                // Initial query parameter values
                $query_params = array(
                    ':email' => $_POST['email'],
                    ':user_id' => $_SESSION['user']['user_id']
                );

                // If the user is changing their password, then we need parameter values
                // for the new password hash and salt too.
                if($password !== null)
                {
                    $query_params[':password'] = $password;
                }

                // Note how this is only first half of the necessary update query.  We will dynamically
                // construct the rest of it depending on whether or not the user is changing
                // their password.
                $query = "
                    UPDATE users
                    SET
                        email = :email
                ";

                // If the user is changing their password, then we extend the SQL query
                // to include the password and salt columns and parameter tokens too.
                if($password !== null)
                {
                    $query .= "
                        , password = :password
                    ";
                }

                // Finally we finish the update query by specifying that we only wish
                // to update the one record with for the current user.
                $query .= "
                    WHERE
                        user_id = :user_id
                ";


                    // Execute the query
                    $stmt = $db->prepare($query);
                    $result = $stmt->execute($query_params);



                // Now that the user's E-Mail address has changed, the data stored in the $_SESSION
                // array is stale; we need to update it so that it is accurate.
                $_SESSION['user']['email'] = $_POST['email'];

                // This redirects the user back to the members-only page after they register
                header("Location: private.php");

                // Calling die or exit after performing a redirect using the header function
                // is critical.  The rest of your PHP script will continue to execute and
                // will be sent to the user if you do not die or exit.
                exit;
            } else {
                die("The password you entered was incorrect.");
            }
        }
        else
        {
            echo 'invalid submission';
            trigger_error('possible CSRF attack', E_USER_ERROR);    // add details for logging like the user ID, the referrer (as a possible source of the attack) etc.
            exit;
        }
    }

?>
<html>
    <head>
        <link href="style.css" rel="stylesheet" type="text/css" />
    </head>
    <body>
        <h1>Edit account</h1>
        <form action="edit_account.php" method="post">
            <input type="hidden" name="action_token" value="<?php echo html_escape($_SESSION['action_token']) ?>">
            Username:<br />
                <b><?php echo html_escape($_SESSION['user']['username']); ?></b>
                <br /><br />
            E-Mail Address:<br />
                <input type="text" name="email" value="<?php echo html_escape($_SESSION['user']['email']); ?>" />
                <br /><br />
            New Password:<br />
                <input type="password" name="new_password" value="" /><br />
                <input type="password" name="new_password_repeat" value="" /><br />
                <i>(leave blank if you do not want to change your password)</i>
                <br /><br />
            To submit the new settings, repeat your <i>current (old)</i> password.<br>
                <input type="password" name="old_password" value="" /><br />
                <br><br>
                <input type="submit" value="Update Account" />
        </form>
    </body>
<html>
