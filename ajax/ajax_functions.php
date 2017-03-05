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

require_once("../classes/Database.class.php");
if (isset($_POST['getUserIdFromUsername'])) {
    $db = new Database("MTA");
    $db->connect();
    $q = $db->query_first("SELECT id FROM accounts WHERE username='". $db->escape($_POST['getUserIdFromUsername'])."' ");
    if ($q and $q['id']) {
        echo $q['id'];
    }
    $db->close();
}


