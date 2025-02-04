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

function isDecimalNumber($n) {
    return (string) (float) $n === (string) $n;
}

function formatBytes($bytes, $precision = 2) {
    $unit = ["bytes", "KiB", "MiB", "GiB"];
    $exp = floor(log($bytes, 1024)) | 0;
    return round($bytes / (pow(1024, $exp)), $precision) . " " . $unit[$exp];
}

/**
 * Function: sanitize
 * Returns a sanitized string, typically for URLs.
 *
 * Parameters:
 *     $string - The string to sanitize.
 *     $force_lowercase - Force the string to lowercase?
 *     $anal - If set to *true*, will remove all non-alphanumeric characters.
 */
function sanitize($string, $force_lowercase = true, $anal = true) {
    $strip = array("~", "`", "!", "@", "#", "$", "%", "^", "&", "*", "(", ")", "_", "=", "+", "[", "{", "]",
        "}", "\\", "|", ";", ":", "\"", "'", "&#8216;", "&#8217;", "&#8220;", "&#8221;", "&#8211;", "&#8212;",
        "â€”", "â€“", ",", "<", ".", ">", "/", "?");
    $clean = trim(str_replace($strip, "", strip_tags($string)));
    $clean = preg_replace('/\s+/', "-", $clean);
    $clean = ($anal) ? preg_replace("/[^a-zA-Z0-9]/", "", $clean) : $clean;
    return ($force_lowercase) ?
            (function_exists('mb_strtolower')) ?
                    mb_strtolower($clean, 'UTF-8') :
                    strtolower($clean) :
            $clean;
}

function getNameFromUserID($userID, $MySQLHandle) {
    $mQuery = mysql_query("SELECT `username` FROM `accounts` WHERE `id`='" . mysql_real_escape_string($userID) . "'", $MySQLHandle);
    if (mysql_num_rows($mQuery) > 0) {
        $row = mysql_fetch_assoc($mQuery);
        return $row['username'];
    }
    return 'Unknown';
}

function getNamefromCharacterID($charID, $MySQLHandle) {
    $mQuery = mysql_query("SELECT `charactername` FROM `characters` WHERE `id`='" . mysql_real_escape_string($charID) . "'", $MySQLHandle);
    if (mysql_num_rows($mQuery) > 0) {
        $row = mysql_fetch_assoc($mQuery);
        return $row['charactername'];
    }
    return 'Unknown';
}

function getDonatorTitleFromIndex($index) {
    $ranks = array("No", "Bronze", "Silver", "Gold", "Platinum", "Pearl", "Diamond", "Godly");
    return $ranks[$index];
}

function getStandingFromIndex($index) {
    $ranks = array("<em><font color='#66FF00'>In Good Standing</font></em>", "<em><font color='#FF0000'>Banned</font></em>");
    return $ranks[$index];
}

function getVACStandingFromIndex($index) {
    $ranks = array("<em><font color='#66FF00'>In Good Standing</font></em>", "<em><font color='#FF0000'>VAC Banned</font></em>");
    return $ranks[$index];
}

function check_email_address($email) {
    // checks proper syntax
    if (preg_match('/^[^@]+@[a-zA-Z0-9._-]+\.[a-zA-Z]+$/', $email)) {
        // gets domain name
        list($username, $domain) = split('@', $email);
        // checks for if MX records in the DNS
        if (!checkdnsrr($domain, 'MX')) {
            return false;
        }
        //// attempts a socket connection to mail server
        //if(!fsockopen($domain,25,$errno,$errstr,30)) {
        //	return false;
        //}
        return true;
    }
    return false;
}

function generatePassword($length = 9, $strength = 0) {
    $vowels = 'aeuy';
    $consonants = 'bdghjmnpqrstvz';
    if ($strength & 1) {
        $consonants .= 'BDGHJLMNPQRSTVWXZ';
    }
    if ($strength & 2) {
        $vowels .= "AEUY";
    }
    if ($strength & 4) {
        $consonants .= '23456789';
    }
    if ($strength & 8) {
        $consonants .= '@#$%';
    }

    $password = '';
    $alt = time() % 2;
    for ($i = 0; $i < $length; $i++) {
        if ($alt == 1) {
            $password .= $consonants[(rand() % strlen($consonants))];
            $alt = 0;
        } else {
            $password .= $vowels[(rand() % strlen($vowels))];
            $alt = 1;
        }
    }
    return $password;
}

function getGsFromDollar($dollar) {
    $dollar = floor($dollar);
    $rate = 3 * 50;
    $actualGC = $dollar * $rate;
    $benefit = $actualGC * (0.01 / 7.5 * $dollar);
    $finalGC = $actualGC + $benefit;
    $discount = ($benefit / $actualGC) * 100;

    if ($benefit < 0) {
        $discount = 0;
    }
    if ($discount > 50) {
        $discount = 50;
        $finalGC = $actualGC + $actualGC * 0.5;
        $benefit = $finalGC - $actualGC;
    }

    return array(round($finalGC), round($benefit), round($discount));
}

function get_client_ip() {
    // check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && validate_ip($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }

    // check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // check if multiple ips exist in var
        if (strpos($_SERVER['HTTP_X_FORWARDED_FOR'], ',') !== false) {
            $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            foreach ($iplist as $ip) {
                if (validate_ip($ip))
                    return $ip;
            }
        } else {
            if (validate_ip($_SERVER['HTTP_X_FORWARDED_FOR']))
                return $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED']) && validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // return unreliable ip since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Ensures an ip address is both a valid IP and does not fall within
 * a private network range.
 */
function validate_ip($ip) {
    if (strtolower($ip) === 'unknown')
        return false;

    // generate ipv4 network address
    $ip = ip2long($ip);

    // if the ip is set and not equivalent to 255.255.255.255
    if ($ip !== false && $ip !== -1) {
        // make sure to get unsigned long representation of ip
        // due to discrepancies between 32 and 64 bit OSes and
        // signed numbers (ints default to signed in PHP)
        $ip = sprintf('%u', $ip);
        // do private network range checking
        if ($ip >= 0 && $ip <= 50331647)
            return false;
        if ($ip >= 167772160 && $ip <= 184549375)
            return false;
        if ($ip >= 2130706432 && $ip <= 2147483647)
            return false;
        if ($ip >= 2851995648 && $ip <= 2852061183)
            return false;
        if ($ip >= 2886729728 && $ip <= 2887778303)
            return false;
        if ($ip >= 3221225984 && $ip <= 3221226239)
            return false;
        if ($ip >= 3232235520 && $ip <= 3232301055)
            return false;
        if ($ip >= 4294967040)
            return false;
    }
    return true;
}

function _make_url_clickable_cb($matches) {
    $ret = '';
    $url = $matches[2];

    if (empty($url))
        return $matches[0];
    // removed trailing [.,;:] from URL
    if (in_array(substr($url, -1), array('.', ',', ';', ':')) === true) {
        $ret = substr($url, -1);
        $url = substr($url, 0, strlen($url) - 1);
    }
    return $matches[1] . "<a href=\"$url\" rel=\"nofollow\" target=\"_blank\">$url</a>" . $ret;
}

function _make_web_ftp_clickable_cb($matches) {
    $ret = '';
    $dest = $matches[2];
    $dest = 'http://' . $dest;

    if (empty($dest))
        return $matches[0];
    // removed trailing [,;:] from URL
    if (in_array(substr($dest, -1), array('.', ',', ';', ':')) === true) {
        $ret = substr($dest, -1);
        $dest = substr($dest, 0, strlen($dest) - 1);
    }
    return $matches[1] . "<a href=\"$dest\" rel=\"nofollow\">$dest</a>" . $ret;
}

function _make_email_clickable_cb($matches) {
    $email = $matches[2] . '@' . $matches[3];
    return $matches[1] . "<a href=\"mailto:$email\">$email</a>";
}

function make_clickable($ret) {
    $ret = ' ' . $ret;
    // in testing, using arrays here was found to be faster

    $ret = preg_replace_callback('#([\s>])([\w]+?://[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_url_clickable_cb', $ret);
    $ret = preg_replace_callback('#([\s>])((www|ftp)\.[\w\\x80-\\xff\#$%&~/.\-;:=,?@\[\]+]*)#is', '_make_web_ftp_clickable_cb', $ret);
    $ret = preg_replace_callback('#([\s>])([.0-9a-z_+-]+)@(([0-9a-z-]+\.)+[0-9a-z]{2,})#i', '_make_email_clickable_cb', $ret);

    // this one is not in an array because we need it to run last, for cleanup of accidental links within links
    $ret = preg_replace("#(<a( [^>]+?>|>))<a [^>]+?>([^>]+?)</a></a>#i", "$1$3</a>", $ret);
    $ret = trim($ret);
    return $ret;
}

function generate_key_string($segment_chars = 4, $num_segments = 4, $key_string = '') {
    $tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
    for ($i = 0; $i < $num_segments; $i++) {
        $segment = '';
        for ($j = 0; $j < $segment_chars; $j++) {
            $segment .= $tokens[rand(0, 35)];
        }
        $key_string .= $segment;
        if ($i < ($num_segments - 1)) {
            $key_string .= '-';
        }
    }
    return $key_string;
}

function showBBcodes($text) {
    // BBcode array
    $find = array(
        '~\[b\](.*?)\[/b\]~s',
        '~\[i\](.*?)\[/i\]~s',
        '~\[u\](.*?)\[/u\]~s',
        //'~\[quote\](.*?)\[/quote\]~s',
        //'~\[size=(.*?)\](.*?)\[/size\]~s',
        '~\[color=(.*?)\](.*?)\[/color\]~s',
        //'~\[url\]((?:ftp|https?)://.*?)\[/url\]~s',
        '~\[img\](https?://.*?\.(?:jpg|jpeg|gif|png|bmp))\[/img\]~s',
        '~\[pastebin\]http://pastebin.com/(.*?)\[/pastebin\]~s',
    );

    // HTML tags to replace BBcode
    $replace = array(
        '<b>$1</b>',
        '<i>$1</i>',
        '<span style="text-decoration:underline;">$1</span>',
        //'<pre>$1</' . 'pre>',
        //'<span style="font-size:$1px;">$2</span>',
        '<span style="color:$1;">$2</span>',
        //'<a href="$1">$1</a>',
        '<img src="$1" alt="" border=0 style="max-width:954px;"/><br><a href="$1" target="_blank">Full size</a>',
        '<iframe src="http://pastebin.com/embed_iframe.php?i=$1" style="border:none;width:100%"></iframe>',
    );

    // Replacing the BBcodes with corresponding HTML tags
    return preg_replace($find, $replace, $text);
}

$itemIDtoName = array(
    '1' => 'Hotdog',
    '2' => 'Cellphone',
    '3' => 'Vehicle key',
    '4' => 'House key',
    '5' => 'Business key',
    '6' => 'Radio',
    '7' => 'Phonebook',
    '8' => 'Sandwich',
    '9' => 'Softdrink',
    '10' => 'Dice',
    '11' => 'Taco',
    '12' => 'Burger',
    '13' => 'Donut',
    '14' => 'Cookie',
    '15' => 'Water',
    '16' => 'Clothes',
    '17' => 'Watch',
    '18' => 'City Guide',
    '19' => 'MP3 Player',
    '20' => 'Standard Fighting for Dummies',
    '21' => 'Boxing for Dummies',
    '22' => 'Kung fu for Dummies',
    '23' => 'Knee Head Fighting for Dummies',
    '24' => 'Grab Kick Fighting for Dummies',
    '25' => 'Elbow Fighting for Dummies',
    '26' => 'Gas Mask',
    '27' => 'Flashbang',
    '28' => 'Glowstick',
    '29' => 'Door Ram',
    '30' => 'Cannabis Sativa',
    '31' => 'Cocaine Alkaloid',
    '32' => 'Lysergic Acit',
    '33' => 'Unprocessed PCP',
    '34' => 'Cocaine',
    '35' => 'Drug 2',
    '36' => 'Drug 3',
    '37' => 'Drug 4',
    '38' => 'Marijuana',
    '39' => 'Drug 6',
    '40' => 'Angel Dust',
    '41' => 'LSD',
    '42' => 'Drug 9',
    '43' => 'PCP Hydrochloride',
    '44' => 'Chemistry Set',
    '45' => 'Handcuffs',
    '46' => 'Rope',
    '47' => 'Handcuff Keys',
    '48' => 'Backpack',
    '49' => 'Fishing Rod',
    '50' => 'Los Santos Highway Code',
    '51' => 'Chemistry 101',
    '52' => 'Police officer\'s Manual',
    '53' => 'Breathalizer',
    '54' => 'Ghettoblaster',
    '55' => 'Business Card',
    '56' => 'Ski Mask',
    '57' => 'Fuel Can',
    '58' => 'Ziebrand Beer',
    '59' => 'Mudkip',
    '60' => 'Safe',
    '61' => 'Emergency Light strobes',
    '62' => 'Bastradov Vodka',
    '63' => 'Scottish Whiskey',
    '64' => 'LSPD Badge',
    '65' => 'LSES Identification',
    '66' => 'Blindfold',
    '67' => 'GPS',
    '68' => 'Lottery Ticket',
    '69' => 'Dictionary',
    '70' => 'First Aid Kit',
    '71' => 'Notebook',
    '72' => 'Note',
    '73' => 'Elevator Remote',
    '76' => 'Riot Shielf',
    '77' => 'Card Deck',
    '78' => 'San Andreas Pilot Certificate',
    '79' => 'Porn Tape',
    '80' => 'Generic Item',
    '81' => 'Fridge',
    '82' => 'LST&R Identification',
    '83' => 'Coffee',
    '84' => 'Escort 9500ci Radar Detector',
    '85' => 'Emergency Siren',
    '86' => 'SAN Identification',
    '87' => 'LS Government Badge',
    '88' => 'Earpiece',
    '89' => 'Food',
    '90' => 'Helmet',
    '91' => 'Eggnog',
    '92' => 'Turkey',
    '93' => 'Christmas Pudding',
    '94' => 'Christmas Present',
    '95' => 'Drink',
    '96' => 'PDA',
    '97' => 'LSES Procedures Manual',
    '98' => 'Garage Remote',
    '99' => 'Mixed Dinner Tray',
    '100' => 'Small Milk Carton',
    '101' => 'Small Juice Carton',
    '102' => 'Cabbage',
    '103' => 'Shelf',
    '104' => 'Portable TV',
    '105' => 'Pack of Cifgarettes',
    '106' => 'Cigarette',
    '107' => 'Lighter',
    '-0' => 'Fist',
    '-1' => 'Brass Knuckles',
    '-2' => 'Golf Club',
    '-3' => 'Nightstick',
    '-4' => 'Knife',
    '-5' => 'Baseball Bat',
    '-6' => 'Shovel',
    '-7' => 'Pool Cue',
    '-8' => 'Katana',
    '-9' => 'Chainsaw',
    '-10' => 'Long Purple Dildo',
    '-11' => 'Short tan Dildo',
    '-12' => 'Vibrator',
    '-14' => 'Flowers',
    '-15' => 'Cane',
    '-16' => 'Grenade',
    '-17' => 'Tear Gas',
    '-18' => 'Molotov Cocktails',
    '-22' => 'Colt 45',
    '-23' => 'Silenced Pistol',
    '-24' => 'Desert Eagle',
    '-25' => 'Shotgun',
    '-26' => 'Sawn-Off Shotgun',
    '-27' => 'SPAZ-12 Combat Shotgun',
    '-28' => 'Uzi',
    '-29' => 'MP5',
    '-30' => 'AK-47',
    '-31' => 'M4',
    '-32' => 'TEC-9',
    '-33' => 'Country Rifle',
    '-34' => 'Sniper Rifle',
    '-35' => 'Rocket Launcher',
    '-36' => 'Heat-Seeking RPG',
    '-37' => 'Flamethrower',
    '-38' => 'Minigun',
    '-39' => 'Satchel Charges',
    '-40' => 'Satchel Detonator',
    '-41' => 'Spraycan',
    '-42' => 'Fire extinguisher',
    '-43' => 'Camera',
    '-44' => 'Night-Vision Goggles',
    '-45' => 'Infrared Goggles',
    '-46' => 'Parachute');

$vehicleIDtoName = array(
    '400' => 'Landstalker',
    '401' => 'Bravura',
    '402' => 'Buffalo',
    '403' => 'Linerunner',
    '404' => 'Perenail',
    '405' => 'Sentinel',
    '406' => 'Dumper',
    '407' => 'Firetruck',
    '408' => 'Trashmaster',
    '409' => 'Stretch',
    '410' => 'Manana',
    '411' => 'Infernus',
    '412' => 'Voodoo',
    '413' => 'Pony',
    '414' => 'Mule',
    '415' => 'Cheetah',
    '416' => 'Ambulance',
    '417' => 'Levetian',
    '418' => 'Moonbeam',
    '419' => 'Esperanto',
    '420' => 'Taxi',
    '421' => 'Washington',
    '422' => 'Bobcat',
    '423' => 'Mr Whoopee',
    '424' => 'BF Injection',
    '425' => 'Hunter',
    '426' => 'Premier',
    '427' => 'Enforcer',
    '428' => 'Securicar',
    '429' => 'Banshee',
    '430' => 'Predator',
    '431' => 'Bus',
    '432' => 'Rhino',
    '433' => 'Barracks',
    '434' => 'Hotknife',
    '435' => 'Artic trailer 1',
    '436' => 'Previon',
    '437' => 'Coach',
    '438' => 'Cabbie',
    '439' => 'Stallion',
    '440' => 'Rumpo',
    '441' => 'RC Bandit',
    '442' => 'Romero',
    '443' => 'Packer',
    '444' => 'Monster',
    '445' => 'Admiral',
    '446' => 'Squalo',
    '447' => 'Seasparrow',
    '448' => 'Pizza boy',
    '449' => 'Tram',
    '450' => 'Artic trailer 2',
    '451' => 'Turismo',
    '452' => 'Speeder',
    '453' => 'Reefer',
    '454' => 'Tropic',
    '455' => 'Flatbed',
    '456' => 'Yankee',
    '457' => 'Caddy',
    '458' => 'Solair',
    '459' => 'Top fun',
    '460' => 'Skimmer',
    '461' => 'PCJ 600',
    '462' => 'Faggio',
    '463' => 'Freeway',
    '464' => 'RC Baron',
    '465' => 'RC Raider',
    '466' => 'Glendale',
    '467' => 'Oceanic',
    '468' => 'Sanchez',
    '469' => 'Sparrow',
    '470' => 'Patriot',
    '471' => 'Quad',
    '472' => 'Coastguard',
    '473' => 'Dinghy',
    '474' => 'Hermes',
    '475' => 'Sabre',
    '476' => 'Rustler',
    '477' => 'ZR 350',
    '478' => 'Walton',
    '479' => 'Regina',
    '480' => 'Comet',
    '481' => 'BMX',
    '482' => 'Burriro',
    '483' => 'Camper',
    '484' => 'Marquis',
    '485' => 'Baggage',
    '486' => 'Dozer',
    '487' => 'Maverick',
    '488' => 'VCN Maverick',
    '489' => 'Rancher',
    '490' => 'FBI Rancher',
    '491' => 'Virgo',
    '492' => 'Greenwood',
    '493' => 'Jetmax',
    '494' => 'Hotring',
    '495' => 'Sandking',
    '496' => 'Blistac',
    '497' => 'Police Maverick',
    '498' => 'Boxville',
    '499' => 'Benson',
    '500' => 'Mesa',
    '501' => 'RC Goblin',
    '502' => 'Hotring A',
    '503' => 'Hotring B',
    '504' => 'Blood ring banger',
    '505' => 'Rancher (lure)',
    '506' => 'Super GT',
    '507' => 'Elegant',
    '508' => 'Journey',
    '509' => 'Bike',
    '510' => 'Mountain bike',
    '511' => 'Beagle',
    '512' => 'Cropduster',
    '513' => 'Stuntplane',
    '514' => 'Petrol',
    '515' => 'Roadtrain',
    '516' => 'Nebula',
    '517' => 'Majestic',
    '518' => 'Buccaneer',
    '519' => 'Shamal',
    '520' => 'Hydra',
    '521' => 'FCR 900',
    '522' => 'NRG 500',
    '523' => 'HPV 1000',
    '524' => 'Cement',
    '525' => 'Towtruck',
    '526' => 'Fortune',
    '527' => 'Cadrona',
    '528' => 'FBI Truck',
    '529' => 'Williard',
    '530' => 'Fork lift',
    '531' => 'Tractor',
    '532' => 'Combine',
    '533' => 'Feltzer',
    '534' => 'Remington',
    '535' => 'Slamvan',
    '536' => 'Blade',
    '537' => 'Freight',
    '538' => 'Streak',
    '539' => 'Vortex',
    '540' => 'Vincent',
    '541' => 'Bullet',
    '542' => 'Clover',
    '543' => 'Sadler',
    '544' => 'Firetruck LA',
    '545' => 'Hustler',
    '546' => 'Intruder',
    '547' => 'Primo',
    '548' => 'Cargobob',
    '549' => 'Tampa',
    '550' => 'Sunrise',
    '551' => 'Merit',
    '552' => 'Utility Van',
    '553' => 'Nevada',
    '554' => 'Yosemite',
    '555' => 'Windsor',
    '556' => 'Monster A',
    '557' => 'Monster B',
    '558' => 'Uranus',
    '559' => 'Jester',
    '560' => 'Sultan',
    '561' => 'Stratum',
    '562' => 'Elegy',
    '563' => 'Raindance',
    '564' => 'RC Tiger',
    '565' => 'Flsh',
    '566' => 'Yahoma',
    '567' => 'Savanna',
    '568' => 'Bandito',
    '569' => 'Freight flat',
    '570' => 'Streak',
    '571' => 'Kart',
    '572' => 'Mower',
    '573' => 'Duneride',
    '574' => 'Sweeper',
    '575' => 'Broadway',
    '576' => 'Tornado',
    '577' => 'AT 400',
    '578' => 'DFT 30',
    '579' => 'Huntley',
    '580' => 'Stafford',
    '581' => 'BF 400',
    '582' => 'News van',
    '583' => 'Tug',
    '584' => 'Petrol Tanker',
    '585' => 'Emperor',
    '586' => 'Wayfarer',
    '587' => 'Euros',
    '588' => 'Hotdog',
    '589' => 'Club',
    '590' => 'Freight box',
    '591' => 'Artic trailer 3',
    '592' => 'Andromada',
    '593' => 'Dodo',
    '594' => 'RC Cam',
    '595' => 'Launch',
    '596' => 'Cop car LS',
    '597' => 'Cop car SF',
    '598' => 'Cop car LV',
    '599' => 'Ranger',
    '600' => 'Picador',
    '601' => 'Swat tank',
    '602' => 'Alpha',
    '603' => 'Pheonix',
    '604' => 'Glendale (damage)',
    '605' => 'Sadler (damage)',
    '606' => 'Bag box A',
    '607' => 'Bag box B',
    '608' => 'Stairs',
    '609' => 'Boxville (black)',
    '610' => 'Farm trailer',
    '611' => 'Utility van trailer'
);
?>

