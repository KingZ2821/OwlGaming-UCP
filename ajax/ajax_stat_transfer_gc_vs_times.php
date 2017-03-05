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

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    echo "Session has timed out.";
} else {
    $transferCost = 750;
    require_once("../classes/Database.class.php");
    $db = new Database("MTA");
    $db->connect();
    $q = $db->query_first("SELECT credits FROM accounts WHERE id='".$_SESSION['userid']."' ");
    echo "You are currently having <b>".number_format($q['credits'])." GC(s)</b> so that you will be able to do <b>".floor($q['credits'] / $transferCost)." transfer(s)</b>";
}
