<?php
define('VALID_PAGE', true);
require("common.php"); 

$db->commonCode();

$given_slugs = $db->giveSlugs(array('no mail invalid key', 'no mail block succes', 'no mail block fail', 'no mail missing key'));

if( !empty($_GET['email_key']) )
{
    $key_stmt = $db->prepare('
        SELECT
            unsubscribed
        FROM
            unsubscribed_email_addresses
        WHERE
            email_key = :email_key
    ');
    $key_stmt->execute(array(
        ':email_key' => $_GET['email_key']
    ));
    $unsub = $key_stmt->fetchColumn();
    if ( $unsub === false ) {           // key doesn't exist
        echo html_escape($given_slugs['slugs']['no mail invalid key'][$db->giveLangName()]);
    } else                            // key exists
    {
        if ( !$unsub )                // not unsubscribed yet
        {
            $unsub_stmt = $db->prepare('
                UPDATE
                    unsubscribed_email_addresses
                SET
                    unsubscribed = 1
                WHERE
                    email_key = :email_key
            ');
            $unsub_stmt->execute(array(
                ':email_key' => $_GET['email_key']
            ));
            if ( $unsub_stmt->rowCount() ) {
                $unsub = true;
            }
        }
        if ( $unsub ) {
            echo html_escape($given_slugs['slugs']['no mail block success'][$db->giveLangName()]);
        } else {
            echo html_escape($given_slugs['slugs']['no mail block fail'][$db->giveLangName()]);
        }
    }
}
else {
    echo html_escape($given_slugs['slugs']['no mail missing key'][$db->giveLangName()]) . $db->giveDomain() . 'password_reset.php?reset_key=...';
}
