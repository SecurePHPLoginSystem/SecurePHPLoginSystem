<?php
    ///////////////////////////////////////////
    ///////////////////////////////////////////
    ///* This is a login system, used to secure private (members-only) pages for a website
    ///* Created by Ricardo van der Pluijm (derplumo on Devshed)
    ///* Based on the files located on:
    ///*    http://forums.devshed.com/php-faqs-and-stickies-167/how-to-program-a-basic-but-secure-login-system-using-891201.html
    ///*    By E-Oreo
    ///*
    ///* The login files are located at:
    ///*    http://forums.devshed.com/php-faqs-and-stickies-167/a-more-advanced-login-system-with-password-forgot-955871.html
    ///*
    ///*
    ///* IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
    ///* LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
    ///* OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
    ///* THE SOFTWARE.
    /////////////////////////////////////////////
    /////////////////////////////////////////////

    require("common.php");
    require("lib/password.php");
    require ("lib/functions.php");

    $submitted_username = '';


    if(!empty($_POST))
    {
        $user_information_stmt = $db->prepare('
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
            ':username' => $_POST['username']
        ));

        $login_ok = false;

        $row = $user_information_stmt->fetch();
        if($row)
        {
            if ( password_verify($_POST['password'], $row['password']) )
            {
                $login_ok = true;
            }
        }

        if($login_ok)
        {
            unset($row['password']);
            $_SESSION['user'] = $row;
            $_SESSION['action_token'] = generate_secure_token();

            header("Location: private.php");
            exit;
        }
        else
        {
            print("Login Failed.");
            $submitted_username = html_escape($_POST['username']);
        }
    }

?>
<html>
    <body>
        <h1>Login</h1>
        <form action="login.php" method="post">
        Username:
            <input type="text" name="username" value="<?php echo $submitted_username; ?>" />
            <br /><br />
        Password:
            <input type="password" name="password" value="" />
            <br /><br />
        <input type="submit" value="Login" />
        </form>
        <a href="register.php">Register</a>
    </body>
</html>
