<?php

/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 10-07-2015
 * ***********************************************************************************************************************
 */

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/User.class.php";

class Supportcenter extends User{
    function can_access_sp_backend() {
        require_once "$this->root/functions/functions.php";
        echo '<h1>TESTING' . $_SESSION['supporter'] . '</h1>';
        return $this->is_logged() and ($_SESSION['admin'] > 0 or $_SESSION['supporter'] > 0 or $_SESSION['vct'] > 0 or $_SESSION['scripter'] > 0 or $_SESSION['mapper'] > 0);
    }
    
    function has_ongoing_ticket_assigned(){
        if ($this->can_access_sp_backend()) {
            $this->dbConnect();
            $userid = $_SESSION['userid'];
            $tc = $this->db->query_first("SELECT COUNT(id) AS total FROM tc_tickets WHERE assign_to=$userid AND (status=1 or status=2 or status=3)")['total'];
            $this->dbClose();
            return $tc;
        } else {
            return false;
        }
    }
}
