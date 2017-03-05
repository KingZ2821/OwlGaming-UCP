<?php
/*
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * File created by Maxime, 07-10-2015
 * ***********************************************************************************************************************
 */

//error_reporting(0);
$action = isset($_POST['action']) ? $_POST['action'] : null;
require_once '../classes/Performance.class.php';
$performance = new Performance();
if ($action == 'load_team_performance') {
    $interval = isset($_POST['interval']) ? $_POST['interval'] : null;
    if ($interval == 'gui_custom') {
        ?>
        <form action="" class="form-horizontal"  role="form">
            <fieldset>
                <div class="form-group">
                    <label for="dtp_input1" class="col-md-2 control-label">FROM</label>
                    <div class="input-group date form_datetime col-md-5" data-date-format="yyyy-mm-dd HH:ii:00" data-link-field="dtp_input1">
                        <input id="from" class="form-control" size="16" type="text" value="" style="width:300px;">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                    </div>
                    <input type="hidden" id="dtp_input1" value="" /><br/>
                </div>
                <div class="form-group">
                    <label for="dtp_input2" class="col-md-2 control-label">TO</label>
                    <div class="input-group date form_datetime col-md-5" data-date-format="yyyy-mm-dd HH:ii:00" data-link-field="dtp_input2">
                        <input id="to" class="form-control" size="16" type="text" value="" style="width:300px;">
                        <span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                    </div>
                    <input type="hidden" id="dtp_input2" value="" /><br/>
                </div>
                <input type="button" value="Draw Graphs" id="draw" onclick="load_team_performance('custom', 'charts-container')"/>
                <input type="button" value="Swap Dates" id="swap" onclick="var tmp = $('#from').val();
                        $('#from').val($('#to').val());
                        $('#to').val(tmp);
                       "/>
            </fieldset>
        </form>
        <script type="text/javascript">
            $(".form_datetime").datetimepicker({
                format: "yyyy-mm-dd HH:ii:00",
                autoclose: true,
                immediateUpdates: true,
                pick12HourFormat: false,
                use24hours: true,
                showMeridian: false,  
            });
        </script>
        <?php
    } else {
        $from = isset($_POST['from']) ? $_POST['from'] : false;
        $to = isset($_POST['to']) ? $_POST['to'] : false;
        $performance->dbConnect();
        $performance->drawCombinationChart($interval, $from, $to);
        $performance->drawPieChart($interval, $from, $to);
        $performance->dbClose();
    }
} else if ($action == 'load_individual_performance') {
    $target = isset($_POST['target']) ? $_POST['target'] : false;
    $targetname = isset($_POST['targetname']) ? $_POST['targetname'] : 'Unknown Staff';
    $interval = isset($_POST['interval']) ? $_POST['interval'] : false;
    $to = isset($_POST['to']) ? $_POST['to'] : false;
    $performance->dbConnect();
    $performance->drawCombinationChartIndividual($target, $targetname, $interval, $to);
    $performance->drawPieChartIndividual($target, $targetname, $interval, $to);
    $performance->dbClose();
} else if ($action == 'menu_switch') {
    $to = isset($_POST['to']) ? $_POST['to'] : null;
    $load_to = isset($_POST['load_to']) ? $_POST['load_to'] : null;
    if ($load_to and $to == 'team') {
        ?>
        <h2><a href="#" onclick="menu_switch('individual', 'perf_nav');
                return false;">Individual Performance</a> | Team Performance</h2>
        <p><a href="#" onclick="load_team_performance('today', 'charts-container');
                return false;"><b>Today</b></a><br><i>Draw team performance charts starting from 00:00 to 23:59 today (<?php echo date('l, jS \of F, Y'); ?>).</i></p>
        <p><a href="#" onclick="load_team_performance('this_week', 'charts-container');
                return false;"><b>This week</b></a><br><i>Draw team performance charts starting for 7 days this week (<?php
                  $day = date('w');
                  $week_start = date('l, jS \of F, Y', strtotime('-' . ($day - 1) . ' days'));
                  $week_end = date('l, jS \of F, Y', strtotime('+' . (7 - $day) . ' days'));
                  echo "From $week_start to $week_end";
                  ?>).</i></p>
        <p><a href="#" onclick="load_team_performance('this_month', 'charts-container');
                return false;"><b>This month</b></a><br><i>Draw team performance charts starting for this month (<?php echo date('F, Y'); ?>).</i></p>
        <p><a href="#" onclick="load_team_performance('this_year', 'charts-container');
                return false;"><b>This year</b></a><br><i>Draw team performance charts starting for this year (<?php echo date('Y'); ?>).</i></p>
        <p><a href="#" onclick="load_team_performance('anytime', 'charts-container');
                return false;"><b>Anytime</b></a><br><i>Draw team performance charts from collected data of all time (Since this system was made).</i></p>
        <p><a href="#" onclick="load_team_performance('gui_custom', 'datetime-container');
                return false;"><b>Custom</b></a><br><i>Define specific time interval.</i></p>
        <div id="datetime-container"></div>
        <?php
    } else if ($load_to and $to == 'individual') {
        ?>
        <h2>Individual Performance | <a href="#" onclick="menu_switch('team', 'perf_nav');
                return false;">Team Performance</a></h2>
            <?php
            require_once '../functions/functions_tickets.php';
            require_once '../functions/functions.php';
            $staffs = getAllStaffs($performance->getConn(), true);
            ?>
        <table border="0" cellpadding="10">
            <tr>
                <td>
                    <b>Select a target:</b>
                </td>
                <td>
                    <?php
                    echo '<select id="target" onchange="" style="width:100%">';
                    $my_id = $_SESSION['userid'];
                    $access = $performance->canAccess('individual', 'dontmatter', false, true);
                    foreach ($staffs as $staff) {
                        if ($access == 'all') {
                            echo '<option value="' . $staff['id'] . '" ' . ($my_id == $staff['id'] ? 'selected' : '') . '>' . getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username'] . '</option>';
                        } else if ($access == 'vct' and $staff['vct'] > 0) {
                            echo '<option value="' . $staff['id'] . '" ' . ($my_id == $staff['id'] ? 'selected' : '') . '>' . getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username'] . '</option>';
                        } else if ($access == 'supporter' and $staff['supporter'] > 0) {
                            echo '<option value="' . $staff['id'] . '" ' . ($my_id == $staff['id'] ? 'selected' : '') . '>' . getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username'] . '</option>';
                        } else if ($my_id == $staff['id']) {
                            echo '<option value="' . $staff['id'] . '" ' . ($my_id == $staff['id'] ? 'selected' : '') . '>' . getAllStaffTitlesFromIndexes($staff['admin'], $staff['supporter'], $staff['vct'], $staff['scripter'], $staff['mapper']) . ' ' . $staff['username'] . '</option>';
                        }
                    }
                    echo '</select>';
                    ?>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Select graph detail level :</b>
                </td>
                <td>
                    <select id="interval" onchange="" style="width:100%">
                        <option value="Hourly" selected >Hourly</option>
                        <option value="Daily"  >Daily</option>
                        <option value="Weekly" >Weekly</option>
                        <option value="Monthly"  >Monthly</option>
                        <option value="Yearly"  >Yearly</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td>
                    <b>Select a point of time:</b><br>
                    <i>(Leave this field empty to select current time)</i>
                </td>
                <td>
                    <div class="input-group date form_datetime col-md-5" data-date-format="yyyy-mm-dd HH:ii:00" data-link-field="dtp_input1" style="width:300px;">
                        <input id="to" class="form-control" size="16" type="text" value="" >
                        <span class="input-group-addon"><span class="glyphicon glyphicon-remove"></span></span>
                        <span class="input-group-addon"><span class="glyphicon glyphicon-th"></span></span>
                    </div>
                    <script type="text/javascript">
                        $(".form_datetime").datetimepicker({
                            format: "yyyy-mm-dd HH:ii:00",
                            autoclose: true,
                            immediateUpdates: true,
                            pick12HourFormat: false,
                            use24hours: true,
                            showMeridian: false,  
                        });
                    </script>
                </td>
            </tr>
        </table> 
        <br>
        <input type="button" value="Draw Graphs" id="draw" onclick="load_individual_performance('charts-container')"/>


        <?php
    }
}