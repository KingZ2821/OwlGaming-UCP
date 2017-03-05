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

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/Session.class.php";
$session = new Session();
$session->start_session('_owlgaming');

$userID = $_SESSION['userid'];

if (!isset($userID) or ! $userID) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    $action = isset($_POST['action']) ? $_POST['action'] : false;
    if ($action == 'btc_create_invoice') {
        $dollar = isset($_POST['dollar']) ? $_POST['dollar'] : false;
        if ($dollar and is_numeric($dollar) and floor($dollar) >= 1) {
            require_once '../config.inc.php';
            require_once '../functions/base_functions.php';
            require_once '../classes/Database.class.php';
            $dollar = floor($dollar);
            $gc = getGsFromDollar($dollar)[0];
            $btc = file_get_contents(BITCOIN_ROOT . "tobtc?currency=USD&value=$dollar");
            
            $my_callback_url = 'https://www.owlgaming.net/postback-btc.php?invoice_id=' . $invoice_id . '&secret=' . BITCOIN_SECRET;
            $parameters = 'method=create&address=' . BITCOIN_ADDRESS . '&callback=' . urlencode($my_callback_url);
            $response = file_get_contents('https://blockchain.info/api/receive?' . $parameters);
            $object = json_decode($response);
            
            $db = new Database("MTA");
            $db->connect();
            // clean up
            $db->query("DELETE FROM btc_invoices WHERE invoice_id > 0 AND paid=0 AND ( account=$userID OR date < NOW() - INTERVAL ".BITCOIN_CLEANUP_DAY." DAY ) ");
            $invoice_id = $db->query_insert('btc_invoices', array('price_in_usd' => $dollar, 'price_in_btc' => $btc, 'fee_percent' => $object->fee_percent, 'game_coin' => $gc, 'account' => $userID, 'input_address' => $object->input_address));
            $db->close();
            if ($invoice_id and is_numeric($invoice_id)) {
                ?>
                <p><b>Please review your donation thoroughly before making payment:</b></p>
                <table id="logtable" border="1" align="center">
                    <tr>
                        <td bgcolor="#C3C3C3" colspan="2" align="center">
                            <b>Donation #<?= $invoice_id ?></b>
                        </td>
                    </tr>
                    <tr>
                        <td>Account</td>
                        <td><?= $_SESSION['username'] ?></td>
                    </tr>
                    <tr>
                        <td>GameCoins</td>
                        <td><?= $gc ?></td>
                    </tr>
                    <tr>
                        <td>Price in USD</td>
                        <td><?= $dollar ?></td>
                    </tr>
                    <tr>
                        <td>Price in BTC</td>
                        <td><?= $btc ?></td>
                    </tr>
                    <tr>
                        <td>Transaction Fee</td>
                        <td><?= $object->fee_percent ?>%</td>
                    </tr>
                </table>
                
                <p><b>To finalize the donation, please send: </b></p>
                <div style="text-align:center; font-size:30px; font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New; background-color:#C3C3C3; padding:10px; padding:10px; border: 1px solid #000000;">Ƀ <?= $btc ?></div>
                <p><b>To address: </b></p>
                <div style="text-align:center; font-size:30px; font-family:Consolas,Monaco,Lucida Console,Liberation Mono,DejaVu Sans Mono,Bitstream Vera Sans Mono,Courier New; background-color:#C3C3C3; padding:10px; padding:10px; border: 1px solid #000000;"><?= $object->input_address ?></div>
                <img style="margin:5px" id="qrsend" src="<?= BITCOIN_ROOT ?>qr?data=bitcoin:<?= $object->input_address ?>%3Famount=<?= $btc ?>%26label=Pay-Demo&size=150" alt=""/>
                <?php
            }
        }
    }
}

