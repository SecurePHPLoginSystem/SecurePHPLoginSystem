<?php

require("common.php"); 


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
    if ( $unsub === false )            // key doesn't exist
        echo 'Invalid email key. Please check the URL in your previous notification email.';
    else                            // key exists
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
            if ( $unsub_stmt->rowCount() )
                $unsub = true;
        }
        if ( $unsub )
            echo 'Your email address has been blocked in our system. You will no longer receive notification emails.';
        else
            echo 'There was a technical issue. Please try again later.';
    }
}
else
    echo 'Missing email key. Please check the URL in your previous notification email. It must have the form http://domain.com/no_mail.php?email_key=...';
