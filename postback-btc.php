<?php

/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * File created by ducch, 20-10-2015
 * ***********************************************************************************************************************
 */

$thankYouEmail = "Dear my friend,

I want to express my appreciation for your generosity in support of OwlGaming Community. Your personal commitment was incredibly helpful and allowed us to reach our goal. Your assistance means so much to me but even more to the Community. Thank you from all of us!

In return, GameCoins should have been added into the donated account instantly. If for some reasons, you don't recieve GC(s) within next 2 hour(s) or have any other donation issue/question, please visit our Support Center at http://owlgaming.net/support.php to submit a ticket under 'Donation issue or question'.

Sincerely,
OwlGaming Community
OwlGaming Development Team";

require_once './config.inc.php';
require_once './functions/base_functions.php';
@$invoice_id = $_GET['invoice_id']; //invoice_id is passed back to the callback URL
@$secret = $_GET['secret'];
@$transaction_hash = $_GET['transaction_hash'];
@$input_transaction_hash = $_GET['input_transaction_hash'];
@$input_address = $_GET['input_address'];
@$value_in_satoshi = $_GET['value'];
@$value_in_btc = $value_in_satoshi / 100000000;
@$destination_address = $_GET['destination_address'];

//Commented out to test, uncomment when live
if (@$_GET['test'] == true and BITCOIN_TEST_MODE == false) {
    mail(WEBMASTER_EMAIL, 'OwlGaming Donation Failed BTC ' . $value_in_btc, "The transaction is not completed\n\n Postback received testing signal while the system isn't in testing mode.\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    die("Error Code: " . __LINE__);
}

if ($secret != BITCOIN_SECRET) {
    mail(WEBMASTER_EMAIL, 'OwlGaming Donation Failed BTC ' . $value_in_btc, "The transaction is not completed\n\n Secrets mismatched. Received '$secret'\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    die("Error Code: " . __LINE__);
}

require_once './classes/Database.class.php';

$db = new Database("MTA");
$db->connect();
$query = "SELECT i.*, username, email, referrer FROM btc_invoices i LEFT JOIN accounts a ON i.account=a.id WHERE invoice_id=" . $db->escape($invoice_id);
$invoice = $db->query_first($query);

if (!$invoice or is_null($invoice['invoice_id'])) {
    mail(WEBMASTER_EMAIL, 'OwlGaming Donation Failed BTC ' . $value_in_btc, "The transaction is not completed\n\n Invoice not found with invoice_id = '$invoice_id' $ query = '$query'\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    $db->close();
    die("Error Code: " . __LINE__);
}

if (is_null($invoice['username'])) {
    mail(WEBMASTER_EMAIL, "OwlGaming Donation(#$invoice_id) Failed - BTC $value_in_btc", "The transaction is not completed\n\n Invoice found with invoice_id = '$invoice_id' but account was not found with id '" . $invoice['account'] . "'\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    $db->close();
    die("Error Code: " . __LINE__);
}

if ($destination_address != BITCOIN_ADDRESS) {
    mail(WEBMASTER_EMAIL, "OwlGaming Donation(#$invoice_id) Failed - BTC $value_in_btc", "The transaction is not completed\n\n Destination address mismatched. Received '$destination_address'\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    $db->close();
    die("Error Code: " . __LINE__);
}

if ($invoice['input_address'] != $input_address or $value_in_btc < $invoice['price_in_btc']) {
    mail(WEBMASTER_EMAIL, "OwlGaming Donation(#$invoice_id) Failed - BTC $value_in_btc", "The transaction is not completed\n\n input_addresses or value_in_btc mismatched. Received '$input_address' & '$value_in_btc'\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    $db->close();
    die("Error Code: " . __LINE__);
}

if ($invoice['game_coin'] < 1 or $invoice['paid'] != 0) {
    mail(WEBMASTER_EMAIL, "OwlGaming Donation(#$invoice_id) Failed - BTC $value_in_btc", "The transaction is not completed\n\n Invoice is already paid or GC is negative. Received '" . $invoice['paid'] . "' & '" . $invoice['game_coin'] . "'\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    $db->close();
    die("Error Code: " . __LINE__);
}

if (BITCOIN_TEST_MODE or $db->query("UPDATE btc_invoices SET paid=1 WHERE invoice_id=" . $invoice['invoice_id'] . " AND paid=0")) {
    if (BITCOIN_TEST_MODE or $db->query("UPDATE accounts SET credits=credits+" . $db->escape($invoice['game_coin']) . " WHERE id=" . $db->escape($invoice['account']))) {
        $referrer = $invoice['referrer'];
        if ($referrer and is_numeric($referrer) and $referrer > 0) {
            $GcToRef = ceil($invoice['game_coin'] / 10);
            if ($GcToRef > 0) {
                $db->query("UPDATE `accounts` SET `credits`=`credits`+" . $GcToRef . " WHERE `id`='" . $referrer . "'");
                $insert = array();
                //Make a purchase history
                $insert['name'] = "Referring reward - 10% of your friend's donation (" . $invoice['username'] . " - $" . $invoice['price_in_usd'] . " - " . $invoice['game_coin'] . " GCs)";
                $insert['cost'] = $GcToRef;
                $insert['date'] = 'NOW()';
                $insert['account'] = $referrer;
                $db->query_insert("don_purchases", $insert);
                @notify($db, $referrer, false, $insert['name'], $thankYouEmail);
            }
        }

        $mail_subj = "OwlGaming Donation(#$invoice_id) Processed - BTC $value_in_btc";
        $mail_cont = "Account: " . $invoice['username'] . "\n"
                . "GC: " . $invoice['game_coin'] . "\n"
                . "Price in USD: " . $invoice['price_in_usd'] . "\n"
                . "Price in BTC: " . $invoice['price_in_btc'] . "\n"
                . "Transaction Hash: $transaction_hash\n"
                . "Input Transaction Hash: $input_transaction_hash\n"
                . "Input Address: $input_address\n"
                . "Transaction Fee: " . $invoice['fee_percent'] . "%\n"
                . "\n\nFROM IP: " . get_client_ip();

        mail(WEBMASTER_EMAIL, $mail_subj, $mail_cont, "From: " . DONATION_SERVER_MAIL);
        @notify($db, $invoice['account'], $invoice['email'], $mail_subj, $mail_cont . "\n\n" . $thankYouEmail);
        $db->close();
        die("OK: " . __LINE__);
    } else {
        mail(WEBMASTER_EMAIL, "OwlGaming Donation(#$invoice_id) Failed - BTC $value_in_btc", "The transaction is not completed\n\n Failed to update credits to accounts\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
        $db->close();
        die("Error Code: " . __LINE__);
    }
} else {
    mail(WEBMASTER_EMAIL, "OwlGaming Donation(#$invoice_id) Failed - BTC $value_in_btc", "The transaction is not completed\n\n Failed to update btc_invoices\n\nFROM IP: " . get_client_ip(), "From: " . DONATION_SERVER_MAIL);
    $db->close();
    die("Error Code: " . __LINE__);
}

        