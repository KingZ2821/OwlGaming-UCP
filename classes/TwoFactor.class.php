<?php

/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */

class TwoFactor {

    protected $_codeLength = 6;
    private $root;
    private $db;

    function __construct() {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        $this->root = $root;
        require_once "$this->root/config.inc.php";
        $this->dbConnect();
        $this->start_session();
        require_once "$this->root/functions/base_functions.php";
    }

    function __destruct() {
        @$this->dbClose();
    }

    private function dbConnect($conn = false) {
        if ($conn) {
            $this->db = $conn;
        } else {
            require_once "$this->root/classes/Database.class.php";
            $this->db = new Database("MTA");
            $this->db->connect(true);
        }
    }

    private function dbClose() {
        @$this->db->close();
    }

    private function start_session() {
        require_once "$this->root/classes/Session.class.php";
        $session = new Session();
        $session->start_session('_owlgaming');
        return $session;
    }

    /**
     * Create new secret.
     * 16 characters, randomly chosen from the allowed base32 characters.
     *
     * @param int $secretLength
     * @return string
     */
    public function createSecret($secretLength = 16) {
        $validChars = $this->_getBase32LookupTable();
        unset($validChars[32]);

        $secret = '';
        for ($i = 0; $i < $secretLength; $i++) {
            $secret .= $validChars[array_rand($validChars)];
        }
        return $secret;
    }

    /**
     * Calculate the code, with given secret and point in time
     *
     * @param string $secret
     * @param int|null $timeSlice
     * @return string
     */
    public function getCode($secret, $timeSlice = null) {
        if ($timeSlice === null) {
            $timeSlice = floor(time() / 30);
        }

        $secretkey = $this->_base32Decode($secret);

        // Pack time into binary string
        $time = chr(0) . chr(0) . chr(0) . chr(0) . pack('N*', $timeSlice);
        // Hash it with users secret key
        $hm = hash_hmac('SHA1', $time, $secretkey, true);
        // Use last nipple of result as index/offset
        $offset = ord(substr($hm, -1)) & 0x0F;
        // grab 4 bytes of the result
        $hashpart = substr($hm, $offset, 4);

        // Unpak binary value
        $value = unpack('N', $hashpart);
        $value = $value[1];
        // Only 32 bits
        $value = $value & 0x7FFFFFFF;

        $modulo = pow(10, $this->_codeLength);
        return str_pad($value % $modulo, $this->_codeLength, '0', STR_PAD_LEFT);
    }

    /**
     * Get QR-Code URL for image, from google charts
     *
     * @param string $name
     * @param string $secret
     * @param string $title
     * @return string
     */
    public function getQRCodeGoogleUrl($name, $secret, $title = null) {
        $urlencoded = urlencode('otpauth://totp/' . $name . '?secret=' . $secret . '');
        if (isset($title)) {
            $urlencoded .= urlencode('&issuer=' . urlencode($title));
        }
        return 'https://chart.googleapis.com/chart?chs=200x200&chld=M|0&cht=qr&chl=' . $urlencoded . '';
    }

    private function logged_in() {
        return isset($_SESSION['userid']) and is_numeric($_SESSION['userid']);
    }

    /**
     * Check if the code is correct. This will accept codes starting from $discrepancy*30sec ago to $discrepancy*30sec from now
     *
     * @param string $secret
     * @param string $code
     * @param int $discrepancy This is the allowed time drift in 30 second units (8 means 4 minutes before or after)
     * @param int|null $currentTimeSlice time slice if we want use other that time()
     * @return bool
     */
    public function verifyCode($code, $discrepancy = 1, $currentTimeSlice = null) {
        if (!$this->logged_in()) {
            return false;
        }
        //First check if it's prematurely deleted in database.
        $twofactor = $this->db->query_first("SELECT * FROM google_authenticator WHERE userid=" . $this->db->escape($_SESSION['userid']));
        if (!$twofactor or is_null($twofactor['enabled']) or ! is_numeric($twofactor['enabled']) or $twofactor['enabled'] == 0) {
            return true;
        }
        if (strlen($code) == 6) {
            if ($currentTimeSlice === null) {
                $currentTimeSlice = floor(time() / 30);
            }
            for ($i = -$discrepancy; $i <= $discrepancy; $i++) {
                $calculatedCode = $this->getCode($twofactor['secret'], $currentTimeSlice + $i);
                if ($calculatedCode == $code) {
                    //Now add new IP to whitelist.
                    $this->add_ip(get_client_ip(), $twofactor['ip']);
                    return true;
                }
            }
            return false;
        } else {
            if (strtoupper($code) == strtoupper($twofactor['recovery_code'])) {
                if ($this->db->query("DELETE FROM google_authenticator WHERE secret='" . $twofactor['secret'] . "'")) {
                    unset($_SESSION['ga_ip']);
                    return true;
                }
            }
            return false;
        }
    }

    function add_ip($new_ip, $existing_ips = '') {
        if (!$this->logged_in()) {
            return false;
        }
        $ips = explode(";", $existing_ips);
        if (!in_array($new_ip, $ips)) {
            array_push($ips, $new_ip);
        }
        //Pop one element off the begining of array, because session data length is limited hence we can not afford unlimited IPs list.
        if (count($ips) > 3 ) {
            array_shift($ips);
        }
        $ips = implode(";", $ips);
        $_SESSION['ga_ip'] = $ips;
        return $this->db->query("UPDATE google_authenticator SET ip='" . $this->db->escape($ips) . "', enabled=1 WHERE userid=" . $_SESSION['userid']);
    }

    /**
     * Set the code length, should be >=6
     *
     * @param int $length
     * @return TwoFactor
     */
    public function setCodeLength($length) {
        $this->_codeLength = $length;
        return $this;
    }

    /**
     * Helper class to decode base32
     *
     * @param $secret
     * @return bool|string
     */
    protected function _base32Decode($secret) {
        if (empty($secret))
            return '';

        $base32chars = $this->_getBase32LookupTable();
        $base32charsFlipped = array_flip($base32chars);

        $paddingCharCount = substr_count($secret, $base32chars[32]);
        $allowedValues = array(6, 4, 3, 1, 0);
        if (!in_array($paddingCharCount, $allowedValues))
            return false;
        for ($i = 0; $i < 4; $i++) {
            if ($paddingCharCount == $allowedValues[$i] &&
                    substr($secret, -($allowedValues[$i])) != str_repeat($base32chars[32], $allowedValues[$i]))
                return false;
        }
        $secret = str_replace('=', '', $secret);
        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i = $i + 8) {
            $x = "";
            if (!in_array($secret[$i], $base32chars))
                return false;
            for ($j = 0; $j < 8; $j++) {
                $x .= str_pad(base_convert(@$base32charsFlipped[@$secret[$i + $j]], 10, 2), 5, '0', STR_PAD_LEFT);
            }
            $eightBits = str_split($x, 8);
            for ($z = 0; $z < count($eightBits); $z++) {
                $binaryString .= ( ($y = chr(base_convert($eightBits[$z], 2, 10))) || ord($y) == 48 ) ? $y : "";
            }
        }
        return $binaryString;
    }

    /**
     * Helper class to encode base32
     *
     * @param string $secret
     * @param bool $padding
     * @return string
     */
    protected function _base32Encode($secret, $padding = true) {
        if (empty($secret))
            return '';

        $base32chars = $this->_getBase32LookupTable();

        $secret = str_split($secret);
        $binaryString = "";
        for ($i = 0; $i < count($secret); $i++) {
            $binaryString .= str_pad(base_convert(ord($secret[$i]), 10, 2), 8, '0', STR_PAD_LEFT);
        }
        $fiveBitBinaryArray = str_split($binaryString, 5);
        $base32 = "";
        $i = 0;
        while ($i < count($fiveBitBinaryArray)) {
            $base32 .= $base32chars[base_convert(str_pad($fiveBitBinaryArray[$i], 5, '0'), 2, 10)];
            $i++;
        }
        if ($padding && ($x = strlen($binaryString) % 40) != 0) {
            if ($x == 8)
                $base32 .= str_repeat($base32chars[32], 6);
            elseif ($x == 16)
                $base32 .= str_repeat($base32chars[32], 4);
            elseif ($x == 24)
                $base32 .= str_repeat($base32chars[32], 3);
            elseif ($x == 32)
                $base32 .= $base32chars[32];
        }
        return $base32;
    }

    /**
     * Get array with all 32 characters for decoding from/encoding to base32
     *
     * @return array
     */
    protected function _getBase32LookupTable() {
        return array(
            'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', //  7
            'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', // 15
            'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', // 23
            'Y', 'Z', '2', '3', '4', '5', '6', '7', // 31
            '='  // padding char
        );
    }

    function is_two_factor_enabled() {
        if (!isset($_SESSION['ga_ip'])) {
            return false;
        }
        return true;
    }

    function is_two_factor_valid($redirect = false) {
        if ($this->is_two_factor_enabled()) {
            require_once "$this->root/functions/base_functions.php";
            $client_ip = get_client_ip();
            $allowed_ips = explode(";", $_SESSION['ga_ip']);
            foreach ($allowed_ips as $allowed_ip) {
                if ($allowed_ip == $client_ip) {
                    return true;
                }
            }
            if ($redirect) {
                header('Location: /twofactor.php');
            }
        }
        return false;
    }

}
