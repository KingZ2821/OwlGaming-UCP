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

$perms = '';
if (isset($_SESSION['groups'])) {
    $perms = $_SESSION['groups'];
}

require_once 'functions.php';
function canUserAccessPlayerManager($groups) {
    return isPlayerTrialAdmin($groups) or isPlayerSupporter($groups) or isPlayerScripter($groups) or isPlayerMappingTeamLeader($groups);
}

function canUserManageAdminTeam($groups) {
    return isPlayerSeniorAdmin($groups);
}
