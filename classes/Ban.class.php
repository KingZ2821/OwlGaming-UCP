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
require_once "$root/classes/User.class.php";

class Ban extends User {

    private function mtaConnect($echo = false) {
        require_once "$this->root/classes/Mta.class.php";
        @$mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
        @$serverOnline = $mtaServer->getResource("usercontrolpanel")->call("isServerOnline");
        if (!$serverOnline or $serverOnline[0] != 1) {
            if ($echo)
                echo "MTA Server is offline at the moment. Please try again later.";
            return false;
        }
        return $mtaServer;
    }

    private function checkSession($print_output = false) {
        if (!isset($_SESSION['userid'])) {
            if ($print_output) {
                echo "<center><h3>You don't have sufficient permission to access this area.</h3></center>";
            }
            return false;
        }
        return isset($_SESSION['userid']);
    }

    function canAccess($print_output = false, $ignore_2factor = false) {
        if (!$this->is_logged() or $this->is_banned()) {
            if ($print_output) {
                echo "<center><h3>You don't have sufficient permission to access this area.</h3></center>";
            }
            return false;
        }
        $groups = $_SESSION['groups'];
        require_once "$this->root/functions/functions.php";
        if (isPlayerScripter($groups) or isPlayerTrialAdmin($groups)) {
            if (!$ignore_2factor) {
                require_once "$this->root/classes/TwoFactor.class.php";
                $twofactor = new TwoFactor();
                if ($twofactor->is_two_factor_enabled()) {
                    if (!$twofactor->is_two_factor_valid()) {
                        if ($print_output) {
                            echo "<center><h3>You don't have sufficient permission to access this area.</h3></center>";
                            header('Location: /twofactor.php');
                        }
                        return false;
                    }
                } else {
                    if ($print_output) {
                        echo "<center><h3>You must enable Two-Factor Authentication to access this area.</h3></center>";
                    }
                    return false;
                }
            }
            return true;
        } else if ($print_output) {
            echo "<center><h3>You don't have sufficient permission to access this area.</h3></center>";
        }
        return false;
    }

    function show($limit = 20, $start = 0, $loadto = 'lib_mid', $search_by = null, $search_key = null) {
        $result = "";
        if ($this->canAccess()) {
            $nume = 0;
            $condition = '';
            $condition_text = '';
            if ($search_by == 1 and is_numeric($search_key)) {
                $condition .= " AND b.admin='$search_key' ";
                $condition_text = "(By admin ID '$search_key')";
            } elseif ($search_by == "account" and strlen($search_key) > 0) {
                $condition .= " AND a.username LIKE '%" . $this->db->escape($search_key) . "%' ";
                $condition_text = "(On account '$search_key')";
            } else if ($search_by == 2 and is_numeric($search_key)) {
                $condition .= " AND b.account='$search_key' ";
                $condition_text = "(On account ID '$search_key')";
            } else if ($search_by == 3 and strlen($search_key) == 32) {
                $condition .= " AND b.serial='" . $this->db->escape($search_key) . "' ";
                $condition_text = "(On serial '$search_key')";
            } else if ($search_by == 'serial' and strlen($search_key) > 0) {
                $condition .= " AND b.serial LIKE '%" . $this->db->escape($search_key) . "%' ";
                $condition_text = "(On serial '$search_key')";
            } else if ($search_by == 4 and strlen($search_key) > 0) {
                $condition .= " AND b.ip='" . $this->db->escape($search_key) . "' ";
                $condition_text = "(On IP address '$search_key')";
            } else if ($search_by == 'ip' and strlen($search_key) > 0) {
                $condition .= " AND b.ip LIKE '%" . $this->db->escape($search_key) . "%' ";
                $condition_text = "(On IP address '$search_key')";
            } elseif ($search_by == "id" and is_numeric($search_key)) {
                $condition .= " AND b.id = '" . $this->db->escape($search_key) . "' ";
            } elseif ($search_by == "admin" and strlen($search_key) > 0) {
                $condition .= " AND a2.username LIKE '%" . $this->db->escape($search_key) . "%' ";
                $condition_text = "(By admin '$search_key')";
            } elseif ($search_by == "reason" and strlen($search_key) > 0) {
                $condition .= " AND b.reason LIKE '%" . $this->db->escape($search_key) . "%' ";
                $condition_text = "(With reason '$search_key')";
            }
            //echo 'c'.$condition;
            $nume = $this->db->query_first("SELECT COUNT(b.id) AS count FROM bans b LEFT JOIN accounts a ON b.account=a.id LEFT JOIN accounts a2 ON b.admin=a2.id WHERE b.id>0 $condition")['count'];
            //die($nume);
            if ($nume > 0) {
                $sql = "SELECT b.admin, b.id, b.account, a.username, a2.username AS adminname, DATE_FORMAT(b.date,'%b %d, %Y %h:%i %p') AS start_date, "
                        . "CASE WHEN b.until IS NULL THEN 'Never' ELSE DATE_FORMAT(b.until,'%b %d, %Y %h:%i %p') END AS end_date, "
                        . "b.serial, b.ip FROM bans b LEFT JOIN accounts a ON b.account=a.id LEFT JOIN accounts a2 ON b.admin=a2.id WHERE b.id>0 $condition ORDER BY b.date DESC";
                $result .= "<h2>Total $nume ban records $condition_text</h2>";
            } else {
                $result .= "<center><p><i>Nothing</i></p></center>";
            }

            $eu = ($start - 0);                               // No of records to be shown per page.
            $this1 = $eu + $limit;
            $back = $eu - $limit;
            $next = $eu + $limit;
            if ($nume > 0) {
                $result .= "<table id='logtable' border='1' align=center width='100%'>";
                $result .= "<tr><td align=center><b>ID</b></td><td><b>Admin</b></td><td><b>Account</b></td><td><b>Serial</b></td><td><b>IP Address</b></td><td><b>Duration</b></td></tr>";
                $bans = $this->db->query($sql . " LIMIT $eu, $limit ");
                $i = 0;
                while ($ban = $this->db->fetch_array($bans)) {
                    $i = $i + 1;   //  increment for alternate color of rows
                    $result .= "<tr>";
                    $result .= "<td align=center><a href='#' onClick=\"load_ban('" . $ban['id'] . "'); return false;\" title='Show ban details'>" . $ban['id'] . "</td>";
                    $result .= "<td><a href='#' onClick=\"load_bans('20','0','lib_mid', '1', '" . $ban['admin'] . "'); return false;\" title='List all bans by " . $ban['adminname'] . "'>" . $ban['adminname'] . "</a></td>";
                    $result .= "<td><a href='#' onClick=\"load_bans('20','0','lib_mid', '2', '" . $ban['account'] . "'); return false;\" title='List all bans on account " . $ban['username'] . "'>" . $ban['username'] . "</a></td>";
                    $result .= "<td><a href='#' onClick=\"load_bans('20','0','lib_mid', '3', '" . $ban['serial'] . "'); return false;\" title='List all bans on serial " . $ban['serial'] . "'>" . $ban['serial'] . "</a></td>";
                    $result .= "<td><a href='#' onClick=\"load_bans('20','0','lib_mid', '4', '" . $ban['ip'] . "'); return false;\" title='List all bans on ip address " . $ban['ip'] . "'>" . $ban['ip'] . "</a></td>";
                    $result .= "<td>" . $ban['start_date'] . " to " . $ban['end_date'] . "</td>";
                    $result .= "</tr>";
                }
                $result .= "</table>";
                $this->db->free_result();
                if ($nume > $limit) {
                    $result .= "<br><table align='center' width='100%'><tr><td  align='left' width=5%>";
                    if ($back >= 0) {
                        $result .= "<a href='#' onclick=\"load_bans('$limit', '$back', '$loadto', '$search_by', '$search_key'); return false;\"><b>PREV</b></a>";
                    }
                    $result .= "</td><td align=center width=90%>";
                    $i = 0;
                    $l = 1;
                    for ($i = 0; $i < $nume; $i = $i + $limit) {
                        if ($i <> $eu) {
                            $result .= "<a href='#' onclick=\"load_bans('$limit', '$i', '$loadto', '$search_by', '$search_key');return false;\"><b>$l</b></a> ";
                        } else {
                            $result .= "<b>$l</b> ";
                        }        /// Current page is not displayed as link and given font color red
                        $l = $l + 1;
                    }
                    $result .= "</td><td  align='right' width=5%>";
                    if ($this1 < $nume) {
                        $result .= "<a href='#' onclick=\"load_bans('$limit', '$next', '$loadto', '$search_by', '$search_key');return false;\"><b>NEXT</b></a>";
                    }
                    $result .= "</td></tr></table></td></tr>";
                    $result .= "</table>";
                }
            }
        } else {
            $result .= "<center>Session timed out. Please relogin and try again.</center>";
        }
        echo $result;
    }

    function outputIndex($print_output = false) {
        if ($this->canAccess($print_output)) {
            require_once "$this->root/functions/functions.php";
            echo "<h2>Hey, " . getUserTitle($_SESSION['groups']) . ucfirst($_SESSION['username']) . "! Welcome to the Ban Manager!</h2>";
            echo "<p>There are only a few groups of users who have access to this page, if you're see this, it means you're in one of those permission groups. However, based on the permission group / staff rank you're currently in, you can only view certain information and perform certain actions on other players.</p>";
        }
    }

    function outputFilter($print_output = false) {
        if ($this->canAccess($print_output)) {
            echo '<h2>Ban Filter</h2>';
            echo "<form name='ban_search_form' action='load_bans();' method='POST' onsubmit=\"load_bans(); return false;\">"
            . "<table id='' border='0' align=center width='100%'>";
            echo "<tr>";
            echo "<td width=200><b>Search by:</b></td>";
            echo '<td><select id="keyword_type" style="width: 100%;">
                    <option value="id">Record ID</option>
                    <option value="account">Banned username</option>
                    <option value="serial">Banned serial number</option>
                    <option value="ip">Banned IP address</option>
                    <option value="reason">Ban reason</option>
                    <option value="admin">Banning admin</option>
                </select></td>';
            echo "</tr>";
            echo "<tr>";
            echo "<td><b>Keyword:</b></td>";
            echo '<td><input type="input" id="keyword" required maxlength="80" minlength="1" style="width: 99%;"></td>';
            echo "</tr>";
            //echo "<tr><td><b>Algorithm:</b></td>";
            //echo '<td>  <input type="radio" name="algo" value="exact" checked>Exact<input type="radio" name="algo" value="partial">Partial</td>';
            //echo "</tr>";
            echo '<td></td><td  align=center><input type="submit" id="search_btn" value="Apply" style="margin-left: 0px; margin-top: 0px; "/><input type="button" id="clear_search_btn" value="Clear" onClick="clear_filter();" style="margin-left: 0px; margin-top: 0px; "/></td>';
            //echo '<td></td>';
            echo "</tr>";
            echo "</table></form>";
            echo "";
        }
    }

    function show_ban_detail($id = null, $print_output = false) {
        if ($this->canAccess($print_output)) {
            if ($id and is_numeric($id) and $id > 0) {
                echo "<h2>Details of ban record #$id</h2>";
                $ban = $this->db->query_first("SELECT b.threadid, b.reason, b.admin, b.id, b.account, a.username, a2.username AS adminname, DATE_FORMAT(b.date,'%b %d, %Y %h:%i %p') AS start_date, "
                        . "CASE WHEN b.until IS NULL THEN 'Never' ELSE DATE_FORMAT(b.until,'%b %d, %Y %h:%i %p') END AS end_date, "
                        . "CASE WHEN b.until IS NOT NULL THEN DATEDIFF(b.until, b.date) ELSE 'Permanent' END AS length, "
                        . "CASE WHEN b.until IS NOT NULL THEN TIME_TO_SEC(TIMEDIFF(b.until,NOW())) ELSE 'Never' END AS remaining, "
                        . "b.serial, b.ip FROM bans b LEFT JOIN accounts a ON b.account=a.id LEFT JOIN accounts a2 ON b.admin=a2.id WHERE b.id=$id");
                if ($ban and $ban['id']) {
                    echo "<ul>"
                    . "<li><b>Banned Target:</b><ul>"
                    . "<li><b>Banned Account:</b> " . $ban['username'] . "</li>"
                    . "<li><b>Banned Serial:</b> " . $ban['serial'] . "</li>"
                    . "<li><b>Banned IP:</b> " . $ban['ip'] . "</li>"
                    . "</ul></li>"
                    . "<li><b>Duration:</b><ul>"
                    . "<li><b>Start date:</b> " . $ban['start_date'] . "</li>"
                    . "<li><b>End date:</b> " . $ban['end_date'] . "</li>"
                    . "<li><b>Length:</b> " . $ban['length'] . " (days)</li>"
                    . "<li><b>Remaining:</b> " . $this->formatRemainingTime($ban['remaining']) . "</li>"
                    . "</ul></li>"
                    . "<li><b>Banning Admin:</b> " . $ban['adminname'] . "</li>"
                    . "<li><b>Banning Reason:</b> " . $ban['reason'] . "</li>";
                    if ($ban['threadid'] and is_numeric($ban['threadid'])) {
                        $ban['threadid'] = "<a href='http://forums.owlgaming.net/index.php?showtopic=" . $ban['threadid'] . "' target='_blank'>http://forums.owlgaming.net/index.php?showtopic=" . $ban['threadid'] . "</a>";
                    } else {
                        $ban['threadid'] = "N/A";
                    }
                    echo "<li><b>Ban thread:</b> " . $ban['threadid'] . "</li>"
                    . "</ul>";
                    echo "<input type='button' value='Lift' id='lift_ban' onClick='lift(" . $ban['id'] . ")'>";
                } else {
                    echo "<center>Ban record does not exist or had been prematurely removed.</center>";
                }
            } else {
                echo "<center>Ban record does not exist or had been prematurely removed.</center>";
            }
        } else {
            echo "<center>Session timed out. Please relogin and try again.</center>";
        }
    }

    private function formatRemainingTime($sec) {
        if (is_numeric($sec)) {
            return date('H hour(s) i minute(s) s second(s)', $sec);
        }
        return $sec;
    }

    function lift($id) {
        if ($this->canAccess(true)) {
            if ($id and is_numeric($id) and $id > 0) {
                $ban = $this->db->query_first("SELECT * FROM bans WHERE id=$id");
                if ($ban and is_numeric($ban['id'])) {
                    if ($this->db->query("DELETE FROM bans WHERE id=$id")) {
                        echo "You have successfully lifted ban record #$id!";
                        require_once "$this->root/functions/functions_account.php";
                        require_once "$this->root/functions/functions.php";
                        @makeAdminHistory($this->db, $ban['account'], "UNBAN", 2, 0, 0, $_SESSION['userid']);
                        $mtaServer = $this->mtaConnect();
                        if ($mtaServer) {
                            @$mtaServer->getResource("global")->call("sendMessageToAdmins", "[UCP] " . getUserTitle($_SESSION['groups']) . $_SESSION['username'] . " has removed ban record #" . $ban['id'] . ".");
                        }
                    } else {
                        echo "Errors occurred while lifting the ban.";
                    }
                } else {
                    echo "Ban record does not exist or had been prematurely removed.";
                }
            } else {
                echo "Ban record does not exist or had been prematurely removed.";
            }
        } else {
            echo "Session timed out. Please relogin and try again.";
        }
    }

    function outputAddBan($print_output = false) {
        if ($this->canAccess($print_output) and $this->mtaConnect(true)) {
            ?>
            <h2>Add ban</h2>
            <form name='' action='#' method='POST' onsubmit="add_ban();
                                return false;">
                <div id="ab_step_0">
                    <p><b>Precautions</b></p>
                    <ul>
                        <li>Adding ban from the UCP relies on the availability of the MTA server.</li>
                        <li>You can use this feature only if MTA server is online and there is at least one member of the admin team in game.</li>
                        <li>If you're not online in game. The ban you're going to make here will be automatically executed by another member of the admin team online in game.</li>
                    </ul>
                    <input type="button" value="Next" onclick="add_ban_goto(1);">
                </div>
                <div id="ab_step_1">
                    <p><b>How would you like to add?</b></p>
                    <input type="radio" name="ab_step_1" value="automatic" checked>Automatic <i>(Input account name to ban, automatically ban serial & IP address belongs to said account)</i><br>
                    <input type="radio" name="ab_step_1" value="manual">Manual <i>(Input account name, serial & IP address to ban separately and partially)</i><br>
                    <p><b>Ban duration:</b> <input type="number" id="ab_duration" max="168" min="0" step="1" > (hours, 0 means permanent)</p>
                    <input type="button" onclick="add_ban_goto(0);" value="Back"><input type="button" value="Next" onclick="add_ban_goto(2);">
                </div>
                <div id="ab_step_2">
                    <p><b>Who do you want to ban?</b></p>
                    <input type="input" id="ab_account_1" maxlength="80" style="width: 500px;" placeholder="Username to ban, required" ><br>
                    <br>
                    <input type="button" onclick="add_ban_goto(1);" value="Back"><input type="submit" id="ab_submit_btn1" value="Save">
                </div>
                <div id="ab_step_3">
                    <p><b>What do you want to ban?</b></p>
                    <table id='' border='0' align=center width='100%'>
                        <tr>
                            <td width="200">
                                <b>Account: </b>
                            </td>
                            <td>
                                <input type="input" id="ab_account_2" maxlength="80" style="width: 99%;" placeholder="Username to ban, optional">
                            </td>
                        </tr>
                        <tr>
                            <td width="200">
                                <b>Serial: </b>
                            </td>
                            <td>
                                <input type="input" id="ab_serial" maxlength="32" style="width: 99%;" placeholder="Serial to ban, optional">
                            </td>
                        </tr>
                        <tr>
                            <td width="200">
                                <b>IP address: </b>
                            </td>
                            <td>
                                <input type="input" id="ab_ip" maxlength="15" style="width: 99%;" placeholder="IP address to ban, support range ban with * wildcards, optional">
                            </td>
                        </tr>
                    </table>
                    <br>
                    <input type="button" onclick="add_ban_goto(1);" value="Back"><input type="submit" id="ab_submit_btn2" value="Save">
                </div>
            </form>
            <script type="text/javascript">
                $('#ab_step_1').slideUp(0);
                $('#ab_step_2').slideUp(0);
                $('#ab_step_3').slideUp(0);
            </script>
            <?php
        }
    }

    private function getAccountToBan($account) {
        if ($account and strlen($account) > 0 and $this->mtaConnect(true)) {
            $acc = $this->db->query_first("SELECT id, username, mtaserial, ip, admin FROM accounts WHERE username='" . $this->db->escape($account) . "'");
            if ($acc and is_numeric($acc['id'])) {
                if ($_SESSION['admin'] >= $acc['admin']) {
                    return $acc;
                } else {
                    echo "You can not perform this action on this player because they are higher ranked than you.";
                }
                //exports['admin-system']:addAdminHistory(targetPlayer, thePlayer, reason, 2 , rhours)
            } else {
                echo "Account name does not exist, please check and try again.";
            }
        } else {
            echo "Errors occurred while adding ban.";
        }
        return false;
    }

    function addBan($method, $account1 = null, $account2 = null, $serial = null, $ip = null) {
        if ($this->canAccess(true) and $this->mtaConnect(true)) {
            if ($method == 'automatic') {
                $acc = $this->getAccountToBan($account1);
                if ($acc) {
                    
                }
            }
        }
    }

}
