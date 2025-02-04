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

$normalPrice = 15; // per week

if (!isset($_SESSION['userid'])) {
    echo "<center><h3>You must be logged in to access this content.<br> <a href='' onClick='location.reload();'>Reload</a></h3></center>";
} else {
    if (isset($_POST['intid'])) {
        require_once '../functions/base_functions.php';
        $userID = $_SESSION['userid']; //This make sure noone edit other char
        require_once("../classes/Database.class.php");
        $db = new Database("MTA");
        $db->connect();
        $intid = $db->escape($_POST['intid']);
        $charid = $db->escape($_POST['charid']);
        $sql = "SELECT *, interiors.id as 'id', 
            DATEDIFF(NOW(), `lastused`) AS `datediff`, 
            DATE_FORMAT(lastused,'%b %d, %Y %h:%i %p') AS `lastused`, 
            charactername AS ownername, 
            (CASE WHEN f.dateline IS NULL THEN -1 ELSE TIMESTAMPDIFF(HOUR, f.dateline, NOW()) END) AS uploaded,
            f.file_size, f.file_type, DATE_FORMAT(f.dateline ,'%b %d, %Y %h:%i %p') AS last_uploaded, 
            (CASE WHEN ((protected_until IS NULL) OR (protected_until > NOW() = 0)) THEN NULL ELSE protected_until END) AS `protected`
            FROM `interiors` LEFT JOIN characters ON owner=characters.id 
            LEFT JOIN files f ON interiors.id=f.connected_interior 
            WHERE interiors.id='" . $intid . "' AND interiors.owner='" . $charid . "'";
        $int = $db->query_first($sql);
        if (!$int or ! $int['id'] or !  is_numeric($int['id'])) {
            echo ('This interior is no longer belonged to you.');
        } else {
            if ($_POST['step'] == 1) {
                ?>
                <link href="../css/login-form.css" type="text/css" rel="stylesheet" />
                <h2>Interior Inactivity Protection for '<?php echo $int['name']; ?>'</h2>

                <table border="0" cellpadding="20">
                    <tr>
                        <td >
                            <table border="0" align=center class="nicetable" style="padding:10px;">
                                <tr><td colspan=3 align=center><img src="../images/interiordesign.png"/></td></tr>
                                <tr>
                                    <td><b>Interior Name</td><td>:</td>
                                    <td>  <?php echo $int['name']; ?></td>
                                </tr>
                                <tr>
                                    <td><b>Cost</td><td>:</td>
                                    <td>  $<?php echo number_format($int['cost']); ?></td>
                                </tr>

                                <tr>
                                    <td><b>Owner</td><td>:</td>
                                    <td>  <?php echo str_replace("_", " ", $int['ownername']); ?></td>
                                </tr>

                                <tr>
                                    <td><b>Supplies</td><td>:</td>
                                    <td>  <?php echo $int['supplies']; ?> Kg(s)</td>
                                </tr>

                                <tr>
                                    <td><b>Last used</td><td>:</td>
                                    <td>  <?php echo $int['lastused']; ?></td>
                                </tr>
                            </table>
                            <?php
                            if ($int['uploaded'] >= 0) {
                                ?>
                                <br>
                                <br>
                                <table border="0" align=center style="padding:10px;">
                                    <tr>
                                        <td align="center">
                                            <div class="button_dl">
                                                <a href="../uploader/ajax_downloader.php?intid=<?php echo $int['id']; ?>&charid=<?php echo $charid; ?>">Download Interior</a>
                                                <p class="top"><?php echo $int['last_uploaded']; ?></p>
                                                <p class="bottom"><?php echo formatBytes($int['file_size']) . " | " . $int['file_type']; ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                </table>
                            <?php } ?>
                        </td>
                        <td valign='top'>
                            <br>
                            <p>An interior goes inactive when no body has entered it for 14 days or when your character has not been logged in game for 30 days. </p>
                            <p>An inactive interior is a waste of resources and thus far the interior's ownership will be stripped from you to give other players opportunities to buy and use it more efficiently.</p>
                            <p><?php
                            if (!isset($_POST['protected_until']))
                                echo 'This interior is currently <b>unprotected</b>. To prevent this to happen, you may want to spend your GC(s) to protect it from the inactive interior scanner.';
                            else
                                echo 'This interior is currently protected until <b>' . $_POST['protected_until'] . '</b>. However, you can extend this protection anytime you like.';
                            ?></p>
                            <br><br><br>
                            <form onsubmit="ajax_int_protect('<?php echo $intid; ?>', '<?php echo $charid; ?>');
                                                    return false;">
                                <table border=0 align="center" class="login-form" cellpadding="2" align='center'>
                                    <tr>
                                        <td><br>
                                            <b><?php
                if (!isset($_POST['protected_until']))
                    echo 'Protect this interior for';
                else
                    echo 'Extend this protection for';
                            ?> <input type='number' id='protection_weeks' min='1' max='99999999' step='1' required value='1' onchange="live_update_int_protection('<?php echo $normalPrice; ?>');"> week(s)
                                                <div id='protection_cost'>Cost: <?php echo $normalPrice; ?> GC(s)</div></b>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>
                                            <input type='submit' id='btn_int_protect' value='<?php
                                if (!isset($_POST['protected_until']))
                                    echo 'Protect';
                                else
                                    echo 'Extend';
                            ?>'>
                                                   <?php if (isset($_POST['protected_until'])) { ?>
                                                <input type='button' id='btn_int_remove_protect' value='Remove Protection' onclick="remove_protection('<?php echo $intid; ?>', '<?php echo $charid; ?>');">
                                            <?php } ?>
                                        </td>
                                    </tr>
                                </table>
                            </form>

                        </td>
                    </tr>
                </table>
                <br>
                <center><a href="" onClick="$('#char_info_mid_top').slideUp(500);
                                        return false;">Close Interior Protection</a></center>
                <script>
                    function ajax_int_protect(intid1, charid1, btnText) {
                        if ($('#btn_int_protect').val() != "Working.." && $('#btn_int_remove_protect').val() != "Working..") {
                            $('#btn_int_protect').val('Working..');
                            $.post("../ajax/ajax_ucp_int_protection.php", {
                                step: 2,
                                intid: intid1,
                                charid: charid1,
                                weeks: $('#protection_weeks').val(),
                            }, function (data) {
                                if (data == "error") {
                                    alert("Opps, sorry. We couldn't process your request. Try again later.");
                                    $('#btn_int_protect').val('Protect');
                                } else if (data == "lack") {
                                    if (confirm("Opps, sorry. You lack of GC(s) to purchase this item. \n\nYou can always get more GC(s) by donating to servers, do you want to go to the donation page now?")) {
                                        self.location = "donate.php";
                                    } else {
                                        $('#btn_int_protect').val('Protect');
                                    }
                                } else if (data == "mtadown") {
                                    alert("MTA server is OFFLINE. Please try again later.");
                                    $('#btn_int_protect').val('Protect');
                                } else {
                                    alert(data);
                                    self.location = "ucp.php";
                                }
                            });
                        }
                    }

                    function live_update_int_protection(cost) {
                        var weeks = $('#protection_weeks').val();
                        $('#protection_cost').html("Cost: " + (weeks * cost) + " GC(s)");
                    }

                    function remove_protection(intid1, charid1) {
                        if ($('#btn_int_protect').val() != "Working.." && $('#btn_int_remove_protect').val() != "Working..") {
                            $('#btn_int_remove_protect').val('Working..');
                            $.post("../ajax/ajax_ucp_int_protection.php", {
                                step: 3,
                                intid: intid1,
                                charid: charid1,
                            }, function (data) {
                                if (data == "error") {
                                    alert("Opps, sorry. We couldn't process your request. Try again later.");
                                    $('#btn_int_remove_protect').val('Remove Protection');
                                } else if (data == "mtadown") {
                                    alert("MTA server is OFFLINE. Please try again later.");
                                    $('#btn_int_remove_protect').val('Remove Protection');
                                } else {
                                    alert(data);
                                    self.location = "ucp.php";
                                }
                            });
                        }
                    }
                </script>
                <?php
            } else {
                require_once("../classes/Mta.class.php");
                $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
                $mtaServerStats = $mtaServer->getResource("usercontrolpanel")->call("getServerStats");
                if (!isset($mtaServerStats) or ( !$mtaServerStats) or ! isset($mtaServerStats[1]) or ( !$mtaServerStats[1])) {
                    echo "mtadown";
                } else {
                    if ($_POST['step'] == 2) {
                        $weeks = $_POST['weeks'];
                        $weeks = round($weeks);
                        if ($weeks > 0) {
                            require_once '../functions/functions_account.php';
                            $takeGC = takeGC($db, $userID, $weeks * $normalPrice, "Interior inactivity protection (" . $int['name'] . " - " . $weeks . " week(s))");
                            if (!$takeGC[0]) {
                                if ($takeGC[1] == "You lack of GC(s) to purchase this item.") {
                                    echo 'lack';
                                } else {
                                    echo "error";
                                }
                            } else {
                                $update = '';
                                $extended = "extended this interior's inactivity protection by";
                                if (is_null($int['protected'])) {
                                    $update = "NOW() + INTERVAL " . $weeks . " WEEK";
                                    $extended = "protected this interior from the inactive scanner for";
                                } else {
                                    $update = "protected_until + INTERVAL " . $weeks . " WEEK";
                                }
                                if ($db->query("UPDATE interiors SET protected_until=" . $update . " WHERE id=" . $intid . " AND owner=" . $charid . "")) {
                                    echo "You have successfully " . $extended . " " . $weeks . " week(s)!";
                                    $mtaServer->getResource("interior_system")->call("realReloadInterior", $intid);
                                } else {
                                    echo 'error';
                                }
                            }
                        } else {
                            echo 'error';
                        }
                    } else if ($_POST['step'] == 3) {
                        if ($db->query("UPDATE interiors SET protected_until=NULL WHERE id=" . $intid . " AND owner=" . $charid . "")) {
                            echo "You have successfully removed the inactivity protection from this interior!";
                            $mtaServer->getResource("interior_system")->call("realReloadInterior", $intid);
                        } else {
                            echo 'error';
                        }
                    }
                }
            }
            $db->close();
        }
    }
}