<?php
    define('VALID_PAGE', true);
    require("common.php"); 
    require_once('lib/functions.php');
    $db->commonCode(true);
    $db->SystemMessage();

$given_slugs = $db->giveSlugs(array('private text', 'logout', 'edit account'));
?><html>
    <head>
        <?php $db->commonCodeHead(); ?>
    </head>
    <body>
        <?php $db->commonCodeUpperBody();
        echo nl2br(html_escape($given_slugs['slugs']['private text'][$db->giveLangName()])); ?>
        
        <br><br><br>
        <a href="<?php echo html_escape($db->giveDomain());?>logout.php"><?php echo html_escape($given_slugs['slugs']['logout'][$db->giveLangName()]);?></a>
        <br>
        <a href="<?php echo html_escape($db->giveDomain());?>edit_account.php"><?php echo html_escape($given_slugs['slugs']['edit account'][$db->giveLangName()]);?></a>
    </body>
</html>