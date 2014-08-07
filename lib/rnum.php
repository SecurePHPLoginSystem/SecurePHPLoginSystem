<?php
    /**
 * Generate a relatively secure hex encoded pseudo random number.
 *
 * Code taken from the password_compat library by Anthony Ferrara (github.com/ircmaxell/password_compat).
 * The resulting number is _not_ necessarily cryptographically secure.
 *
 * @param int $length The length of the resulting hex string
 *
 * @return string|boolean The hex string or false in case of an error
 */
function rnum($length = 32) {
    $length = (int) $length;
    if (!$length) {
        trigger_error('Invalid length for random string', E_USER_WARNING);
        return false;
    }
    $buffer = '';
    $raw_length = $length / 2;        // the number of bytes
    $buffer_valid = false;
    if (function_exists('mcrypt_create_iv') && !defined('PHALANGER')) {
        $buffer = mcrypt_create_iv($raw_length, MCRYPT_DEV_URANDOM);
        if ($buffer) {
            $buffer_valid = true;
        }
    }
    if (!$buffer_valid && function_exists('openssl_random_pseudo_bytes')) {
        $buffer = openssl_random_pseudo_bytes($raw_length);
        if ($buffer) {
            $buffer_valid = true;
        }
    }
    if (!$buffer_valid && is_readable('/dev/urandom')) {
        $f = fopen('/dev/urandom', 'r');
        $read = strlen($buffer);
        while ($read < $raw_length) { 
            $buffer .= fread($f, $raw_length - $read);
            $read = strlen($buffer);
        }
        fclose($f);
        if ($read >= $raw_length) {
            $buffer_valid = true;
        }
    }
    if (!$buffer_valid || strlen($buffer) < $raw_length) {
        $bl = strlen($buffer);
        for ($i = 0; $i < $raw_length; $i++) {
            if ($i < $bl) {
                $buffer[$i] = $buffer[$i] ^ chr(mt_rand(0, 255));
            } else {
                $buffer .= chr(mt_rand(0, 255));
            }
        }
    }
    if (!$buffer_valid || strlen($buffer) < $raw_length) {
        trigger_error('Could not generate random string', E_USER_WARNING);
        return false;
    }

    return bin2hex($buffer);
}
