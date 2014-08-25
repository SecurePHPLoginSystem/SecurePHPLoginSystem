<?php
    defined('VALID_PAGE') or die('You are not authorized to view this page.');
    

/**
 * This is the database class. It extends the PDO class and adds some functions to it.
 *
 * @author ricardo
 */
class DBC extends PDO{
    private $domain = ''; // change this to your domain name
    private $language = 0; // this will be english by default
    private $logged_in_redirect = 'private.php'; // this should be the path from the root on so that it URL will be: $domain . $logged_in_page
    private $allow_ReCaptcha = true; // turn to false if you don't want ReCaptcha on your site.
    private $recaptcha_private_key = '6LfQ9PgSAAAAAOPKblkit6re5QGM4CEPgrZGx5I8'; // you got this from the signup page
    private $recaptcha_public_key = '6LfQ9PgSAAAAAM7nsh-CJbRsco6YCvPXJOQAu4tN'; // you got this from the signup page
    private $allow_language = true; // turn to false if you want to go your own way with the language parts
    private $allow_language_change = true; // turn to false if you want to keep a language setting, but don't want the user to change to an other language.
    /* to disable the language system:
     *          1) turn this to false
     *          2) write your text in the files if you want text to show up. 
     * To disable a specific language, delete it from the 'languages' table. 
     * To add a new language
     *          1) add it to the 'languages' table.
     *          2) add the column named after the language (same as the value in the 'languages' table) in the 'slugs' table.
     */
    
    /* The common codes. These are the codes that will be on the top of every page (accessable by the user). */
    public function commonCode($private = false) {
        // this code will be ran on top of each page
        if($private) {
            $this->loggedIn();
        }
        
        if(empty($_SESSION['system_message'])) {
            $_SESSION['system_message'] = '';
        }
        if(empty($_SESSION['language']) || $_SESSION['language'] < 0) {
            $_SESSION['language'] = 0;
        }
    }
    public function commonCodeHead() {
        // This code will be ran in the head of each page. 
        // This might include your style sheet. 
        // To change the default head code, just add / edit the code below.
        echo '<link href="' . $this->giveDomain() . 'style.css" rel="stylesheet" type="text/css" />';
        
    }
    
    # commonCodeUpperBody will be ran first in the body of each page. You could use this to implement Google Analytics
    public function commonCodeUpperBody() {
        if($this->giveAllowLanguage()) {
            echo $this->changeLangForm();
        }
    }
    
    # loggedIn redirects the user to login.php if he isn't logged in
    private function loggedIn() {
        if(empty($_SESSION['user']))
        {
            header("Location: " . $this->giveDomain() . "login.php");
            exit;
        }
    }
    
    # checkLoggedIn checks if a user is logged in or not.
    public function checkLoggedIn() {
        if(!empty($_SESSION['user'])) {
            return true;
        } else {
            return false;
        }
    }
    # SystemMessage echoes the system message
    public function SystemMessage() {
        if(!empty($_SESSION['system_message'])) {
            echo $_SESSION['system_message']; 
            unset($_SESSION['system_message']);
        }
    }
    
    
    /* The functions that will give the values of variables. */
    public function giveDomain() {
        return $this->domain;
    }
    public function giveAllowReCaptcha() {
        return $this->allow_ReCaptcha;
    }
    public function giveAllowLanguage() {
        return $this->allow_language;
    }
    public function giveAllowLanguageChange() {
        return $this->allow_language_change;
    }
    public function giveLoggedInRedirect() {
        return $this->logged_in_redirect;
    }
    
    
    /* The language functions. These will make up the language part of the website. */
    # changeLangForm gives the form that will be used to change the language of the website
    public function changeLangForm() {
        if($this->giveAllowLanguage() && $this->giveAllowLanguageChange()) {
            $form = '<form action="' . $this->giveDomain() . 'change_lang.php" method="post">
                        <input type="hidden" name="ref_file" value="' . html_escape($_SERVER['REQUEST_URI']) . '">';
            foreach($this->langNames() as $lang) {
                $lang_names[] = $lang['name'];
            }
            $slugs = $this->giveSlugs($lang_names);

            foreach($this->langNames() as $lang) {
                $form .= '<button type="submit" class="flag_' . $slugs['slugs'][$lang['name']][$this->giveLangName()] . '" name="action" value="' . $lang['lang_id'] . '">' . $slugs['slugs'][$lang['name']][$this->giveLangName()] . '</button>';
            }
            $form .= '</form>';
            return $form;
        }
    }
    
    # isLang controls if the given language ID is a legit one, and supported by the system.
    public function isLang($language, $name = false) {
        if($this->giveAllowLanguage()) {
            if($name) {
                $lang_names = $this->langNames(true);
            } else {
                $lang_names = $this->langNames();
            }
            if(!empty($lang_names[$language])) {
                return $lang_names[$language];
            } else {
                return false;
            }
        }
    }
    
    # langNames gives the names of all the supported languages.
    public function langNames($names = false) {
        if($this->giveAllowLanguage()) {
            require_once($this->giveDomain() . 'lib/functions.php');

            $lang_names_stmt = $this->prepare('
                SELECT *
                FROM languages
            ');
            $lang_names_stmt->execute();
            $lang_names_raw = $lang_names_stmt->fetchAll();
            if($names) {
                $lang_names = assoc($lang_names_raw, 'name');
            } else {
                $lang_names = assoc($lang_names_raw, 'lang_id');
            }
            return $lang_names;
        }
    }
    
    # giveLangID gives the language ID that the user chose, or didn't choose.
    private function giveLangID() {
        if($this->giveAllowLanguage()) {
            if($this->checkLoggedIn()) {
                $language_stmt = $this->prepare('
                    SELECT lang
                    FROM users
                    WHERE user_id = :user_id
                ');
                $language_stmt->execute(array(
                    ':user_id' => $_SESSION['user']['user_id']
                ));
                $language = $language_stmt->fetchColumn();
            } else {
                $language = $_SESSION['language'];
            }

            if(!empty($language) && $this->isLang($language) && $this->giveAllowLanguageChange()) {
                return $language;
            } else {
                return $this->language; // gives the language id
            }
        }
    }
    
    # giveLangName gives the language name the user chose, or didn't choose
    public function giveLangName() {
        if($this->giveAllowLanguage()) {
            $language = $this->isLang($this->giveLangID());
            return $language['name'];
        }
    }
    
    # giveSlugs gives the translation of each slug/id given. The use might look like this: $givenSlugs['slugs'][$slug_name][$db->giveLangName()]
    public function giveSlugs($slugs=false, $ids=false) {
        if($this->giveAllowLanguage()) {
            require_once($this->giveDomain() . 'lib/functions.php');
            if(!empty($slugs)) { // this can be used to translate small words which are produced by the system. For example 'Male' and 'Female'.
                $fetched_slugs_stmt = $this->prepare('
                    SELECT *
                    FROM slugs
                    WHERE slug IN ('. implode(',', array_fill(0, count($slugs), '?')) .')
                ');
                $fetched_slugs_stmt->execute($slugs);
                $fetched_slugs['slugs'] = assoc($fetched_slugs_stmt->fetchAll(), 'slug');
            }
            if(!empty($ids)) { // this can be used for individual translations without a original slug
                $fetched_slugs_stmt = $this->prepare('
                    SELECT *
                    FROM slugs
                    WHERE slug_id IN ('. implode(',', array_fill(0, count($slugs), '?')) .')
                ');
                $fetched_slugs_stmt->execute($slugs);
                $fetched_slugs['ids'] = assoc($fetched_slugs_stmt->fetchAll(), 'ids');
            }
            return $fetched_slugs;
        }
    }
    
    /* The ReCaptcha functions */
    # handleReCaptcha will be called when ReCaptcha should be handled, for example after registration
    private function handleReCaptcha($recaptcha_challenge_field, $recaptcha_response_field) {
        if($this->giveAllowReCaptcha()) {
            // recaptcha
            require_once('lib/recaptcha/recaptchalib.php');
            $resp = recaptcha_check_answer ($this->recaptcha_private_key,
                $_SERVER["REMOTE_ADDR"],
                $recaptcha_challenge_field,
                $recaptcha_response_field);

            if (!$resp->is_valid) {
                // What happens when the CAPTCHA was entered incorrectly
                $given_slugs = $this->giveSlugs(array('ReCaptcha fail'));
                $_SESSION['system_message'] .= $given_slugs['slugs']['ReCaptcha fail'][$this->giveLangName()] . $resp->error . ")";
                return false;
            } else {
                return true;
            }
        } else {
            return true;
        }
    }
    
    # giveReCaptcha will give the picture and the input field
    public function giveReCaptcha() {
        if($this->giveAllowReCaptcha()) {
            require_once($this->giveDomain() . 'lib/recaptcha/recaptchalib.php');
            echo recaptcha_get_html($this->recaptcha_public_key);
        }
    }
    /* From here on all the functions will be for the login system. */
    
    # login will control the user's input, logs the user in based on the input and redirects him to private.php
    public function login($username, $password) {
        $user_information_stmt = $this->prepare('
            SELECT
                user_id,
                username,
                password,
                email
            FROM users
            WHERE
                username = :username
        ');


        $user_information_stmt->execute(array(
            ':username' => $username
        ));

        $login_ok = false;

        $row = $user_information_stmt->fetch();
        if($row)
        {
            if ( password_verify($password, $row['password']) )
            {
                $login_ok = true;
            }
        }
        
        $given_slugs = $this->giveSlugs(array('login fail'));
        if($login_ok)
        {
            unset($row['password']);
            $_SESSION['user'] = $row;
            $_SESSION['action_token'] = generate_secure_token();

            header("Location: " . $this->giveDomain() . $this->giveLoggedInRedirect());
            exit;
        }
        else
        {
            $_SESSION['system_message'] .= $given_slugs['slugs']['login fail'][$this->giveLangName()] . "<br>";
        }
    }
    
    # register will control the user's input, registers the user and redirects him to the login page
    public function register($post) {
        $username = $post['username'];
        $email = $post['email'];
        $password = $post['password'];
        if(!empty($post['recaptcha_challenge_field'])) {
            $recaptcha_challenge_field = $post['recaptcha_challenge_field'];
        } else {
            $recaptcha_challenge_field = '';
        }
        if(!empty($post['recaptcha_response_field'])) {
            $recaptcha_response_field = $post['recaptcha_response_field'];
        } else {
            $recaptcha_response_field = '';
        }
        if($this->handleReCaptcha($recaptcha_challenge_field, $recaptcha_response_field)) {
            $given_slugs = $this->giveSlugs(array('empty username', 'existing username', 'empty password', 'invalid email', 'existing email'));
            
            $register = true;
            if(empty($username))
            {
                $_SESSION['system_message'] .= $given_slugs['slugs']['empty username'][$this->giveLangName()] . "<br>";
                $register = false;
            } else {
                $user_exist_stmt = $this->prepare('
                    SELECT
                        1
                    FROM users
                    WHERE
                        username = :username
                ');


                $user_exist_stmt->execute(array(
                    ':username' => $username
                ));

                $user_exist = $user_exist_stmt->fetch();

                if($user_exist)
                {
                    $_SESSION['system_message'] = $given_slugs['slugs']['existing username'][$this->giveLangName()] . "<br>";
                    $register = false;
                }
            }
            
            if(empty($password))
            {
                $_SESSION['system_message'] .= $given_slugs['slugs']['empty password'][$this->giveLangName()] . "<br>";
                $register = false;
            }
            
            if(!filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $_SESSION['system_message'] .= $given_slugs['slugs']['invalid email'][$this->giveLangName()] . "<br>";
                $register = false;
            } else {
                $email_exist_stmt = $this->prepare('
                    SELECT
                        1
                    FROM users
                    WHERE
                        email = :email
                ');

                $email_exist_stmt->execute(array(
                    ':email' => $email
                ));

                $email_exist = $email_exist_stmt->fetch();

                if($email_exist)
                {
                    $_SESSION['system_message'] = $given_slugs['slugs']['existing email'][$this->giveLangName()] . "<br>";
                    $register = false;
                }
            }
            if($register) {
                $hash = password_hash($password, PASSWORD_BCRYPT, array("cost" => 10));
                
                $register_stmt = $this->prepare('
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
                
                $register_stmt->execute(array(
                    ':username' => $username,
                    ':password' => $hash,
                    ':email' => $email
                ));

                header("Location: " . $this->giveDomain() . "login.php");
                exit;
            }
        }
    }
    
    # forgot_password will control the user's input and sends a mail to the submitted email address
    public function forgotPassword($post) {
        require_once($this->giveDomain() . 'lib/functions.php');
        require_once($this->giveDomain() . 'lib/rnum.php');
        require_once($this->giveDomain() . 'lib/mail.php');
        require_once($this->giveDomain() . 'lib/password.php');
        
        $email = $post['email'];
        if(!empty($post['recaptcha_challenge_field'])) {
            $recaptcha_challenge_field = $post['recaptcha_challenge_field'];
        } else {
            $recaptcha_challenge_field = '';
        }
        if(!empty($post['recaptcha_response_field'])) {
            $recaptcha_response_field = $post['recaptcha_response_field'];
        } else {
            $recaptcha_response_field = '';
        }
        
        if($this->handleReCaptcha($recaptcha_challenge_field, $recaptcha_response_field)) {
            $given_slugs = $this->giveSlugs(array('forgot password mail success', 'invalid email'));
            if(filter_var($email, FILTER_VALIDATE_EMAIL))
            {
                $hash = hash('sha256', $email);
                $time = new DateTime('24 hours ago');
                $time_formatted = $time->format('Y-m-d H:i:s');

                $count_stmt = $this->prepare('
                    SELECT COUNT(*)  as count
                    FROM sent_emails
                    WHERE email_address = :email_address AND timestamp >= :time
                ');
                $count_stmt->execute(array(
                    ':email_address' => $hash,
                    ':time' => $time_formatted
                ));

                $times = $count_stmt->fetch();
                $user_stmt = $this->prepare('
                    SELECT
                        user_id
                    FROM
                        users
                    WHERE
                        email = :email
                ');
                $user_stmt->execute(array(
                    ':email' => $email
                ));
                $user_id = $user_stmt->fetchColumn();
                if($user_id)  // is the mail of a user?
                {
                    if($times['count'] < 10)
                    {
                        $deactivation_stmt = $this->prepare('
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

                        $stmt = $this->prepare($query);
                        $stmt->execute($query_params);

                        $mail_to      = $email;
                        $mail_subject = 'Forgot password';
                        $mail_body = "Hallo,
                            <br><br>
                            you or somebody else requested a password reset for your user account at http://domain.com/.
                            <br><br>
                            To set a new password, please visit this link:
                            <br><br>
                            http://www.domain.com/password_reset.php?reset_key=" . html_escape($reset_key) . "&user=" . html_escape($user_id) . "&password_token=" . html_escape($password_token) ."
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
                            $new_stmt = $this->prepare('
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
                        $email_adress = $email;
                        $hash = hash('sha256', $email_adress);
                        # the following is for an unregistered address that hasn't reached its request limit yet

                        # you only need one query
                        $unsub_data_stmt = $this->prepare('
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
                            $unsubscribe_stmt = $this->prepare('
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
                            $email_adress = $email;
                            $mail_subject = "Forgot password";
                            $mail_body = "Hallo,
                                <br><br>
                                you or somebody else entered your email address into the password reset form at http://domain.com, but your address is not registered in our system.
                                <br><br>
                                If you have an account on our website, you must have used a different email address. Please try again with your other addresses.
                                <br><br>
                                If you did not use our form, we apologize for this email. Please ignore it. If you never want to receive the email again, you can mark your address as blocked in our system:
                                <br><br>
                                http://www.domain.com/no_mail.php?email_key=" . html_escape($unsub_token) . "
                                <br><br>
                                Do you have further questions? Please contact us at info@domain.com.
                                <br><br>
                                Best regards,
                                <br><br>
                                domain.com";

                            # put the mail text into an external template; only append the token if $valid_token
                            if( mail_f($email_adress, $mail_subject, $mail_body) )
                            {
                                $sent_stmt = $this->prepare('
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
                $_SESSION['system_message'] .= $given_slugs['slugs']['forgot password mail success'][$this->giveLangName()] . "<br>";
            }
            else{
                $_SESSION['system_message'] .= $given_slugs['slugs']['invalid email'][$this->giveLangName()] . "<br>";
            }
        }
    }
    
    # edit account will control the user's input and edit the user's data based on the input
    public function editAccount($post) {
        $given_slugs = $this->giveSlugs(array('invalid email', 'existing email'));
        $password_check_stmt = $this->prepare('
            SELECT password
            FROM users
            WHERE user_id = :user_id
        ');
        $password_check_stmt->execute(array(
            ':user_id' => $_SESSION['user']['user_id']
        ));
        $password_check = $password_check_stmt->fetchcolumn();

        if(password_verify($post['old_password'], $password_check)) {
            // Make sure the user entered a valid E-Mail address
            if(!filter_var($post['email'], FILTER_VALIDATE_EMAIL))
            {
                $_SESSION['system_message'] .= $given_slugs['slugs']['invalid email'][$this->giveLangName()] . "<br>";
                header('Location: ' . $this->giveDomain() . 'edit_account.php');
                exit;
            }

            // If the user is changing their E-Mail address, we need to make sure that
            // the new value does not conflict with a value that is already in the system.
            // If the user is not changing their E-Mail address this check is not needed.
            if($post['email'] != $_SESSION['user']['email'])
            {
                // Define our SQL query
                $email_check_stmt = $this->prepare('
                    SELECT
                        1
                    FROM users
                    WHERE
                        email = :email
                ');

                // Define our query parameter values
                $email_check_stmt->execute(array(
                    ':email' => $post['email']
                ));


                // Retrieve results (if any)
                $email_check = $email_check_stmt->fetch();
                if($email_check)
                {
                    $_SESSION['system_message'] .= $given_slugs['slugs']['existing email'][$this->giveLangName()] . "<br>";
                    header('Location: ' . $this->giveDomain() . 'edit_account.php');
                    exit;
                }
            }

            // If the user entered a new password, we need to hash it and generate a fresh salt
            // for good measure.
            if(!empty($post['new_password']) && !empty($post['new_password_repeat']))
            {
                if($post['new_password'] === $post['new_password_repeat']) {
                    $password = password_hash($post['new_password'], PASSWORD_BCRYPT, array("cost" => 10));
                } else {
                    $_SESSION['system_message'] .= $given_slugs['slugs']['unmatching passwords'][$this->giveLangName()] . "<br>";
                    header('Location: ' . $this->giveDomain() . 'edit_account.php');
                    exit;
                }

            }
            else
            {
                // If the user did not enter a new password we will not update their old one.
                $password = null;
            }

            // Initial query parameter values
            $query_params = array(
                ':email' => $post['email'],
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
            $stmt = $this->prepare($query);
            $stmt->execute($query_params);



            // Now that the user's E-Mail address has changed, the data stored in the $_SESSION
            // array is stale; we need to update it so that it is accurate.
            $_SESSION['user']['email'] = $post['email'];

            $_SESSION['system_message'] .= $given_slugs['slugs']['edit account success'][$this->giveLangName()] . "<br>";
            // This redirects the user back to the logout page to log him out after they changing his data
            header('Location: ' . $this->giveDomain() . 'logout.php');

            // Calling die or exit after performing a redirect using the header function
            // is critical.  The rest of your PHP script will continue to execute and
            // will be sent to the user if you do not die or exit.
            exit;
        } else {
            $_SESSION['system_message'] .= $given_slugs['slugs']['incorrect password'][$this->giveLangName()] . "<br>";
            header('Location: ' . $this->giveDomain() . 'edit_account.php');
            exit;
        }
    }
}