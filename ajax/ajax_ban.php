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

$action = isset($_POST['action']) ? $_POST['action'] : null;
require_once '../classes/Ban.class.php';
if ($action == 'load_bans') {
    $start = (isset($_POST['start']) ? $_POST['start'] : 0);
    $limit = (isset($_POST['limit']) ? $_POST['limit'] : 20);
    $loadto = (isset($_POST['loadto']) ? $_POST['loadto'] : 'lib_mid');
    $search_by = (isset($_POST['search_by']) ? $_POST['search_by'] : null);
    $search_key = (isset($_POST['search_key']) ? $_POST['search_key'] : null);
    $ban = new Ban();
    $ban->dbConnect();
    $ban->show($limit, $start, $loadto, $search_by, $search_key);
    $ban->dbClose();
} else if ($action == 'load_ban') {
    $ban = new Ban();
    $ban->dbConnect();
    $ban->show_ban_detail($_POST['id']);
    $ban->dbClose();
} else if ($action == 'lift_ban') {
    $ban = new Ban();
    $ban->dbConnect();
    $ban->lift($_POST['id']);
    $ban->dbClose();
} else if ($action == 'add_ban_gui') {
    $ban = new Ban();
    $ban->outputAddBan();
} else if ($action == 'add_ban') {
    $ban = new Ban();
    
}

