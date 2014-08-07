<?php
    function generate_secure_token($length = 16) {
        return bin2hex(openssl_random_pseudo_bytes($length));                           // important! this has to be a crytographically secure random generator
    }

    function html_escape($raw_input) {
        return htmlspecialchars($raw_input, ENT_QUOTES | ENT_HTML401, 'UTF-8');
    }
