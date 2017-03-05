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

require_once "$root/functions/functions_tickets.php";
require_once "$root/classes/Database.class.php";
$db = new Database("MTA");
$db->connect();
$step = (isset($_POST['step']) ? $_POST['step'] : null);

$protocol = (empty($_SERVER['HTTPS']) or $_SERVER['HTTPS'] == 'off') ? 'http' : 'https';
$host = $_SERVER['HTTP_HOST'];
$script = $_SERVER['SCRIPT_NAME'];
@$params = $_SERVER['QUERY_STRING'];
$currentUrl = $protocol . '://' . $host;
$userID = (isset($_SESSION['userid']) ? $_SESSION['userid'] : null);

if ($step == "change_status") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        if ($db->query("UPDATE tc_tickets SET status=" . $db->escape($_POST['state']) . " WHERE id=" . $db->escape($_POST['tcid'])))
            echo htmlspecialchars($_POST['tcid'], ENT_QUOTES, 'UTF-8');
        else
            echo "error";
    } else
        echo "error";
} else if ($step == "toggle_private") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        if ($db->query("UPDATE tc_tickets SET private=" . $db->escape($_POST['state']) . " WHERE id=" . $db->escape($_POST['tcid'])))
            echo htmlspecialchars($_POST['tcid'], ENT_QUOTES, 'UTF-8');
        else
            echo "error";
    } else
        echo "error";
} else if ($step == "add_subcriber") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        $sub = $db->query_first("SELECT id, username, email FROM accounts WHERE username='" . $db->escape($_POST['subcriber']) . "' LIMIT 1");
        if ($sub and $sub['id'] and is_numeric($sub['id'])) {
            $ticket = $db->query_first("SELECT subcribers, creator, assign_to, status FROM tc_tickets WHERE id=" . $db->escape($_POST['tcid']));
            $isExisted = (($ticket['creator'] == $sub['id']) or ( $ticket['assign_to'] == $sub['id']));
            if (!$isExisted) {
                $checks = explode(",", $ticket['subcribers']);
                foreach ($checks as $check) {
                    if ($check == $sub['id']) {
                        $isExisted = true;
                        break;
                    }
                }
            }
            if ($isExisted)
                echo "error";
            else {
                $db->query("UPDATE tc_tickets SET subcribers=CONCAT(subcribers, '" . $sub['id'] . ",') WHERE id=" . $db->escape($_POST['tcid']));
                @addTicketComment($db, $_POST['tcid'], $userID, "Subscribed " . $sub['username'] . " to this ticket.", 0, $ticket);
                echo htmlspecialchars($_POST['tcid'], ENT_QUOTES, 'UTF-8');
            }
        } else
            echo "error";
    } else
        echo "error";
} else if ($step == "remove_subs") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        $ticket = $db->query_first("SELECT subcribers, creator, assign_to, status FROM tc_tickets WHERE id=" . $db->escape($_POST['tcid']));
        $checks = explode(",", $ticket['subcribers']);
        $newsubs = array();
        foreach ($checks as $check) {
            if ($check and is_numeric($check) and $check != $_POST['sub'])
                array_push($newsubs, $check);
        }
        $newsubs = "," . implode(",", $newsubs) . ",";
        $db->query("UPDATE tc_tickets SET subcribers='" . $newsubs . "' WHERE id=" . $_POST['tcid']);
        @addTicketComment($db, $db->escape($_POST['tcid']), $userID, "Unsubscribed " . $db->escape($_POST['subname']) . " from this ticket.", 0, $ticket);
        echo htmlspecialchars($_POST['tcid'], ENT_QUOTES, 'UTF-8');
    } else
        echo "error";
} else if ($step == "reassign_ticket") {
    if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        if (@assignTicket($db, $_POST['tcid'], $_POST['assignto']) and @ addTicketComment($db, $db->escape($_POST['tcid']), $userID, "Re-assigned to " . $db->escape($_POST['assigntoname']), 0, false, true)) {
            echo htmlspecialchars($_POST['tcid'], ENT_QUOTES, 'UTF-8');
        }
    } else {
        echo "error";
    }
} else if ($step == "delete_my_comment") {
    $cid = isset($_POST['cid']) ? $_POST['cid'] : 0;
    if (is_numeric($cid) and $cid > 0) {
        if ($userID) {
            //Get some data first
            $comment = $db->query_first("SELECT c.id, c.poster, c.tcid, t.status FROM tc_comments c LEFT JOIN tc_tickets t ON c.tcid=t.id WHERE c.id=" . $cid);
            if ($comment) {
                if ($comment['poster'] == $userID) {
                    if ($comment['status'] != -1 and $comment['status'] != 4) { // Not locked or closed
                        $last_comment = $db->query_first("SELECT id FROM tc_comments WHERE tcid=" . $comment['tcid'] . " ORDER BY date DESC LIMIT 1");
                        if ($last_comment and $last_comment['id'] == $cid) {
                            if ($db->query("DELETE FROM tc_comments WHERE id=" . $cid)) {
                                echo $comment['tcid'];
                            } else {
                                echo "Errors occured while deleting this comment. Try again.";
                            }
                        } else {
                            echo "You can only delete the last comment.";
                        }
                    } else {
                        echo "You can not delete comments from a closed/locked ticket.";
                    }
                } else {
                    echo "You can only delete your own comments.";
                }
            } else {
                echo "Comment does not exist.";
            }
        } else {
            echo "Please log in to delete comment.";
        }
    } else {
        echo "Errors occured while deleting this comment. Try again.";
    }
} else if ($step == "tc_switch") {
    if (isset($userID) and canUserAccessTcBackEnd($_SESSION['groups'])) {
        if ($db->query("UPDATE accounts SET tc_backend=" . $_POST['state'] . " WHERE id=" . $userID . " AND (admin>0 OR supporter>0 OR vct>0 OR scripter>0 OR mapper>0 )")) {
            $_SESSION['tc_backend'] = $_POST['state'];
        }
    }
} else if ($step == "load_backend_midtop") {
    if (canUserAccessTcBackEnd($_SESSION['groups']) and isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
        echo "<br>";
        $condition = "";
        $chk_closed = " checked ";
        $chk_locked = " checked ";
        if ($_POST['closed'] == 0) {
            $condition .= " AND status!=4 ";
            $chk_closed = "";
        }
        if ($_POST['locked'] == 0) {
            $condition .= " AND status!=-1 ";
            $chk_locked = "";
        }
        echo "<h2>Unassigned Tickets</h2>";
        $unassigneds = $db->fetch_all_array("SELECT subject, username AS creatorname, status, t.id, type, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id WHERE assign_to=0 " . $condition . " ORDER BY t.last_updated DESC, t.id");
        if ($unassigneds and $db->affected_rows > 0) {
            foreach ($unassigneds as $unassigned) {
                echo getTicketStatus($unassigned['status'], $unassigned['id']) . " <a href='#' onclick='load_ticket(" . $unassigned['id'] . "); return false;'>#" . $unassigned['id'] . " - " . getTicketType($unassigned['type']) . " (Creator: " . $unassigned['creatorname'] . ") - " . $unassigned['date'] . " (" . formatDays($unassigned['dateago']) . ")</a><br>";
            }
        } else {
            echo "<center><p><i>There is no unassigned ticket at the moment.</i></p></center>";
        }
        echo "<br>";
        echo "<input type='checkbox' id='chk_global_closed' onclick='checkBoxGlobalChanges(); return false;' " . $chk_closed . "/> <i>Include closed tickets</i><br>";
        echo "<input type='checkbox' id='chk_global_locked' onclick='checkBoxGlobalChanges(); return false;' " . $chk_locked . "/> <i>Include locked tickets</i>";
        echo "<br>";
    }
} else if ($step == "load_my_tickets") {
    if (isset($userID)) {
        $start = (isset($_POST['start']) ? $_POST['start'] : 0);
        $limit = (isset($_POST['limit']) ? $_POST['limit'] : 10);
        $loadto = (isset($_POST['loadto']) ? $_POST['loadto'] : null);

        $condition = "";
        $chk_closed = " checked ";
        $chk_locked = " checked ";
        if ($_POST['closed'] == 0) {
            $condition .= " AND status!=4 ";
            $chk_closed = "";
        }
        if ($_POST['locked'] == 0) {
            $condition .= " AND status!=-1 ";
            $chk_locked = "";
        }
        echo "<h2>My Tickets</h2>";
        $nume = 0;
        if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
            $nume = $db->query_first("SELECT COUNT(id) AS count FROM tc_tickets WHERE (assign_to=" . $userID . " OR subcribers LIKE '%," . $userID . ",%')" . $condition)['count'];
            if ($nume > 0) {
                echo "<p>The following tickets were assigned to you or subscribed by you, click to view in details.</p>";
                $sql = "SELECT username AS creatorname, status, t.id, type, subject, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), date) AS dateago, DATE_FORMAT(t.last_updated,'%b %d, %Y at %h:%i %p') AS fudate, DATEDIFF(NOW(), t.last_updated) AS udateago  FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id WHERE (assign_to=" . $userID . " OR subcribers LIKE '%," . $userID . ",%')" . $condition . "  ORDER BY t.last_updated ASC, t.id DESC";
            } else
                echo "<center><p><i>You don't have any tickets assigned to you at the moment.</i></p></center>";
        } else {
            $nume = $db->query_first("SELECT COUNT(id) AS count FROM tc_tickets WHERE (creator=" . $userID . " OR subcribers LIKE '%," . $userID . ",%')" . $condition)['count'];
            if ($nume > 0) {
                echo "<p>The following tickets were created by you or related to you, click to view in details.</p>";
                $sql = "SELECT status, id, type, subject, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets WHERE (creator=" . $userID . " OR subcribers LIKE '%," . $userID . ",%')" . $condition . " ORDER BY last_updated DESC, id DESC";
            } else
                echo "<center><p><i>You don't have any tickets at the moment.</i></p></center>";
        }

        $eu = ($start - 0);                               // No of records to be shown per page.
        $this1 = $eu + $limit;
        $back = $eu - $limit;
        $next = $eu + $limit;
        if ($nume > 0) {
            $tickets = $db->query($sql . " LIMIT $eu, $limit ");
            $i = 0;
            while ($myticket = $db->fetch_array($tickets)) {
                $i = $i + 1;   //  increment for alternate color of rows
                echo getTicketStatus($myticket['status'], $myticket['id']) . " <a href='#' onclick='load_ticket(" . $myticket['id'] . "); return false;'>#" . $myticket['id'] . " (" . formatDays($myticket['dateago']) . ") - " . getTicketType($myticket['type']) . " (" . trimSubject($myticket['subject'], 80) . ") - Last updated " . formatDays($myticket['udateago']) . ".</a><br>";
            }
            $db->free_result();
            if ($nume > $limit) {
                echo "<br><table align = 'center' width='100%'><tr><td  align='left' width=5%>";
                if ($back >= 0) {
                    ?>
                    <a href='#' onclick="load_my_tickets('<?php echo $limit ?>', '<?php echo $back ?>', '<?php echo $loadto ?>');
                            return false;"><b>PREV</b></a>
                       <?php
                   }
                   echo "</td><td align=center width=90%>";
                   $i = 0;
                   $l = 1;
                   for ($i = 0; $i < $nume; $i = $i + $limit) {
                       if ($i <> $eu) {
                           ?>
                        <a href='#' onclick="load_my_tickets('<?php echo $limit ?>', '<?php echo $i ?>', '<?php echo $loadto ?>');
                                return false;"><b><?php echo $l; ?></b></a>
                           <?php
                       } else {
                           echo "<b>$l</b>";
                       }        /// Current page is not displayed as link and given font color red
                       $l = $l + 1;
                   }
                   echo "</td><td  align='right' width=5%>";
                   if ($this1 < $nume) {
                       ?>
                    <a href='#' onclick="load_my_tickets('<?php echo $limit ?>', '<?php echo $next ?>', '<?php echo $loadto ?>');
                            return false;"><b>NEXT</b></a>
                       <?php
                   }
                   echo "</td></tr></table></td></tr>";
                   echo "</table>";
               }
           }


           echo "<input type='checkbox' id='chk_my_ticket_closed' onclick='checkBoxMyTicketChanges(); return false;' " . $chk_closed . "/> <i>Include closed tickets</i><br>";
           echo "<input type='checkbox' id='chk_my_ticket_locked' onclick='checkBoxMyTicketChanges(); return false;' " . $chk_locked . "/> <i>Include locked tickets</i><br>"
           . "<br><input type=button onclick='load_my_tickets(); return false;' value='Refresh'>";
       }
   } else if ($step == "load_submit_form") {
       ?>
    <form action="" onsubmit="client_submit_ticket(<?php echo $_POST['type']; ?>);
            return false;">
        <table border="0">
            <?php
            echo "<br><h2>Submit a new ticket - " . getTicketType($_POST['type']) . "</h2>";
            if (!isset($userID) or ! $userID or $userID < 1) {
                echo "<center><p><i>You must be logged in to submit this type of ticket.</i></p></center>";
            } else {
                if ($_POST['type'] == 2) {
                    echo '<tr><td colspan="2">';
                    echo '<b>Upon submitting your ban appeal, you hereby agree that:</b><br>
<ul>
<li>You will not submit more than one appeal a week (unless instructed to do so by a staff member).</li>
<li>If your appeal is unsuccessful, you will serve your ban without attempting to enter the server on another account and if caught doing so; you will be subject to further discipline.</li>
<li>You will tell the truth and continue to do so throughout the entirety of your appeal.</li>
<li>You have not left any relevant information out of your appeal.</li></ul><br>';
                    echo "<b>Select account type to be unbanned: </b>";
                    echo '<select id="unban_account_type" onchange="unban_select();">';
                    echo '<option value="None" selected>None</option>';
                    echo '<option value="MTA" >MTA</option>';
                    echo '<option value="Forums" >Forums</option>';
                    echo '</td></tr>'
                    . '<tr><td colspan=2><div id="unban_below"></div> </td></tr>';
                } else if ($_POST['type'] == 8) {
                    echo '<tr><td colspan="2">';
                    echo '<p><b>Please choose a history record to appeal with:</b><br><i>Multiple records are allowed however, they should be related. Otherwise, you should make them in separated tickets.</i></p>'
                    . '<div id="histories">';

                    require_once '../functions/functions.php';
                    //SELECT DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS date, action, h.admin AS hadmin, reason, duration, a.username as username, "
                    //. "c.charactername AS user_char, h.id as recordid "
                    $histories = getAdminHistory($db, $userID);
                    echo '<table id="logtable" border=1 align=center width=100%>';
                    echo '<tr><th align=center valign=center>ID</th><th align=center valign=center>Action</th><th align=center valign=center>Reason</th><th align=center valign=center>Duration</th><th align=center valign=center>Admin</th><th align=center valign=center>Date</th><th align=center valign=center>Appeal</th></tr>';
                    $count = 0;
                    foreach ($histories as $his) {
                        if ($his['action'] != "other") {
                            $count += 1;
                            echo '<tr><td align=center valign=center>';
                            echo $his['recordid'];
                            echo '</td><td align=center valign=center>';
                            echo $his['action'];
                            echo '</td><td align=center valign=center>';
                            echo $his['reason'];
                            echo '</td><td align=center valign=center>';
                            echo $his['duration'];
                            echo '</td><td align=center valign=center>';
                            echo $his['adminname'];
                            echo '</td><td align=center valign=center>';
                            echo $his['date'];
                            echo '</td><td align=center valign=center>';
                            if ($his['action'] == "other")
                                echo "Irrelevant";
                            else
                                echo '<input type="checkbox" name=appeals[] value="' . $his['recordid'] . ',' . $his['adminid'] . '" >';
                            echo '</td></tr>';
                        }
                    }
                    if ($count == 0) {
                        echo "<tr><td colspan=7><i>Your history is clean. Nothing to appeal.</i></td></tr>";
                    }
                    echo '</table><br>'
                    . '<input id="history_next_btn" type="button" value="Next" onclick="history_next(' . $_POST['type'] . '); return false;" >'
                    . '</div>';

                    echo '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="history_below">';

                    echo ' <table border=0><tr>
                            <td colspan="2"><b>How can we help? (in brief):</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="subject" type="text" maxlength="70" required style="width:500px" >
                            </td>
                            <td valign="top"><i>Please describe what you need in brief (one sentence).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Explain to us what exactly happened and why your history should be removed:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="content" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top"><i>Include chatlogs, screenshots, videos if any.</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>

                        </tr>
                        <tr>
                            <td>
                                <script src="//www.google.com/recaptcha/api.js"></script>
                                <div class="g-recaptcha" data-sitekey="6LedoggUAAAAAE5ymwtwsFCE9kWmltfX-ylCI4eS"></div>
                            </td>
                        </tr>
                        </table>';
                    echo '</div> '
                    . '</td></tr>';
                    echo '<script>$("#history_below").hide();</script>';
                } else if ($_POST['type'] == 6) { //bug report
                    echo '<tr><td colspan="2">';
                    echo '<p><i>Report bugs you find within MTA, UCP or Forums here. Your feedback goes a long way towards making OwlGaming even better!</i></p>';
                    echo '<p><b>Where do you find the bug: </b> <select id="bug_where" onchange="load_bug_report_area();" >'
                    . '<option value="" selected></option>'
                    . '<option value="MTA Server">MTA Server</option>'
                    . '<option value="UCP">UCP</option>'
                    . '<option value="Forums">Forums</option>'
                    . '</select>'
                    . '</p>'
                    . '<div id="bug_report_area"></div> ';
                } else if ($_POST['type'] == 7) { //player report 
                    echo '<tr><td colspan="2">';
                    echo '<p><i>"Our aim is to provide an environment where rule breakers are disciplined accordingly for their actions." - OwlGaming Administration Team.</i></p>'
                    . '<p>As a player, you are entitled to a server with zero tolerance for rule breakers. As a result, this forum exists in order for players to report other players for breaking our server rules. Furthermore, our Administration Team review all reports professionally, quickly and have a non-biased approach.</p>'
                    . '<p>In order to maintain this environment, we require your assistance as a player. As it\'s apparent to all players, administrators are not omnipresent and do depend on your help.</p>'
                    . '<p>Before submitting your report, you hereby agree that:</p>'
                    . '<ul><li style="">Vexatiously reporting a player without sufficient evidence will result in the report being locked, disregarded and the original reporter being disciplined.</li><li style="">You will remain calm/tell the truth throughout the entirety of the report.</li><li style="">You are reporting a player because s/he has broken a server rule that cannot be dealt with in-game.</li><li style="">You have provided screenshots or chatlogs.</li><li style="">You contacted the player and informed them they\'re being reported.</li></ul>';
                    echo '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="add_reported_players">'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>List the players you\'re reporting (by character name): </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="reportedcharacters" type="text" maxlength="300" required style="width:500px" placeholder="Multiple character names separated by a comma.">'
                    . '</td>'
                    . '<td valign="top" align=left><i>For example: Christopher Clark, Minh Nguyen, Matthew Perry</i></td>'
                    . '<tr><td colspan=2>'
                    . '<input id="btn_add_reported_players" type="button" value="Add" onclick="load_player_report_forms(' . $_POST['type'] . ', \'btn_add_reported_players\'); return false;" >'
                    . '</table>'
                    . '</div>'
                    . '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="report_players_invovled">'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>List the players those were involved (by character name, optional): </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="involvedcharacters" type="text" maxlength="300" style="width:500px" placeholder="Multiple character names separated by a comma.">'
                    . '</td>'
                    . '<td valign="top" align=left></td>'
                    . '</tr>'
                    . '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<input id="btn_add_involved_players" type="button" value="Add" onclick="load_player_report_forms(' . $_POST['type'] . ', \'btn_add_involved_players\'); return false;" >'
                    . '</td></tr></table></div>'
                    . '
                        <tr>
                            <td colspan="2"><b>Date of incident:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="date" type="date" required autocomplete>
                            </td>
                            <td valign="top" align="left" width=100%><i>An estimated moment when players broke the rules.</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>What rules did these players break:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="rules_broken" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top" align="left" width=100%><i>List and quote rules from <a href="http://forums.owlgaming.net/index.php?showforum=194" target="new">Server Rules</a></i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Explain your side of the story and list your evidence:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="story" style="width:500px; height:300px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top" align="left" width=100%><i>Elaborate on the incident and what your side of the situation is.<br>Provide at a minimum: screenshots or chatlogs or videos. (Screenshots or videos may result in your report being processed faster).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>
                        </tr>
                        <tr>
                            <td>
                                <script src="//www.google.com/recaptcha/api.js"></script>
                                <div class="g-recaptcha" data-sitekey="6LedoggUAAAAAE5ymwtwsFCE9kWmltfX-ylCI4eS"></div>
                            </td>
                        </tr>
                        </table>';
                } else if ($_POST['type'] == 9) { //staff report 
                    echo '<tr><td colspan="2">';
                    echo '<p>If you feel you do not agree with the decision, action or behavior of a staff member, you are first of all required to inform them. Sometimes the matter can be resolved by doing this simple step. However, if that fails and you\'re still left unhappy, you may proceed with submitting a report.</p>'
                    . '<p>The following are acceptable reasons to report a staff member:</p>'
                    . '<ul>'
                    . '<li style="">You feel insulted by a word, phrase, image or video they used.</li>'
                    . '<li style="">You disagree with a staff decision they\'ve made (i.e. server kick, jail, ban - website warning, infraction or ban).</li>'
                    . '<li style="">You feel the way in a which the staff behaved was not professional, acceptable or appropriate.</li>'
                    . '<li style="">You have been or are being harassed by a staff.</li>'
                    . '</ul>'
                    . '<p>The following are invalid reasons to report a staff member:</p>'
                    . '<ul>'
                    . '<li style="">You dislike them for personal reasons.</li>'
                    . '<li style="">You feel they do not deserve their current position.</li>'
                    . '<li style="">You want to get them in trouble/removed from their position.</li>'
                    . '</ul>'
                    . '<p>Upon submitting a staff report, you hereby agree to the following:</p>'
                    . '<ul>'
                    . '<li style="">Vexatiously reporting a staff member without sufficient evidence will result in the report being disregarded and the original reporter being disciplined.</li>'
                    . '<li style="">You will remain calm/tell the truth throughout the entirety of the report.</li>'
                    . '<li style="">You are reporting a staff member because s/he has broken a server/forum rule that cannot be dealt with immediately.</li>'
                    . '<li style="">You are reporting a staff member for a valid reason as seen above and not reporting a staff member for an invalid reason, as also listed above.</li>'
                    . '<li style="">You have provided screenshots or chatlogs as required.</li>'
                    . '<li style="">You contacted the staff member in order to attempt to resolve the situation, else if that failed you informed them they\'re being reported.</li>'
                    . '<li style="">You may or may not be informed about the status of the report, or outcome.</li>'
                    . '</ul>';
                    echo '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="add_reported_players">'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>Username of the staff: </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="reportedstaff" type="text" maxlength="300" required style="width:500px" placeholder="">'
                    . '</td>'
                    . '<td valign="top" align=left><i>If you don\'t remember by their username, input character name.</i></td>'
                    . '<tr><td colspan=2>'
                    . '</table>'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>What did the staff was doing wrong: </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="subject" type="text" maxlength="300" required style="width:500px" placeholder="">'
                    . '</td>'
                    . '<td valign="top" align=left><i>Describe briefly in one sentence.</i></td>'
                    . '<tr><td colspan=2>'
                    . '</table>'
                    . '</div>'
                    . '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '<div id="report_players_invovled">'
                    . '<table border=0><tr><td colspan=2>'
                    . '<b>List the players those were involved or witnessed (optional): </b>'
                    . '</td></tr>'
                    . '<tr>'
                    . '<td>'
                    . '<input id="involvedcharacters" type="text" maxlength="300" style="width:500px" placeholder="">'
                    . '</td>'
                    . '<td valign="top" align=left></td>'
                    . '</tr>'
                    . '</td></tr>'
                    . '<tr><td colspan=2>'
                    . '</td></tr></table></div>'
                    . '
                        <tr>
                            <td colspan="2"><b>Date of incident:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="date" type="date" required autocomplete>
                            </td>
                            <td valign="top" align="left" width=100%><i>An estimated moment when the incident was happening.</i></td>
                        </tr>
                 
                        <tr>
                            <td colspan="2"><b>Elaborate on the situation along with possible evidence:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="story" style="width:500px; height:300px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top" align="left" width=100%><i>Elaborate on the incident and what your side of the situation is.<br>Provide at a minimum: screenshots or chatlogs or videos. (Screenshots or videos may result in your report being processed faster).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>
                        </tr>
                        <tr>
                            <td>
                                <script src="//www.google.com/recaptcha/api.js"></script>
                                <div class="g-recaptcha" data-sitekey="6LedoggUAAAAAE5ymwtwsFCE9kWmltfX-ylCI4eS"></div>
                            </td>
                        </tr>
                        </table>';
                } else {
                    echo ' <tr>
                            <td colspan="2"><b>How can we help? (in brief):</b></td>
                        </tr>
                        <tr>
                            <td>
                                <input id="subject" type="text" maxlength="70" required style="width:500px" >
                            </td>
                            <td valign="top"><i>Please describe what you need in brief (one sentence).</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><b>Please add any details that might help us help you:</b></td>
                        </tr>
                        <tr>
                            <td>
                                <textarea id="content" style="width:500px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required ></textarea>
                            </td>
                            <td valign="top"><i>For example, what are you trying to do and what\'s happening? How could we possibly help you? What do you expect us to do?</i></td>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="btn_submit_ticket" type="submit" value="Create"></td>
                        </tr>
                        <tr>
                            <td>
                            <script src="//www.google.com/recaptcha/api.js"></script>
                            <div class="g-recaptcha" data-sitekey="6LedoggUAAAAAE5ymwtwsFCE9kWmltfX-ylCI4eS"></div>
                            </td>
                        </tr>';
                }
            }
        } else if ($step == "submit") {
            if(!isset($_POST['captcha']) || empty($_POST['captcha']))
            {
                echo '<h2>Please complete the captcha.</h2>';
                die();
            }
            else
            {
                $captcha = $_POST['captcha'];
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LedoggUAAAAAL-dHFRc1QUvzOo8I1gZgIYosMj1&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
                $response = json_decode($response, true);
                if(intval($response['success']) != 1)
                {
                    die("Invalid Captcha.");
                }
                else
                {
                    $insert = array();
                    $insert['private'] = $_POST['private'];
                    $insert['type'] = $_POST['type'];
                    if (isset($userID))
                        $insert['creator'] = $userID;
                    else
                        $insert['creator'] = $_POST['email'];
                    if (strlen($insert['creator']) > 0) {
                        $insert['subject'] = strip_tags($_POST['subject']);
                        $allowable_tags = "<br><i><u><b><ul><li><hr><table><tr><td><tbody><a></center></br></i></u></b></ul></li></hr></table></tr></td></tbody></a></center>";
                        $insert['content'] = nl2br(strip_tags($_POST['content'], $allowable_tags));
                        $tcid = $db->query_insert("tc_tickets", $insert);
                        if ($tcid) {
                            echo $tcid;
                        } else {
                            echo "error no tcid";
                        }
                        $laziestStaff = @getLaziestStaff($db, $_POST['type'], $_POST['assignto']);
                        if ($laziestStaff) {
                            $insert['id'] = $tcid;
                            $insert['assign_to'] = $laziestStaff['id'];
                            @assignTicket($db, $tcid, $laziestStaff['id'], $insert);
                        }
                    } else {
                        echo "error creator = 0";
                    }
                }
            }
        } else if ($step == "add_comment") {
                        if(!isset($_SESSION['userid']))
            {
                die("Please login to reply to a ticket.");
            }
            if(!isset($_POST['captcha']) || empty($_POST['captcha']))
            {
                echo '<h2>Please complete the captcha.</h2>';
                die();
            }
            else
            {
                $captcha = $_POST['captcha'];
                $response = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=6LedoggUAAAAAL-dHFRc1QUvzOo8I1gZgIYosMj1&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']);
                $response = json_decode($response, true);
                if(intval($response['success']) != 1)
                {
                    die("Invalid Captcha.");
                }
                else
                {
                    $isAdmin = $db->query_first("SELECT admin FROM accounts WHERE id = " . $_SESSION['userid']);
                    $ticketOwner = $db->query_first("SELECT creator FROM tc_tickets WHERE id = '" . $db->escape($_POST['tcid']) . "'");
                    $subs = $db->query_first("SELECT subcribers FROM tc_tickets WHERE id = '" . $db->escape($_POST['tcid']) . "'");
                    $subs = explode(",", $subs['subcribers']);
                    if($ticketOwner['creator'] != $_SESSION['userid'] && $isAdmin['admin'] == 0 && !in_array($_SESSION['userid'], $subs))
                    {
                        die("You're trying to add a comment to a ticket that doesn't belong to you.");
                    }
                    else
                    {
                        $commenter = $_POST['email'];
                        if (!isset($commenter))
                            $commenter = $userID;
                        if (@addTicketComment($db, $db->escape($_POST['tcid']), $commenter, nl2br(strip_tags($_POST['comment'])), $_POST['internal'])) {
                            echo htmlspecialchars($_POST['tcid'], ENT_QUOTES, 'UTF-8');
                        }
                    }
                }
            }
        } else if ($step == "ajax_remove_admin_history") {
            $record = $_POST['record'];
            $data = explode("_", $_POST['record']);
            //echo '$data0 = '.$data[0];
            //echo '$data1 = '.$data[1];
            require_once '../functions/functions.php';
            if (isPlayerTrialAdmin($_SESSION['groups']) or isPlayerSupporter($_SESSION['groups'])) {
                if ((isset($data[1]) and $data[1] > 0 and $data[1] == $userID) or isPlayerLeadAdmin($_SESSION['groups'])) {
                    if ($db->query("DELETE FROM adminhistory WHERE id=" . $db->escape($data[0]))) {
                        if ($db->affected_rows > 0) {
                            echo ".";
                        } else {
                            echo "This admin history record is already removed by someone else.";
                        }
                    } else {
                        echo "Could not remove this admin history record.\n\nInternal Error!";
                    }
                } else {
                    echo "You don't have sufficient permission to remove this admin history record.";
                }
            } else {
                echo "You don't have sufficient permission to remove this admin history record.";
            }
        } else if ($step == "list_tickets") {
            $start = $_POST['start'];
            if (strlen($start) > 0 and ! is_numeric($start)) {
                echo "Interal Error!";
            } else {
                if (!canUserAccessTcBackEnd($_SESSION['groups']) or ! isset($_SESSION['tc_backend']) or ! $_SESSION['tc_backend'] == 1) {
                    echo "You don't have sufficient permission to access this area.";
                } else {
                    $condition = "";
                    $condition = " WHERE 1=2 ";
                    $type = $_POST['type'];
                    $keyword = $_POST['keyword'];
                    if ($type == "id") {
                        if ($keyword and is_numeric($keyword) and $keyword > 0) {
                            $condition = " WHERE t.id=$keyword ";
                        }
                    } else if ($type == "type") {
                        $condition = " WHERE type=$keyword ";
                    } else if ($type == "status") {
                        $condition = " WHERE status=$keyword ";
                    } else if ($type == "assign_to") {
                        $condition = " WHERE assign_to=$keyword ";
                    } else if ($type == "creator") {
                        $condition = " WHERE creator=(SELECT id FROM accounts WHERE username LIKE '%" . $db->escape($keyword) . "%' LIMIT 1) ";
                    } else if ($type == "subcriber") {
                        $id = $db->query_first("SELECT id FROM accounts WHERE username LIKE '%" . $db->escape($keyword) . "%' LIMIT 1");
                        if ($id and $id['id'] and is_numeric($id['id'])) {
                            $condition = " WHERE subcribers LIKE '%," . $id['id'] . ",%' ";
                        }
                    } else if ($type == "date") {
                        if ($keyword == "Today") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=0 ";
                        } else if ($keyword == "Yesterday") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=1 ";
                        } else if ($keyword == "3 days ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=3 ";
                        } else if ($keyword == "1 week ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=7 ";
                        } else if ($keyword == "1 month ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=30 ";
                        } else if ($keyword == "3 month ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=90 ";
                        } else if ($keyword == "1 year ago") {
                            $condition = " WHERE DATEDIFF(NOW(), date)<=365 ";
                        }
                    } else if ($type == "subject") {
                        $condition = " WHERE subject LIKE '%" . $db->escape($keyword) . "%' ";
                    } else if ($type == "content") {
                        $condition = " WHERE content LIKE '%" . $db->escape($keyword) . "%' ";
                    } else if ($type == "custom") {
                        if ($keyword == "all") {
                            $condition = ' WHERE 1=1 ';
                        } else if ($keyword == 'active') {
                            $condition = ' WHERE 1=1 ';
                        } else if ($keyword == 'unassigned') {
                            $condition = ' WHERE assign_to=0 ';
                        } else if ($keyword == 'assigned') {
                            $condition = ' WHERE assign_to>0 ';
                        } else if ($keyword == 'closed') {
                            $condition = ' WHERE status=4 ';
                        } else if ($keyword == 'locked') {
                            $condition = ' WHERE status=-1 ';
                        } else {
                            $condition = ' WHERE 1=2 ';
                        }
                    }
                    $close = $_POST['close'];
                    $lock = $_POST['lock'];
                    if ($type != "status") {
                        if (!isset($close) or ! $close or $close == 0) {
                            $condition .= " AND status!=4 ";
                        }
                        if (!isset($lock) or ! $lock or $lock == 0) {
                            $condition .= " AND status!=-1 ";
                        }
                    }

                    if (!isPlayerLeadAdmin($_SESSION["groups"])) {
                        $condition = $condition . 'AND type!=9 ';
                    }

                    $sql = "SELECT a.username AS creatorname, b.username AS assigneename, status, t.id, type, subject, DATE_FORMAT(date,'%b %d, %Y at %h:%i %p') AS fdate, DATEDIFF(NOW(), date) AS dateago  FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id LEFT JOIN accounts b ON t.assign_to=b.id  " . $condition . "  ORDER BY t.last_updated DESC, t.id DESC";
                    //echo $sql;
                    //echo "<br>type: $type";
                    $eu = ($start - 0);
                    $limit = $_POST['limit'];                                 // No of records to be shown per page.
                    $this1 = $eu + $limit;
                    $back = $eu - $limit;
                    $next = $eu + $limit;
                    /////////////// Total number of records in our table. We will use this to break the pages///////
                    $nume = $db->query_first("SELECT COUNT(id) AS count FROM tc_tickets t " . $condition)['count'];
                    if ($nume > 0) {
                        /////// The variable nume above will store the total number of records in the table////
                        echo '<table id="logtable" border="1" align="center" width="100%">';
                        echo "<tr>"
                        . "<th align=center valign=center>Status</th>"
                        . "<th align=center valign=center>ID</th>"
                        . "<th align=center valign=center>Type</th>"
                        . "<th align=center valign=center>Subject</th>"
                        . "<th align=center valign=center>Creator</th>"
                        . "<th align=center valign=center>Assigned to</th>"
                        . "<th align=center valign=center>Date</th>"
                        . "</tr>";
                        $query = $db->query($sql . " LIMIT $eu, $limit ");
                        $i = 0;



                        while ($myticket = $db->fetch_array($query)) {
                            $i = $i + 1;   //  increment for alternate color of rows
                            echo "<tr ><td align=center valign=center>" . getTicketStatus($myticket['status'], $myticket['id']) . "</td><td align=center valign=center><a href='#' onclick='load_ticket(" . $myticket['id'] . "); return false;'>#" . $myticket['id'] . "</a></td><td align=center valign=center>" . getTicketType($myticket['type']) . "</td><td align=center valign=center>" . trimSubject($myticket['subject']) . "</td><td align=center valign=center>" . $myticket['creatorname'] . "</td><td align=center valign=center>" . $myticket['assigneename'] . "</td><td align=center valign=center>" . $myticket['fdate'] . " (" . formatDays($myticket['dateago']) . ")</td></tr>";
                        }
                        $db->free_result();
                        $loadto = "lib_mid_top";
                        if (isset($_POST['loadto'])) {
                            $loadto = $_POST['loadto'];
                        }
                        echo "<tr><td align=center colspan=7><i>$nume ticket(s) found.</i></td></tr>";
                        if ($nume > $limit) {
                            echo "<tr><td colspan=7>";
                            echo "<table align = 'center' width='100%'><tr><td  align='left' width='5%'>";
                            if ($back >= 0) {
                                ?>
                                <a href='#' onclick="ticket_search_load_results('<?php echo $back ?>', '<?php echo $limit ?>', '<?php echo $type ?>', '<?php echo $keyword ?>', '<?php echo $close ?>', '<?php echo $lock ?>', '<?php echo $loadto ?>');
                                        return false;"><b>PREV</b></a>
                                   <?php
                               }
                               echo "</td><td align=center width='90%'>";
                               $i = 0;
                               $l = 1;
                               for ($i = 0; $i < $nume; $i = $i + $limit) {
                                   if ($i <> $eu) {
                                       ?>
                                    <a href='#' onclick="ticket_search_load_results('<?php echo $i ?>', '<?php echo $limit ?>', '<?php echo $type ?>', '<?php echo $keyword ?>', '<?php echo $close ?>', '<?php echo $lock ?>', '<?php echo $loadto ?>');
                                            return false;"><b><?php echo $l; ?></b></a>
                                       <?php
                                   } else {
                                       echo "<b>$l</b>";
                                   }        /// Current page is not displayed as link and given font color red
                                   $l = $l + 1;
                               }
                               echo "</td><td  align='right' width='5%'>";
                               if ($this1 < $nume) {
                                   ?>
                                <a href='#' onclick="ticket_search_load_results('<?php echo $next ?>', '<?php echo $limit ?>', '<?php echo $type ?>', '<?php echo $keyword ?>', '<?php echo $close ?>', '<?php echo $lock ?>', '<?php echo $loadto ?>');
                                        return false;"><b>NEXT</b></a>
                                   <?php
                               }
                               echo "</td></tr></table></td></tr>";
                           }

                           echo "</table>";
                           echo "<br><center><input value='Hide' type='button' onclick=\"$('#$loadto').html(''); return false;\"></center><br><br>";
                       } else {
                           echo "<center><i>No ticket found.</i></center>";
                       }
                   }
               }
           } else if ($step == "load_ticket_comments") {
               //die("Testing..");
               $tcid = $_POST['tcid'];
               $start = $_POST['start'];
               $limit = $_POST['limit'];
               $loadto = (isset($_POST['loadto']) ? $_POST['loadto'] : null);
               $tc = $db->query_first("SELECT t.private, t.status, t.assign_to, t.type, t.subcribers, t.creator FROM tc_tickets t WHERE t.id=" . $db->escape($tcid));
               if ($tc) {
                   if (canUserViewTicket($userID, $tc)) {
                       require_once '../functions/base_functions.php';
                       require_once '../functions/functions.php';
                       $condition1 = ' tcid=' . $tcid . ' ';
                       if (canUserAccessTcBackEnd($_SESSION['groups']) and isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                           if ($tc['type'] == 9 and ! isPlayerLeadAdmin($_SESSION['groups'])) { // If this is a staff report, only UAT can see internal posts in case we want it private from subscribers and the poster.
                               $condition1 .= ' AND internal=0 ';
                           }
                           // Do nothing
                       } else {
                           $condition1 .= ' AND internal=0 ';
                       }
                       $eu = ($start - 0);                               // No of records to be shown per page.
                       $this1 = $eu + $limit;
                       $back = $eu - $limit;
                       $next = $eu + $limit;
                       /////////////// Total number of records in our table. We will use this to break the pages///////
                       echo '<title>Ticket #' . $tcid .' - OwlGaming Community</title>';
                       $nume = $db->query_first("SELECT COUNT(id) AS count FROM tc_comments WHERE " . $condition1)['count'];
                       if ($nume > 0) {
                           $sql = "SELECT c.internal, c.comment, c.id, c.poster, a.username AS postername, DATE_FORMAT(c.date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), c.date) AS dateago FROM tc_comments c LEFT JOIN accounts a ON c.poster=a.id WHERE " . $condition1 . " ORDER BY c.date DESC";
                           $comments = $db->fetch_all_array($sql . " LIMIT $eu, $limit ");
                           $i = 0;
                           for ($index = count($comments) - 1; $index >= 0; $index--) {
                               $i = $i + 1;
                               //echo $index;
                               $comment = $comments[$index];
                               $delete_comment_btn = '<a href="#" onClick="delete_my_comment(\'' . $comment['id'] . '\'); return false;">[Delete]</a>';
                               echo '<div style="border:1px solid #3D3D3D;margin-bottom: 5px">';
                               if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1) {
                                   if ($_SESSION['userid'] == $comment['poster']) {
                                       if ($comment['internal'] == 1) {
                                           echo '<div style="color:white;border-bottom:1px solid #A63D3D;text-align:right;background: #A63D3D;padding:2px;">'
                                           . 'You answered on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ') [INTERNAL] ' . $delete_comment_btn . '</div>';
                                       } else {
                                           echo '<div style="color:white;border-bottom:1px solid #3F3E3E;text-align:right;background: #3F3E3E;padding:2px;">'
                                           . 'You answered on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ') ' . $delete_comment_btn . '</div>';
                                       }
                                   } else {
                                       if ($comment['internal'] == 1) {
                                           echo '<div style="color:white;border-bottom:1px solid #A63D3D;text-align:right;background: #A63D3D;padding:2px;">';
                                       } else {
                                           echo '<div style="color:white;border-bottom:1px solid #838383;text-align:right;background: #838383;padding:2px;">';
                                       }
                                       if (!is_null($comment['postername']))
                                           echo $comment['postername'];
                                       elseif ($comment['poster'])
                                           echo $comment['poster'];
                                       else
                                           echo "Someone";
                                       echo ' responded on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ')' . ($comment['internal'] == 1 ? " [INTERNAL]" : "") . '</div>';
                                   }
                               } else {
                                   if ($_SESSION['userid'] == $comment['poster']) {
                                       echo '<div style="color:white;border-bottom:1px solid #3F3E3E;text-align:right;background: #3F3E3E;padding:2px;">'
                                       . 'You responded on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ') ' . $delete_comment_btn . '</div>';
                                   } else {
                                       echo '<div style="color:white;border-bottom:1px solid #838383;text-align:right;background: #838383;padding:2px;">';
                                       if (!is_null($comment['postername']))
                                           echo $comment['postername'];
                                       elseif ($comment['poster'])
                                           echo $comment['poster'];
                                       else
                                           echo "Someone";
                                       echo ' answered on ' . $comment['date'] . ' (' . formatDays($comment['dateago']) . ')</div>';
                                   }
                               }

                               echo '<div style="text-align:left;padding:2px;">' . make_clickable(showBBcodes($comment['comment'])) . '</div></div>';
                           }
                           //$db->free_result();
                           if ($nume > $limit) {
                               echo "<table align = 'center' border=0 width='100%'><tr><td  align='left' width='5%'>";
                               if ($back >= 0) {
                                   ?>
                                <a href='#' onclick="load_ticket_comments('<?php echo $tcid; ?>', '<?php echo $back; ?>', '<?php echo $limit; ?>', '<?php echo $loadto; ?>');
                                        return false;"><b>NEXT</b></a>
                                   <?php
                               }
                               echo "</td><td align=center width='90%'>";
                               $i = 0;
                               $l = 1;
                               for ($i = 0; $i < $nume; $i = $i + $limit) {
                                   if ($i <> $eu) {
                                       ?>
                                    <a href='#' onclick="load_ticket_comments('<?php echo $tcid; ?>', '<?php echo $i; ?>', '<?php echo $limit; ?>', '<?php echo $loadto; ?>');
                                            return false;"><b><?php echo $l; ?></b></a>
                                       <?php
                                   } else {
                                       echo "<b>$l</b>";
                                   }        /// Current page is not displayed as link and given font color red
                                   $l = $l + 1;
                               }
                               echo "</td><td  align='right' width='5%'>";
                               if ($this1 < $nume) {
                                   ?>
                                <a href='#' onclick="load_ticket_comments('<?php echo $tcid; ?>', '<?php echo $next; ?>', '<?php echo $limit; ?>', '<?php echo $loadto; ?>');
                                        return false;"><b>PREV</b></a>
                                   <?php
                               }
                               echo "</td></tr></table>";
                           }
                       }
                   }
               }
           } else if (isset($_POST['tcid']) && is_numeric($_POST['tcid'])) {
               $tc = $db->query_first("SELECT t.private, t.status, t.assign_to, t.type, t.subcribers, t.creator, t.subject, t.content, DATE_FORMAT(t.date,'%b %d, %Y at %h:%i %p') AS date, DATEDIFF(NOW(), t.date) AS dateago, DATE_FORMAT(t.last_updated,'%b %d, %Y at %h:%i %p') AS last_updated, DATEDIFF(NOW(), t.last_updated) AS last_updated_ago, t.id ,a.username AS creatorname, b.username AS assignee FROM tc_tickets t LEFT JOIN accounts a ON t.creator=a.id LEFT JOIN accounts b ON t.assign_to=b.id WHERE t.id=" . $db->escape($_POST['tcid']));
               if ($tc) {
                   if (canUserViewTicket($userID, $tc)) {
                       echo "<h2>You are viewing ticket #" . $tc['id'] . " - " . getTicketType($tc['type']) . "</h2>"
                       . "<script>window.history.pushState('', '', '/support.php?tcid=" . $tc['id'] . "');</script>";
                       ?>
                    <table border="0" width="100%">
                        <tr>
                            <td valign="top" width="50%">
                                <ul>
                                    <li><b>Creator: </b><?php
                                        if ($tc['creatorname'])
                                            echo $tc['creatorname'];
                                        else
                                            echo $tc['creator'];
                                        ?></li>
                                    <li><b>Creation Date: </b><?php echo $tc['date'] . " (" . formatDays($tc['dateago']) . ")"; ?></li>
                                    <li><b>Type: </b><?php echo getTicketType($tc['type']); ?></li>
                                    <li><b>Subject: </b><?php echo trimSubject($tc['subject'], 60); ?></li>
                                    <li><b>URL: </b><?php
                                        $currentUrl = $currentUrl . "/support.php?tcid=" . $tc['id'];
                                        echo "<a href='" . $currentUrl . "'>" . $currentUrl . "</a>";
                                        ?></li>
                                </ul>
                            </td>
                            <td valign="top">
                                <ul>
                                    <li><b>Status: </b><?php echo getTicketStatus($tc['status'], $tc['id'], true, $tc['type'], $tc['private']); ?>
                                    </li>
                                    <li><b>Assigned to: </b><?php
                                        if (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1 and $tc['status'] != -1 and $tc['status'] != 4) {
                                            require_once '../functions/functions.php';
                                            $staffs = getAllStaffs($db);
                                            echo '<select id="reassign_ticket" onchange="reassign_ticket(' . $tc['id'] . ');">';
                                            echo '<option value="0" selected>No-one (Unassign)</option>';
                                            foreach ($staffs as $staff) {
                                                $selected = '';
                                                if ($tc['assign_to'] == $staff['id'])
                                                    $selected = 'selected';
                                                echo '<option value="' . $staff['id'] . '" ' . $selected . '>' . getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username'] . '</option>';
                                            }
                                            echo '</select>';
                                        } else {
                                            if ($tc['assignee'])
                                                echo $tc['assignee'];
                                            else
                                                echo "No-one";
                                        }
                                        ?></li>

                                    <?php
                                    echo "<li><b>Subscribers:</b>";
                                    $isBcEnabled = (isset($_SESSION['tc_backend']) and $_SESSION['tc_backend'] == 1 and $tc['status'] != -1 and $tc['status'] != 4);
                                    if ($tc['subcribers'] and strlen($tc['subcribers']) > 0) {
                                        $subs = explode(",", $tc['subcribers']);

                                        $tail = '';
                                        foreach ($subs as $sub) {
                                            if ($sub and is_numeric($sub)) {
                                                $sub = $db->query_first("SELECT id, username, email FROM accounts WHERE id=" . $sub);
                                                $tail .= " " . $sub['username'];
                                                if ($isBcEnabled) {
                                                    $tail .= " (<a href='#' onclick='remove_subs(" . $tc['id'] . ", " . $sub['id'] . ", \"" . $sub['username'] . "\"); return false;'>X</a>)";
                                                }
                                                $tail .= ",";
                                            }
                                        }
                                        $tail = substr($tail, 0, -1);
                                        echo $tail;
                                    } else {
                                        echo " None";
                                    }
                                    if ($isBcEnabled)
                                        echo " | <a href='#' onclick='add_subcriber(" . $tc['id'] . "); return false;' >Add</a>";

                                    echo "</li>";
                                    ?>
                                    <li><b>Last Updated: </b><?php echo $tc['last_updated'] . " (" . formatDays($tc['last_updated_ago']) . ")"; ?></li>
                                    <ul>
                                        </td>
                                        </tr>
                                        </table>
                                        <div style="border:1px solid #3F3E3E;margin-bottom: 15px">
                                            <div style="color:white;border-bottom:1px solid #3F3E3E;text-align:right;background: #3F3E3E;padding:2px;">Issue started by <?php
                                                if ($tc['creatorname'])
                                                    echo $tc['creatorname'];
                                                else
                                                    echo $tc['creator'];
                                                require_once '../functions/base_functions.php';
                                                ?> on <?php echo $tc['date'] . " (" . formatDays($tc['dateago']) . ")"; ?></div>
                                            <div style="text-align:left;padding:2px;"><?php echo "<b><i>" . strip_tags($tc['subject']) . "</i></b><hr>" . make_clickable($tc['content']); ?></div>
                                        </div>
                                        <?php
                                        echo '<div id="comments"><center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center></div>'
                                        . '<script>load_ticket_comments(' . $tc['id'] . ',0,10);</script>';
                                        if ($tc['status'] >= 0) {
                                            if ($tc['type'] == 6 and ! canUserCommentOnBugReport($userID, $tc)) {
                                                echo "<i>You don't have sufficient permission to comment on this bug report.</i>";
                                            } else {
                                                $placeholder = "Comment on this ticket...";
                                                if ($isBcEnabled) { // tc backend
                                                    $placeholder = "Answer this ticket...";
                                                    if ($tc['status'] == 0) { // open
                                                        if ($tc['assign_to'] == 0) {
                                                            $placeholder = "Comment on this ticket may also auto-assign it to you...";
                                                        }
                                                    } else if ($tc['status'] == 4) { // closed
                                                        $placeholder = "Comment on this ticket may also automatically re-open the ticket...";
                                                    }
                                                } else {
                                                    if ($tc['status'] == 4) { // closed
                                                        $placeholder = "Comment on this ticket may also automatically re-open the ticket...";
                                                    }
                                                }
                                                ?>
                                                <form action="" onsubmit="client_add_comment('<?php echo $tc['id']; ?>');
                                                        return false;">
                                                    <textarea id="comment" style="width:954px; height:100px; font: inherit; resize: vertical;" maxlength="5000" required placeholder='<?php echo $placeholder; ?>' ></textarea>
                                                    <div style=" clear:both;">
                                                        <input type="submit" id="btn_add_comment" value="Add">
                                                        <script src="//www.google.com/recaptcha/api.js"></script>
                                                        <div class="g-recaptcha" data-sitekey="6LedoggUAAAAAE5ymwtwsFCE9kWmltfX-ylCI4eS"></div>
                                                        <?php
                                                        if ($isBcEnabled) { // if tc backend
                                                            echo "<input type='checkbox' id='internal' value='Internal Note'/> Make internal (Only staff members can read)";
                                                        } else {
                                                            if (!is_numeric($tc['creator']) or ( !isset($userID))) {
                                                                echo '<input id="email" type="email" maxlength="200" required style="width:500px" placeholder="Please enter your email address">';
                                                            }
                                                        }
                                                        ?>
                                                        </form>

                                                        <?php
                                                    }
                                                } else {
                                                    echo '<i>This ticket is locked and archived. Commenting is not possible.</i>';
                                                }
                                                ?>
                                            </div>
                                            <div style="display: inline-block;float: right; margin:3px;">
                                                <a href="/bbcodes" id="bbcodes">Supported BBCode</a>
                                                <div class="messagepop pop">
                                                    <table border="1" class="logtable">
                                                        <b>Usage samples:</b>
                                                        <ul>
                                                            <li>[img]http://images.com/test.png[/img]</li>
                                                            <li>[pastebin]dVsdCfgw[/pastebin]</li>
                                                            <li>[b]This is some bold text.[/b]</li>
                                                            <li>[i]This is some italic text.[/i]</li>
                                                            <li>[u]This text is underlined.[/u]</li>
                                                        </ul>
                                                    </table>
                                                    <a class="close" href="/">Close</a>
                                                </div>
                                            </div>

                                            <script>
                                                function deselect(e) {
                                                    $('.pop').slideFadeToggle(function () {
                                                        e.removeClass('selected');
                                                    });
                                                }

                                                $(function () {
                                                    $('#bbcodes').on('click', function () {
                                                        if ($(this).hasClass('selected')) {
                                                            deselect($(this));
                                                        } else {
                                                            $(this).addClass('selected');
                                                            $('.pop').slideFadeToggle();
                                                        }
                                                        return false;
                                                    });

                                                    $('.close').on('click', function () {
                                                        deselect($('#contact'));
                                                        return false;
                                                    });
                                                });

                                                $.fn.slideFadeToggle = function (easing, callback) {
                                                    return this.animate({opacity: 'toggle', height: 'toggle'}, 'fast', easing, callback);
                                                };
                                            </script>
                                            <?php
                                        } else {
                                            echo "<br><br><center><i>You don't have sufficient permission to view this ticket.</i></center>";
                                        }
                                    } else {
                                        echo "<br><br><center><i>Opps, sorry! The ticket you're looking for does not exist!</i></center>";
                                    }
                                } else {
                                    
                                }
                                $db->close();

                                function trimSubject($subj, $length = 32) {
                                    $subj = strip_tags($subj);
                                    if ($subj and strlen($subj) > 0) {
                                        if (strlen($subj) > $length) {
                                            return substr($subj, 0, $length) . "[..]";
                                        } else {
                                            return $subj;
                                        }
                                    } else {
                                        return "N/A";
                                    }
                                }
                                ?>