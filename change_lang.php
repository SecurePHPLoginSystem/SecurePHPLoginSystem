<?php
    define('VALID_PAGE', true);
    
    require("common.php");
    
    $db->commonCode();
    
    $return = false;
    if(!empty($_POST) && ALLOW_LANGUAGE_CHANGE) {
        $is_lang = $db->isLang($_POST['action']);
        if(!empty($is_lang) && !empty($_POST['ref_file'])) {
            if($db->checkLoggedIn()) {
                $change_lang_stmt = $db->prepare('
                    UPDATE users
                    SET lang = :lang
                    WHERE user_id = :user_id
                ');
                $change_lang_stmt->execute(array(
                    ':lang' => $is_lang['lang_id']
                    , ':user_id' => $_SESSION['user']['user_id']
                ));
            }
            $_SESSION['language'] = $is_lang['lang_id'];

            $return = true;
        }
    }
    
    if($return) {
        header("Location: " . DOMAIN . html_escape($_POST['ref_file']));
        exit;
    } else {
        header("Location: " . DOMAIN . "login.php");
        exit;
    }