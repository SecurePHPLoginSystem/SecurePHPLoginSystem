<?php
    defined('VALID_PAGE') or die('You are not authorized to view this page.');
    
    function generate_secure_token($length = 16) {
        return bin2hex(openssl_random_pseudo_bytes($length));                           // important! this has to be a crytographically secure random generator
    }

    function html_escape($raw_input) {
        return htmlspecialchars($raw_input, ENT_QUOTES | ENT_HTML401, 'UTF-8');
    }

    function assoc(&$array,$key,$key2=false, $key3=false) { 
        // cast as array if an object is being passed 
        if(is_object($array)){ 
            $array = (array)$array; 
        } 
        $newarray = array(); 
        // only one key 
        if(!$key2){ 
            foreach ($array as $values){ 
                // cast as array if an object of values is being passed 
                if(is_object($values)){ 
                    $values = (array)$values; 
                } 
                $newarray[$values[$key]] = $values; 
            } 
        // or two keys 
        } elseif(!$key3) { 
            foreach ($array as $values){ 
                if(is_object($values)){ 
                    $values = (array)$values; 
                }                 
                $newarray[$values[$key]][$values[$key2]] = $values; 
            } 
        } else {
            foreach ($array as $values){ 
                if(is_object($values)){ 
                    $values = (array)$values; 
                }                 
                $newarray[$values[$key]][$values[$key2]][$values[$key3]] = $values; 
            } 
        } 
        return $newarray;
    }