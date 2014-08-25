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
    define('VALID_PAGE', true);
    
    require_once("common.php");
    require_once("lib/password.php");
    require_once("lib/functions.php");

    $db->commonCode();
    
    $submitted_username = '';

    
    if(!empty($_POST))
    {
        $submitted_username = $_POST['username'];
        $db->login($_POST['username'], $_POST['password']);
    }
    $given_slugs = $db->giveSlugs(array('login', 'username', 'password', 'register', 'forgot password'));
?>
<html>
    <head>
        <?php $db->commonCodeHead(); ?>
    </head>
    <body>
        <?php $db->commonCodeUpperBody(); ?>
        <h1><?php echo html_escape($given_slugs['slugs']['login'][$db->giveLangName()]);?></h1>
        <?php $db->SystemMessage();?>
        <form action="login.php" method="post">
        <?php echo html_escape($given_slugs['slugs']['username'][$db->giveLangName()]);?>:
            <input type="text" name="username" value="<?php echo html_escape($submitted_username); ?>" />
            <br /><br />
        <?php echo html_escape($given_slugs['slugs']['password'][$db->giveLangName()]);?>:
            <input type="password" name="password" value="" />
            <br /><br />
        <input type="submit" value="<?php echo html_escape($given_slugs['slugs']['login'][$db->giveLangName()]);?>" />
        </form>
        <a href="register.php"><?php echo html_escape($given_slugs['slugs']['register'][$db->giveLangName()]);?></a> | <a href="forgot_password.php"><?php echo html_escape($given_slugs['slugs']['forgot password'][$db->giveLangName()]);?></a>
    </body>
</html>