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

$thankYouEmail = "Dear my friend,

I want to express my appreciation for your generosity in support of OwlGaming Community. Your personal commitment was incredibly helpful and allowed us to reach our goal. Your assistance means so much to me but even more to the Community. Thank you from all of us!

In return, GameCoins should have been added into the account instantly. If for some reasons, you don't recieve GC(s) within next 2 hour(s) or have any other purchase issues/questions, please visit our Support Center at http://owlgaming.net/support.php to submit a ticket under 'Donation issue or question'.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

$referrerEmail = "Dear my friend,

I want to express my appreciation for your generosity in support of OwlGaming Community. Your personal commitment was incredibly helpful and allowed us to reach our goal. Your assistance means so much to me but even more to the Community. Thank you from all of us!

Someone you referred has just purchased GCs! In return, 10% of the Gamecoins he purchased should have been added into your account instantly. If for some reasons, you don't recieve GC(s) within next 2 hour(s) or have any other purchase issues/questions, please visit our Support Center at http://owlgaming.net/support.php to submit a ticket under 'Donation issue or question'.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

require_once './classes/Database.class.php';
if (!in_array($_SERVER['REMOTE_ADDR'], array('79.125.125.1', '79.125.5.205', '79.125.5.95', '54.72.6.126', '54.72.6.27', '54.72.6.17', '54.72.6.23'))) {
    header("HTTP/1.0 403 Forbidden");
    mail(WEBMASTER_EMAIL, 'OwlGaming Mobile Payments - Error: Unknown IP', "Error: Unknown IP", "From: donate@owlgaming.net");
    die("Error: Unknown IP");
}

// check the signature
$secret = 'cefda219dce16b7cd8722fe92f0a1ba5'; // insert your secret between ''
if (empty($secret) || !check_signature($_GET, $secret)) {
    header("HTTP/1.0 404 Not Found");
    mail(WEBMASTER_EMAIL, 'OwlGaming Mobile Payments - Error: Invalid signature', "Error: Invalid signature", "From: donate@owlgaming.net");
    die("Error: Invalid signature");
}
//Info https://developers.fortumo.com/cross-platform-mobile-payments/receipt-verification-for-web-apps/
$sender = $_GET['sender']; //phone num.
$amount = $_GET['amount']; //credit
$cuid = $_GET['cuid']; //resource i.e. user
$payment_id = $_GET['payment_id']; //unique id
$test = $_GET['test']; // this parameter is present only when the payment is a test payment, it's value is either 'ok' or 'fail'
//hint: find or create payment by payment_id
//additional parameters: operator, price, user_share, country
$operator = $_GET['operator'];
$user_share = $_GET['user_share'];
$price = $_GET['price'];
$currency = $_GET['currency'];
$revenue = $_GET['revenue'];
$country = $_GET['country'];

$final = "Sender: +$sender\nGCs: $amount\nUser ID: $cuid\nPayment ID: $payment_id\nSandbox: $test\nOperator: $operator\nCountry: $country\nCost: $currency $price\nRevenue: $currency $revenue ($user_share%)";
if (preg_match("/completed/i", $_GET['status'])) {
  // mark payment successful
  // print out the reply
  if ($test) {
      mail(WEBMASTER_EMAIL, "OwlGaming Mobile Payments - Completed - $amount GCs", $final, "From: donate@owlgaming.net");
      echo('TEST OK');
  } else if (is_numeric($cuid)) {
      $db = new Database("MTA");
      $db->connect();
      $alreadyProcessed = $db->query_first("SELECT `payment_id` FROM `mobile_payments` WHERE `transaction_id`='" . $db->escape($payment_id) ."' LIMIT 1");
      error_log(($alreadyProcessed['payment_id']), 0);
      if (is_numeric($alreadyProcessed['payment_id'])) {
        // Already Processed
        error_log("woah!");
        die("Warn: Already Processed this transaction " . $payment_id);
      } else {
        $donatedFromPlayer = $db->query_first("SELECT `id`, `username`, `email`, `referrer` FROM `accounts` WHERE `id`='" . $db->escape($cuid) . "'");
        $db->query("UPDATE `accounts` SET `credits`=`credits`+" . $db->escape($amount) . " WHERE `id`='" . $db->escape($cuid) . "'");
        $db->query_insert('mobile_payments', array('sender_phone' => $sender, 'operator' => $operator, 'country' => $country, 'game_coin' => $amount, 'account' => $cuid, 'currency' => $currency, 'cost' => $price, 'revenue' => $revenue, 'transaction_id' => $payment_id));
        $referrer = $donatedFromPlayer['referrer'];
        if ($referrer and is_numeric($referrer) and $referrer > 0) {
            $GcToRef = ceil($amount / 10);
            if ($GcToRef > 0) {
                $db->query("UPDATE `accounts` SET `credits`=`credits`+" . $GcToRef . " WHERE `id`='" . $referrer . "'");
                $insert = array();
                //Make a purchase history
                $insert['name'] = "Referring reward - 10% of your friend's donation (" . $donatedFromPlayer['username'] . " - $currency $price - " . $amount . " GCs)";
                $insert['cost'] = $GcToRef;
                $insert['date'] = 'NOW()';
                $insert['account'] = $referrer;
                $db->query_insert("don_purchases", $insert);
                error_log("Processed that thing", 0);
                $referrerQuery = $db->query_first("SELECT `email` FROM `accounts` WHERE `id`='" . $db->escape($referrer) . "'");
                @notify($db, $referrer, $referrerQuery['email'], $insert['name'], $referrerEmail);

              }
            }

            error_log("Processed this thing!!!", 0);
            // send user an email indicating the transaction has completed.
            $to = $donatedFromPlayer['email'];
            $subject = "Thank you for your donation!";
            require_once './functions/functions_tickets.php';
            @notify($db, $cuid, $to, "Thank you for your donation!", $thankYouEmail);
            mail(WEBMASTER_EMAIL, "OwlGaming Donation $currency $price from " . $donatedFromPlayer['username'], $final, "From: " . DONATION_SERVER_MAIL);
            error_log("poop", 0);
            //Add an admin history
            //require_once './functions/functions_account.php';
            //@makeAdminHistory($db, $cuid, "OwlGaming Donation $currency $price from " . $donatedFromPlayer['username'], 6);
            $db->close();
            echo('OK');
      }
} else {
    // mark payment as failed
    mail(WEBMASTER_EMAIL, "OwlGaming Mobile Payments - Failed", $final, "From: donate@owlgaming.net");
    }
}

function check_signature($params_array, $secret) {
    ksort($params_array);

    $str = '';
    foreach ($params_array as $k => $v) {
        if ($k != 'sig') {
            $str .= "$k=$v";
        }
    }
    $str .= $secret;
    $signature = md5($str);

    return ($params_array['sig'] == $signature);
}

?>
