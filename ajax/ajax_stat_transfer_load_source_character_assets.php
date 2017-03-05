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
    $charid = isset($_POST['charid']) ? $_POST['charid'] : null;
    $target_id = isset($_POST['target_id']) ? $_POST['target_id'] : null;
    if ($charid and $target_id) {
        require_once("../classes/Database.class.php");
        require_once("../classes/Mta.class.php");
        $mtaServer = new mta(SDK_IP, SDK_PORT, SDK_USER, SDK_PASSWORD);
        $mtaServerStats = $mtaServer->getResource("usercontrolpanel")->call("getServerStats");
        if (!isset($mtaServerStats) or ( !$mtaServerStats) or ! isset($mtaServerStats[1]) or ( !$mtaServerStats[1])) {
            echo "<p>Gameserver is <font color=#FF0000>OFFLINE.</font></p>"
            . "<p>We're sorry, the game server is currently down for scheduled maintenance. Please check back soon. </p>";
        } else {
            $db = new Database("MTA");
            $db->connect();
            $charid = $db->escape($charid);
            $target_id = $db->escape($target_id);
            $characterQry = $db->query_first("SELECT * FROM `characters` WHERE `account`='" . $_SESSION['userid'] . "' AND `id`='" . $charid . "'");

            $vehicleArr = array();
            $vehicleQuery = $db->query("SELECT v.id, v.model, v.impounded FROM vehicles v LEFT JOIN leo_impound_lot l ON v.id=l.veh WHERE v.owner='" . $characterQry['id'] . "' AND v.deleted='0' ");
            while ($vehicleRow = $db->fetch_array($vehicleQuery)) {
                $vehicleName = $mtaServer->getResource("usercontrolpanel")->call("getVehicleName", $vehicleRow['id']);
                if ($vehicleName and $vehicleName[0]) {
                    $vehicleArr[$vehicleRow['id']] = array($vehicleName[0], $vehicleRow['impounded']);
                } else {
                    require_once("../functions/base_functions.php");
                    $vehicleArr[$vehicleRow['id']] = array($vehicleIDtoName[$vehicleRow['model']], $vehicleRow['impounded']);
                }
            }
            $interiorArr = array();
            $interiorQuery = $db->query("SELECT `id`, `name`, disabled FROM `interiors` WHERE `owner`='" . $characterQry['id'] . "' AND `deleted`='0' ");
            while ($interiorRow = $db->fetch_array($interiorQuery)) {
                $interiorArr[$interiorRow['id']] = array($interiorRow['name'], $interiorRow['disabled']);
            }
            
            $slots_from = $db->query_first("SELECT maxvehicles, maxinteriors, max_clothes FROM characters WHERE id=$charid AND account=".$_SESSION['userid']);
            $slots_to = $db->query_first("SELECT maxvehicles, maxinteriors, max_clothes FROM characters WHERE id=$target_id AND account=".$_SESSION['userid']);
            $db->close();
            ?>
            <form name="stat_transfer_assets_form" >
                <table id=logtable border=1 width=100%>
                    <tr>
                        <td>
                    <center><b>Money</b></center>
                    </td>
                    <td>
                    <center><b>Vehicles</b></center>
                    </td>
                    <td>
                    <center><b>Interiors</b></center>
                    </td>
                    <td>
                    <center><b>Slots</b></center>
                    </td>
                    <tr>
                        <td valign="top">
                            Bank money: <BR />
                            <input type="number" name="bankmoney" value="<?php echo $characterQry['bankmoney']; ?>" required/><BR /><BR />
                            Money on hand: <BR />
                            <input type="number" name="money" value="<?php echo $characterQry['money']; ?>" /><BR required/>
                        </td>
                        <td valign="top">

                            <?php
                            foreach ($vehicleArr as $vehicleID => $vehicle) {
                                if (true) {//($mtaServer->getResource("carshop-system")->call("isForSale", $vehicleModel))
                                    if ($vehicle[1] != 0 and $characterQry['cked'] == 0) {
                                        echo "											<input type=\"checkbox\" name=\"vehicle[]\" value=\"" . $vehicleID . "\" onclick=\"alert('This vehicle is currently impounded or seized thus far it can not be transferred at the moment.'); return false;\"> <b>" . $vehicle[0] . "</b> (VIN: " . $vehicleID . ")<BR/>";
                                    } else {
                                        echo "											<input type=\"checkbox\" name=\"vehicle[]\" value=\"" . $vehicleID . "\" CHECKED> <b>" . $vehicle[0] . "</b> (VIN: " . $vehicleID . ")<BR/>";
                                    }
                                } else
                                    echo "										<i> <b>" . $vehicleArr[$vehicleID] . "</b> (VIN: " . $vehicleID . ")</i><BR />";
                            }
                            ?>
                        </td>
                        <td valign="top">

                            <?php
                            foreach ($interiorArr as $interiorID => $interior) {
                                if ($interior[1] != 0 and $characterQry['cked'] == 0) {
                                    echo "											<input type=\"checkbox\" name=\"interior[]\" value=\"" . $interiorID . "\" onclick=\"alert('This interior is currently disabled thus far it can not be transferred at the moment.'); return false;\"> <b>" . $interior[1] . "</b> (ID " . $interiorID . ")<BR />";
                                } else {
                                    echo "											<input type=\"checkbox\" name=\"interior[]\" value=\"" . $interiorID . "\" CHECKED> <b>" . $interior[0] . "</b> (ID " . $interiorID . ")<BR />";
                                }
                            }
                            ?>										
                        </td>
                        <td valign="top">
                            <?php
                                echo "<input type='checkbox' value='1' name='maxvehicles' ".( ($slots_from['maxvehicles']!=$slots_to['maxvehicles']) ? "" : "onclick='return false;'" )."> <b>Swap Vehicle Slots: </b>(".$slots_from['maxvehicles']." <-> ".$slots_to['maxvehicles'].") <BR/>";
                                echo "<input type='checkbox' value='1' name='maxinteriors' ".( ($slots_from['maxinteriors']!=$slots_to['maxinteriors']) ? "" : "onclick='return false;'" )."> <b>Swap Interior Slots: </b>(".$slots_from['maxinteriors']." <-> ".$slots_to['maxinteriors'].") <BR/>";
                                echo "<input type='checkbox' value='1' name='max_clothes' ".( ($slots_from['max_clothes']!=$slots_to['max_clothes']) ? "" : "onclick='return false;'" )."> <b>Swap Skin Slots: </b>(".$slots_from['max_clothes']." <-> ".$slots_to['max_clothes'].") <BR/>";
                            ?>
                        </td>
                    </tr>

                </table>
            </form>
            <?php
        }
    } else {
        echo "Error fetching character info.";
    }
}

