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

class Loa extends User {

    function can_access_loa() {
        require_once "$this->root/functions/functions.php";
        return $this->is_logged() and isPlayerSupporter($_SESSION['groups']) or isPlayerVCT($_SESSION['groups']) or isPlayerScripter($_SESSION['groups']) or isPlayerMappingTeamMember($_SESSION['groups']);
    }

    function get_loa($user_id = null, $effective = 1) {
        if ($this->can_access_loa()) {
            $user_id = $user_id ? ("user_id=$user_id") : "1=1";
            $this->dbConnect();
            if ($effective == 1) {
                $loa = $this->db->fetch_all_array("SELECT l.*, a.username FROM account_loa l LEFT JOIN accounts a ON l.user_id=a.id WHERE $user_id AND effective=$effective AND `from` IS NOT NULL AND `to` IS NOT NULL AND `from`<=NOW() AND `to`>=NOW() ORDER BY `from`");
            } else {
                $loa = $this->db->fetch_all_array("SELECT l.*, a.username FROM account_loa l LEFT JOIN accounts a ON l.user_id=a.id WHERE $user_id AND ( effective=$effective OR `from` IS NULL OR `to` IS NULL OR `to`<NOW() ) ORDER BY `from`");
            }
            $this->dbClose();
            return $loa;
        } else {
            return false;
        }
    }

    function set_loa($length, $reason) {
        if ($this->can_access_loa() and $length and $length and is_numeric($length) and $length >= 5 and $length <= 21) {
            $this->dbConnect();
            $user_id = $_SESSION['userid'];
            $reason = $this->db->escape(strip_tags($reason));
            $this->db->query("INSERT INTO account_loa SET user_id=$user_id, `from`=NOW(), `to`=NOW() + INTERVAL $length DAY, reason='$reason'");
            $this->dbClose();
            return true;
        }
        return false;
    }

    function output_effective_loas($loas = null) {
        if (!$loas) {
            $loas = $this->get_loa();
        }
        echo "<ul>";
        $count = 0;
        foreach ($loas as $effective_loa) {
            $return = '';
            if (isset($_SESSION['userid']) and $effective_loa['user_id'] == $_SESSION['userid']) {
                $return = "<input type='button' value='Return' onclick='loa_return(" . $effective_loa['loa_id'] . ");'/>";
            }
            echo "<li><b>" . $effective_loa['username'] . "</b> $return"
            . "<ul><i>"
            . "<li>Begin on " . $effective_loa['from'] . "</li>"
            . "<li>Return on " . $effective_loa['to'] . "</li>"
            . "<li>Reason: " . $effective_loa['reason'] . "</li>"
            . "</i></ul>"
            . "</li>";
            $count +=1;
        }
        if ($count == 0) {
            echo "<li>No leave of absence found. Assuming everyone is active.</li>";
        }
        echo "</ul>";
    }

    function output_previous_loas($loas = null) {
        if (!$loas) {
            $loas = $this->get_loa(null, 0);
        }
        echo "<ul>";
        $count = 0;
        foreach ($loas as $effective_loa) {
            echo "<li><b>" . $effective_loa['username'] . "</b> "
            . "<ul><i>"
            . "<li>Begin on " . $effective_loa['from'] . "</li>"
            . "<li>Return on " . $effective_loa['to'] . "</li>"
            . "<li>Reason: " . $effective_loa['reason'] . "</li>"
            . "</i></ul>"
            . "</li>";
            $count +=1;
        }
        if ($count == 0) {
            echo "<li>No leave of absence found. Assuming everyone is active.</li>";
        } else {
            if ($this->can_access_loa() and isPlayerLeadAdmin($_SESSION['groups'])) {
                echo "<input type='button' value='Clean up' onclick='loa_clean();'/>";
            }
        }
        echo "</ul>";
    }

    function has_loa($userid = null, $loas) {
        $userid = $userid ? $userid : $_SESSION['userid'];
        foreach ($loas as $loa) {
            if ($loa['user_id'] == $userid and $loa['effective'] == 1) {
                return true;
            }
        }
        return false;
    }

    function return_from_loa($loa_id) {
        if ($this->can_access_loa() and isset($loa_id) and $loa_id > 0) {
            $this->dbConnect();
            $this->db->query("UPDATE account_loa SET effective=0 WHERE loa_id=$loa_id AND user_id=" . $_SESSION['userid']);
            $this->dbClose();
            return true;
        }
        return false;
    }
    
    function clean() {
        if ($this->can_access_loa() and isPlayerLeadAdmin($_SESSION['groups']) ) {
            $this->dbConnect();
            $this->db->query("DELETE FROM account_loa WHERE effective=0 OR `from` IS NULL OR `to` IS NULL OR `to`<NOW() ");
            $this->dbClose();
            return true;
        }
        return false;
    }

}
