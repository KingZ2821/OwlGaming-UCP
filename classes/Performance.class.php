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

$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
require_once "$root/classes/User.class.php";

class Performance extends User {

    private $is_staff = "(admin > 0 OR supporter > 0 OR vct > 0 OR scripter > 0 OR mapper > 0)";
    private $order = "reports DESC, admin DESC, supporter DESC, vct DESC, scripter DESC, mapper DESC";

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

    function canAccess($type = 'team', $interval = 'today', $print_output = true, $ignore_2factor = false) {
        if (!$this->is_logged() or $this->is_banned()) {
            if ($print_output) {
                echo "<center><h3>You don't have sufficient permission to access this area.</h3></center>";
            }
            return false;
        }
        $groups = $_SESSION['groups'];
        require_once "$this->root/functions/functions.php";

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

        if (isPlayerLeadAdmin($groups)) {
            return 'all';
        } else if (isPlayerVCTLeader($groups)) {
            return 'vct';
        } else if (isPlayerSupporterManager($groups)) {
            return 'supporter';
        } else if ($type == 'invidiual') {
            return $_SESSION['userid'] == $interval;
        }

        if ($print_output) {
            echo "<center><h3>You don't have sufficient permission to access this area.</h3></center>";
        }
        return false;
    }

    function drawCombinationChart($interval = 'today', $from = false, $to = false) {
        $access = $this->canAccess('team', $interval, true, true);
        if (!$access) {
            return false;
        } else if ($access == 'vct') {
            $this->is_staff = "(vct > 0)";
        } else if ($access == 'supporter') {
            $this->is_staff = "(supporter > 0)";
        }
        $sql = false;
        $cond_today_1 = "( YEAR(o.date) = YEAR(NOW()) AND MONTH(o.date) = MONTH(NOW()) AND DAY(o.date) = DAY(NOW()) )";
        $cond_today_2 = "(YEAR(r.date) = YEAR(NOW()) AND MONTH(r.date) = MONTH(NOW()) AND DAY(r.date) = DAY(NOW()))";
        if ($interval == 'today') {
            $sql = "SELECT      a.id, username, admin, supporter, vct, scripter, mapper, 
                                (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'online', 
                                (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'duty',
                                (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id AND $cond_today_2 ) AS 'reports' 
                    FROM accounts a
                    WHERE $this->is_staff 
                    GROUP BY a.id ORDER BY $this->order";
            $f_interval = "Today (" . date('l, jS \of F, Y') . ")";
        } else if ($interval == 'this_week') {
            $cond_today_1 = "(YEARWEEK(o.date, 5) = YEARWEEK(CURDATE(), 5))";
            $cond_today_2 = "(YEARWEEK(r.date, 5) = YEARWEEK(CURDATE(), 5))";
            $sql = "SELECT      a.id, username, admin, supporter, vct, scripter, mapper, 
                                (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'online', 
                                (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'duty',
                                (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id AND $cond_today_2 ) AS 'reports' 
                    FROM accounts a
                    WHERE $this->is_staff 
                    GROUP BY a.id ORDER BY $this->order";
            $day = date('w');
            $week_start = date('l, jS \of F, Y', strtotime('-' . ($day - 1) . ' days'));
            $week_end = date('l, jS \of F, Y', strtotime('+' . (7 - $day) . ' days'));
            $f_interval = "This week (From $week_start to $week_end)";
        } else if ($interval == 'this_month') {
            $cond_today_1 = "(EXTRACT(MONTH FROM o.date) = EXTRACT(MONTH FROM CURDATE()))";
            $cond_today_2 = "(EXTRACT(MONTH FROM r.date) = EXTRACT(MONTH FROM CURDATE()))";
            $sql = "SELECT      a.id, username, admin, supporter, vct, scripter, mapper, 
                                (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'online', 
                                (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'duty',
                                (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id AND $cond_today_2 ) AS 'reports' 
                    FROM accounts a
                    WHERE $this->is_staff 
                    GROUP BY a.id ORDER BY $this->order";
            $f_interval = "This month (" . date('F, Y') . ")";
        } else if ($interval == 'this_year') {
            $cond_today_1 = "(EXTRACT(YEAR FROM o.date) = EXTRACT(YEAR FROM CURDATE()))";
            $cond_today_2 = "(EXTRACT(YEAR FROM r.date) = EXTRACT(YEAR FROM CURDATE()))";
            $sql = "SELECT      a.id, username, admin, supporter, vct, scripter, mapper, 
                                (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'online', 
                                (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'duty',
                                (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id AND $cond_today_2 ) AS 'reports' 
                    FROM accounts a
                    WHERE $this->is_staff 
                    GROUP BY a.id ORDER BY $this->order";
            $f_interval = "This year (" . date('Y') . ")";
        } else if ($interval == 'custom' and $from and $to) {
            $f_from = date_create_from_format('yyyy-mm-dd HH:ii:ss', $from);
            $f_to = date_create_from_format('yyyy-mm-dd HH:ii:ss', $to);
            $f_interval = "From " . date_format(new DateTime($f_from), 'jS \of F, Y h:i:s A') . " to " . date_format(new DateTime($f_to), 'jS \of F, Y h:i:s A');
            $cond_today_1 = "( o.date BETWEEN '" . $this->db->escape($from) . "' AND '" . $this->db->escape($to) . "' )";
            $cond_today_2 = "( r.date BETWEEN '" . $this->db->escape($from) . "' AND '" . $this->db->escape($to) . "' )";
            $sql = "SELECT      a.id, username, admin, supporter, vct, scripter, mapper, 
                                (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'online', 
                                (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id AND $cond_today_1 ) AS 'duty',
                                (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id AND $cond_today_2 ) AS 'reports' 
                    FROM accounts a
                    WHERE $this->is_staff 
                    GROUP BY a.id ORDER BY $this->order";
        } else if ($interval == 'anytime') {
            $f_interval = "Of all time";
            $sql = "SELECT  username, COUNT(handler) AS reports, admin, supporter, vct, scripter, mapper, 
                            (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=r.handler ) AS 'online', 
                            (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=r.handler ) AS 'duty'
                FROM reports r LEFT JOIN accounts a ON r.handler=a.id 
                GROUP BY handler ORDER BY reports DESC, admin DESC, supporter DESC, vct DESC, scripter DESC, mapper DESC;";
            $sql = "SELECT      a.id, username, admin, supporter, vct, scripter, mapper, 
                                (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id ) AS 'online', 
                                (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id ) AS 'duty',
                                (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id ) AS 'reports' 
                    FROM accounts a
                    WHERE $this->is_staff 
                    GROUP BY a.id ORDER BY $this->order";
        }


        $qs = $this->db->query($sql);
        if ($this->db->affected_rows() > 0) {
            $username = array();
            $staff = array();
            $reports = array();
            $online = array();
            $duty = array();
            $max_o = 0;
            $max_r = 0;
            $productivity_duty = array();
            $productivity_online = array();
            require_once $this->root . '/functions/functions.php';
            while ($q = $this->db->fetch_array($qs)) {
                array_push($username, array('label' => $q['username']));
                array_push($staff, array('label' => $q['username'] . ' | ' . getAllStaffTitlesFromIndexes($q['admin'], $q['supporter'], $q['vct'], $q['scripter'], $q['mapper'])));
                array_push($reports, array('value' => $q['reports']));
                $o = is_null($q['online']) ? 0 : $q['online'] / 60;
                array_push($online, array('value' => $o));
                $d = is_null($q['duty']) ? 0 : $q['duty'] / 60;
                array_push($duty, array('value' => $d));
                array_push($productivity_duty, array('value' => $d >= 1 ? $q['reports'] / $d : $q['reports']));
                array_push($productivity_online, array('value' => $o >= 1 ? $q['reports'] / $o : $q['reports']));
                if ($o > $max_o) {
                    $max_o = $o;
                }
                if ($q['reports'] > $max_r) {
                    $max_r = $q['reports'];
                }
            }
            $this->db->free_result();
            echo '<div id="combinationChart" style="width: 90%; margin: 0px auto;"></div>
        <div style="width:90%; height: 400px; margin: 0px auto; position:relative; white-space: nowrap;">
            <div id="pieChart" style="width:30.5%; display: inline-block;"></div>
            <div id="productivityChartByDuty" style="width:34.5%; display: inline-block;"></div>
            <div id="productivityChartByOnline" style="width:34.5%; display: inline-block;"></div>
        </div>';
            ?>
            <script type="text/javascript">
                $('#datetime-container').slideUp(500);
                FusionCharts.ready(function () {
                    var revenueChart = new FusionCharts({
                        "type": "mscombidy2d",
                        "renderAt": "combinationChart",
                        "width": "100%",
                        /*"height": "<?php //echo 100 + ($this->db->affected_rows() * 30);                                     ?>",*/
                        "height": "800",
                        "dataFormat": "json",
                        "dataSource": {
                            "chart": {
                                "caption": "Combination Performance",
                                "subCaption": "<?php echo $f_interval; ?>",
                                "xAxisName": "Staff",
                                "pYAxisName": "Reports handled",
                                "xAxisMaxValue": "<?php echo $max_r; ?>",
                                "sYAxisName": "Online Session (hours)",
                                "sNumberSuffix": "",
                                "sYAxisMaxValue": "<?php echo $max_o; ?>",
                                "theme": "fint",
                                "usePlotGradientColor": "0",
                                "plotFillAlpha": "70",
                                "baseFont": "Segoe UI",
                                "baseFontSize": "13",
                                "primaryAxisOnLeft": "1",
                                /*"labelFont": "Segoe UI",
                                 "labelFontSize": "13",
                                 "captionFont": "Segoe UI",
                                 "captionFontSize": "20",
                                 "valueFont": "Segoe UI",*/
                                "labelDisplay": "rotate",
                                "slantLabels": "1",
                                "showBorder": "1",
                                "showShadow": "1",
                                "decimals": "2",
                                "legendShadow": "1",
                            },
                            "categories": [
                                {
                                    "category": [
            <?php echo json_encode($staff); ?>
                                    ]
                                }
                            ],
                            "dataset": [
                                {
                                    "seriesName": "Reports",
                                    "parentYAxis": "P",
                                    "data": [
            <?php echo json_encode($reports); ?>
                                    ]
                                },
                                {
                                    "seriesName": "Online",
                                    "parentYAxis": "S",
                                    "renderAs": "area",
                                    "showValues": "0",
                                    "data": [
            <?php echo json_encode($online); ?>
                                    ]
                                },
                                {
                                    "seriesName": "Duty",
                                    "parentYAxis": "S",
                                    "renderAs": "line",
                                    "showValues": "0",
                                    "data": [
            <?php echo json_encode($duty); ?>
                                    ]
                                }
                            ]

                        }
                    });
                    revenueChart.render();
                });
                FusionCharts.ready(function () {
                    var revenueChart = new FusionCharts({
                        type: 'scrollColumn2d',
                        renderAt: 'productivityChartByDuty',
                        width: '100%',
                        height: '100%',
                        dataFormat: 'json',
                        dataSource: {
                            "chart": {
                                "caption": "Productivity (Reports/Duty)",
                                "subCaption": "<?php echo $f_interval; ?>",
                                "plotFillAlpha": "70",
                                "baseFont": "Segoe UI",
                                "baseFontSize": "13",
                                "labelDisplay": "rotate",
                                "slantLabels": "1",
                                "showBorder": "1",
                                "showShadow": "1",
                                "xaxisname": "Staff",
                                "yaxisname": "Reports per hour",
                                "showvalues": "1",
                                "placeValuesInside": "1",
                                "rotateValues": "1",
                                "theme": "fint",
                                "showborder": "1",
                                "showcanvasborder": "0",
                                "numVisiblePlot": "12",
                                "scrollheight": "10",
                                "flatScrollBars": "1",
                                "scrollShowButtons": "0",
                                "showHoverEffect": "1",
                                "decimals": "2",
                                "scrollToEnd": "0",
                            },
                            "categories": [
                                {
                                    "category": [
            <?php echo json_encode($username); ?>
                                    ]
                                }
                            ],
                            "dataset": [
                                {
                                    "data": [
            <?php echo json_encode($productivity_duty); ?>
                                    ]
                                }
                            ],
                        }
                    });

                    revenueChart.render();
                });
                FusionCharts.ready(function () {
                    var revenueChart = new FusionCharts({
                        type: 'scrollColumn2d',
                        renderAt: 'productivityChartByOnline',
                        width: '100%',
                        height: '100%',
                        dataFormat: 'json',
                        dataSource: {
                            "chart": {
                                "caption": "Productivity (Reports/Online)",
                                "subCaption": "<?php echo $f_interval; ?>",
                                "plotFillAlpha": "70",
                                "baseFont": "Segoe UI",
                                "baseFontSize": "13",
                                "labelDisplay": "rotate",
                                "slantLabels": "1",
                                "showBorder": "1",
                                "showShadow": "1",
                                "xaxisname": "Staff",
                                "yaxisname": "Reports per hour",
                                "showvalues": "1",
                                "placeValuesInside": "1",
                                "rotateValues": "1",
                                "theme": "fint",
                                "showborder": "1",
                                "showcanvasborder": "0",
                                "numVisiblePlot": "12",
                                "scrollheight": "10",
                                "flatScrollBars": "1",
                                "scrollShowButtons": "0",
                                "showHoverEffect": "1",
                                "decimals": "2",
                                "scrollToEnd": "0",
                            },
                            "categories": [
                                {
                                    "category": [
            <?php echo json_encode($username); ?>
                                    ]
                                }
                            ],
                            "dataset": [
                                {
                                    "data": [
            <?php echo json_encode($productivity_online); ?>
                                    ]
                                }
                            ],
                        }
                    });

                    revenueChart.render();
                });
            </script>
            <?php
        } else {
            echo "<center>*There isn't enough information to draw this graph yet. Check back later.*</center>";
        }
    }

    function drawPieChart($interval = 'today', $from = false, $to = false) {
        $access = $this->canAccess('team', $interval, true, true);
        if (!$access) {
            return false;
        } else if ($access == 'vct') {
            $this->is_staff = "(vct > 0)";
        }

        $sql = false;
        if ($interval == 'today') {
            $sql = "SELECT type, COUNT(type) AS count FROM reports r LEFT JOIN accounts a ON r.handler=a.id WHERE $this->is_staff AND YEAR(r.date) = YEAR(NOW()) AND MONTH(r.date) = MONTH(NOW()) AND DAY(r.date) = DAY(NOW()) GROUP BY type ORDER BY count DESC";
            $f_interval = "Today (" . date('l, jS \of F, Y') . ")";
        } else if ($interval == 'this_week') {
            $sql = "SELECT type, COUNT(type) AS count FROM reports r LEFT JOIN accounts a ON r.handler=a.id WHERE $this->is_staff AND YEARWEEK(r.date, 5) = YEARWEEK(CURDATE(), 5) GROUP BY type ORDER BY count DESC";
            $day = date('w');
            $week_start = date('l, jS \of F, Y', strtotime('-' . ($day - 1) . ' days'));
            $week_end = date('l, jS \of F, Y', strtotime('+' . (7 - $day) . ' days'));
            $f_interval = "This week (From $week_start to $week_end)";
        } else if ($interval == 'this_month') {
            $sql = "SELECT type, COUNT(type) AS count FROM reports r LEFT JOIN accounts a ON r.handler=a.id WHERE $this->is_staff AND EXTRACT(MONTH FROM r.date) = EXTRACT(MONTH FROM CURDATE()) GROUP BY type ORDER BY count DESC";
            $f_interval = "This month (" . date('F, Y') . ")";
        } else if ($interval == 'this_year') {
            $sql = "SELECT type, COUNT(type) AS count FROM reports r LEFT JOIN accounts a ON r.handler=a.id WHERE $this->is_staff AND EXTRACT(YEAR FROM r.date) = EXTRACT(YEAR FROM CURDATE()) GROUP BY type ORDER BY count DESC";
            $f_interval = "This year (" . date('Y') . ")";
        } else if ($interval == 'custom' and $from and $to) {
            $f_from = date_create_from_format('yyyy-mm-dd HH:ii:ss', $from);
            $f_to = date_create_from_format('yyyy-mm-dd HH:ii:ss', $to);
            $f_interval = "From " . date_format(new DateTime($f_from), 'jS \of F, Y h:i:s A') . " to " . date_format(new DateTime($f_to), 'jS \of F, Y h:i:s A');
            $sql = "SELECT type, COUNT(type) AS count FROM reports r LEFT JOIN accounts a ON r.handler=a.id WHERE $this->is_staff AND ( r.date BETWEEN '" . $this->db->escape($from) . "' AND '" . $this->db->escape($to) . "' ) GROUP BY type ORDER BY count DESC";
        } else if ($interval == 'anytime') {
            $f_interval = "Of all time";
            $sql = "SELECT type, COUNT(type) AS count FROM reports r LEFT JOIN accounts a ON r.handler=a.id WHERE $this->is_staff GROUP BY type ORDER BY count DESC";
        }
        if ($sql && $this->canAccess('team', $interval, false, true)) {
            $qs = $this->db->query($sql);
            if ($this->db->affected_rows() > 0) {
                $data = array();
                $total = 0;
                require_once $this->root . '/functions/functions.php';
                while ($q = $this->db->fetch_array($qs)) {
                    array_push($data, array('label' => getReportTypes($q['type'])['name'], 'value' => $q['count']));
                    $total += $q['count'];
                }
                $this->db->free_result();
                ?>
                <script type="text/javascript">
                    FusionCharts.ready(function () {
                        var revenueChart = new FusionCharts({
                            type: 'doughnut2d',
                            renderAt: 'pieChart',
                            width: '100%',
                            height: '100%',
                            dataFormat: 'json',
                            dataSource: {
                                "chart": {
                                    "caption": "Split of reports by type",
                                    "subCaption": "<?php echo $f_interval; ?>",
                                    "numberPrefix": "",
                                    "showPercentValues": "1",
                                    "showPercentInTooltip": "0",
                                    "decimals": "1",
                                    "plotFillAlpha": "50",
                                    "baseFont": "Segoe UI",
                                    "baseFontSize": "13",
                                    "showBorder": "1",
                                    "startingAngle": "45",
                                    "theme": "fint",
                                    "showLegend": "1",
                                    "enableMultiSlicing": "0",
                                    "defaultCenterLabel": "<?php echo "Total: " . $total; ?>",
                                    "showShadow": "1",
                                    "enableSmartLabels": "0",
                                    "labelDistance": "10",
                                },
                                "data": [<?php echo json_encode($data); ?>]
                            }
                        }).render();

                    });
                </script>
                <?php
            } else {
                echo "<center>*There isn't enough information to draw this graph yet. Check back later.*</center>";
            }
        } else {
            //echo "<center>Session timed out. Please relogin and try again.</center>";
        }
    }

    function drawCombinationChartIndividual($target, $target_name, $interval, $to) {
        if ($this->canAccess('invidiual', $target, true, true)) {
            $to = (strlen($to) < 1 or strtotime($to) > time()) ? time() : strtotime($to);
            //var_dump(date("jS \of F, Y H:i", time()));
            if ($interval == 'Hourly') {
                $plots = 24;
                $plot_time = 3600;
            } else if ($interval == 'Daily') {
                $plots = 30;
                $plot_time = 3600 * 24;
            } else if ($interval == 'Weekly') {
                $plots = 30;
                $plot_time = 3600 * 24 * 7;
            } else if ($interval == 'Monthly') {
                $plots = 12;
                $plot_time = 3600 * 24 * 30;
            } else if ($interval == 'Yearly') {
                $plots = 10;
                $plot_time = 3600 * 24 * 365;
            }

            $plot_names = array();
            $reports = array();
            $online = array();
            $duty = array();
            $max_o = 0;
            $max_r = 0;
            $productivity_duty = array();
            $productivity_online = array();
            require_once $this->root . '/functions/functions.php';
            $start = "N/A";
            for ($i = $plots - 1; $i >= 0; $i--) {
                $time = $to - ($plot_time * $i);
                if ($i == $plots - 1) {
                    $start = date("jS \of F, Y H:i", $time);
                } else if ($i == 0) {
                    $stop = date("jS \of F, Y H:i", $time);
                    $f_interval = "From $start to $stop";
                }
                array_push($plot_names, array('label' => date($interval == 'Yearly' ? "M Y" : "M j H:i", $time)));
                $cond = $this->db->escape($time - $plot_time) . "' AND '" . $this->db->escape($time);
                $data = $this->db->query_first("SELECT  a.id, username, admin, supporter, vct, scripter, mapper, 
                                                        (SELECT SUM(minutes_online) FROM online_sessions o WHERE o.staff=a.id AND ( UNIX_TIMESTAMP(o.date) BETWEEN '" . $cond . "' ) ) AS 'online', 
                                                        (SELECT SUM(minutes_duty) FROM online_sessions o WHERE o.staff=a.id AND ( UNIX_TIMESTAMP(o.date) BETWEEN '" . $cond . "' ) ) AS 'duty',
                                                        (SELECT COUNT(handler) FROM reports r WHERE r.handler= a.id AND ( UNIX_TIMESTAMP(r.date) BETWEEN '" . $cond . "' ) ) AS 'reports' 
                                FROM accounts a WHERE a.id=" . $this->db->escape($target) . " LIMIT 1");
                $r = is_null($data['reports']) ? 0 : $data['reports'];
                array_push($reports, array('value' => $r));
                $o = is_null($data['online']) ? 0 : $data['online'] / 60;
                array_push($online, array('value' => $o));
                $d = is_null($data['duty']) ? 0 : $data['duty'] / 60;
                array_push($duty, array('value' => $d));
                array_push($productivity_duty, array('value' => $d >= 1 ? $r / $d : $r));
                array_push($productivity_online, array('value' => $o >= 1 ? $r / $o : $r));
                if ($o > $max_o) {
                    $max_o = $o;
                }
                if ($r > $max_r) {
                    $max_r = $r;
                }
            }

            echo '<div id="combinationChart" style="width: 90%; margin: 0px auto;"></div>
        <div style="width:90%; height: 400px; margin: 0px auto; position:relative; white-space: nowrap;">
            <div id="pieChart" style="width:30.5%; display: inline-block;"></div>
            <div id="productivityChartByDuty" style="width:34.5%; display: inline-block;"></div>
            <div id="productivityChartByOnline" style="width:34.5%; display: inline-block;"></div>
        </div>';
            ?>
            <script type="text/javascript">
                FusionCharts.ready(function () {
                    var revenueChart = new FusionCharts({
                        "type": "mscombidy2d",
                        "renderAt": "combinationChart",
                        "width": "100%",
                        /*"height": "<?php //echo 100 + ($this->db->affected_rows() * 30);                                    ?>",*/
                        "height": "800",
                        "dataFormat": "json",
                        "dataSource": {
                            "chart": {
                                "caption": "<?php echo $target_name; ?> - Combination Performance",
                                "subCaption": "<?php echo $f_interval; ?>",
                                "xAxisName": "Staff",
                                "pYAxisName": "Reports handled",
                                "xAxisMaxValue": "<?php echo $max_r; ?>",
                                "sYAxisName": "Online Session (hours)",
                                "sNumberSuffix": "",
                                "sYAxisMaxValue": "<?php echo $max_o; ?>",
                                "theme": "fint",
                                "usePlotGradientColor": "0",
                                "plotFillAlpha": "70",
                                "baseFont": "Segoe UI",
                                "baseFontSize": "13",
                                "primaryAxisOnLeft": "1",
                                /*"labelFont": "Segoe UI",
                                 "labelFontSize": "13",
                                 "captionFont": "Segoe UI",
                                 "captionFontSize": "20",
                                 "valueFont": "Segoe UI",*/

                                "labelDisplay": "rotate",
                                "slantLabels": "1",
                                "showBorder": "1",
                                "showShadow": "1",
                                "decimals": "2",
                                "legendShadow": "1",
                            },
                            "categories": [
                                {
                                    "category": [
            <?php echo json_encode($plot_names); ?>
                                    ]
                                }
                            ],
                            "dataset": [
                                {
                                    "seriesName": "Reports",
                                    "parentYAxis": "P",
                                    "data": [
            <?php echo json_encode($reports); ?>
                                    ]
                                },
                                {
                                    "seriesName": "Online",
                                    "parentYAxis": "S",
                                    "renderAs": "area",
                                    "showValues": "0",
                                    "data": [
            <?php echo json_encode($online); ?>
                                    ]
                                },
                                {
                                    "seriesName": "Duty",
                                    "parentYAxis": "S",
                                    "renderAs": "line",
                                    "showValues": "0",
                                    "data": [
            <?php echo json_encode($duty); ?>
                                    ]
                                }
                            ]

                        }
                    });
                    revenueChart.render();
                });
                FusionCharts.ready(function () {
                    var revenueChart = new FusionCharts({
                        type: 'scrollColumn2d',
                        renderAt: 'productivityChartByDuty',
                        width: '100%',
                        height: '100%',
                        dataFormat: 'json',
                        dataSource: {
                            "chart": {
                                "caption": "<?php echo $target_name; ?> - Productivity (Reports/Duty)",
                                "subCaption": "<?php echo $f_interval; ?>",
                                "plotFillAlpha": "70",
                                "baseFont": "Segoe UI",
                                "baseFontSize": "13",
                                "labelDisplay": "rotate",
                                "slantLabels": "1",
                                "showBorder": "1",
                                "showShadow": "1",
                                "xaxisname": "Staff",
                                "yaxisname": "Reports per hour",
                                "showvalues": "1",
                                "placeValuesInside": "1",
                                "rotateValues": "1",
                                "theme": "fint",
                                "showborder": "1",
                                "showcanvasborder": "0",
                                "numVisiblePlot": "12",
                                "scrollheight": "10",
                                "flatScrollBars": "1",
                                "scrollShowButtons": "0",
                                "showHoverEffect": "1",
                                "decimals": "2",
                                "scrollToEnd": "1",
                            },
                            "categories": [
                                {
                                    "category": [
            <?php echo json_encode($plot_names); ?>
                                    ]
                                }
                            ],
                            "dataset": [
                                {
                                    "data": [
            <?php echo json_encode($productivity_duty); ?>
                                    ]
                                }
                            ],
                        }
                    });
                    revenueChart.render();
                });
                FusionCharts.ready(function () {
                    var revenueChart = new FusionCharts({
                        type: 'scrollColumn2d',
                        renderAt: 'productivityChartByOnline',
                        width: '100%',
                        height: '100%',
                        dataFormat: 'json',
                        dataSource: {
                            "chart": {
                                "caption": "<?php echo $target_name; ?> - Productivity (Reports/Online)",
                                "subCaption": "<?php echo $f_interval; ?>",
                                "plotFillAlpha": "70",
                                "baseFont": "Segoe UI",
                                "baseFontSize": "13",
                                "labelDisplay": "rotate",
                                "slantLabels": "1",
                                "showBorder": "1",
                                "showShadow": "1",
                                "xaxisname": "Staff",
                                "yaxisname": "Reports per hour",
                                "showvalues": "1",
                                "placeValuesInside": "1",
                                "rotateValues": "1",
                                "theme": "fint",
                                "showborder": "1",
                                "showcanvasborder": "0",
                                "numVisiblePlot": "12",
                                "scrollheight": "10",
                                "flatScrollBars": "1",
                                "scrollShowButtons": "0",
                                "showHoverEffect": "1",
                                "decimals": "2",
                                "scrollToEnd": "1",
                            },
                            "categories": [
                                {
                                    "category": [
            <?php echo json_encode($plot_names); ?>
                                    ]
                                }
                            ],
                            "dataset": [
                                {
                                    "data": [
            <?php echo json_encode($productivity_online); ?>
                                    ]
                                }
                            ],
                        }
                    });
                    revenueChart.render();
                });

            </script>
            <?php
        } else {
            //
        }
    }

    function drawPieChartIndividual($target, $target_name, $interval, $to) {
        if ($this->canAccess('invidiual', $target, false, true)) {
            $to = (strlen($to) < 1 or strtotime($to) > time()) ? time() : strtotime($to);
            //var_dump(date("jS \of F, Y H:i", time()));
            if ($interval == 'Hourly') {
                $plots = 24;
                $plot_time = 3600;
            } else if ($interval == 'Daily') {
                $plots = 30;
                $plot_time = 3600 * 24;
            } else if ($interval == 'Weekly') {
                $plots = 30;
                $plot_time = 3600 * 24 * 7;
            } else if ($interval == 'Monthly') {
                $plots = 12;
                $plot_time = 3600 * 24 * 30;
            } else if ($interval == 'Yearly') {
                $plots = 10;
                $plot_time = 3600 * 24 * 365;
            }
            $from = $to - ($plot_time * $plots);
            $start = date("jS \of F, Y H:i", $from);
            $stop = date("jS \of F, Y H:i", $to);
            $f_interval = "From $start to $stop";

            $sql = "SELECT type, COUNT(type) AS count FROM reports r WHERE r.handler=$target AND ( UNIX_TIMESTAMP(r.date) BETWEEN '" . $this->db->escape($from) . "' AND '" . $this->db->escape($to) . "' ) GROUP BY type ORDER BY count DESC";

            $qs = $this->db->query($sql);
            if ($this->db->affected_rows() > 0) {
                $data = array();
                $total = 0;
                require_once $this->root . '/functions/functions.php';
                while ($q = $this->db->fetch_array($qs)) {
                    array_push($data, array('label' => getReportTypes($q['type'])['name'], 'value' => $q['count']));
                    $total += $q['count'];
                }
                $this->db->free_result();
                ?>
                <script type="text/javascript">
                    FusionCharts.ready(function () {
                        var revenueChart = new FusionCharts({
                            type: 'doughnut2d',
                            renderAt: 'pieChart',
                            width: '100%',
                            height: '100%',
                            dataFormat: 'json',
                            dataSource: {
                                "chart": {
                                    "caption": "<?php echo $target_name; ?>'s reports",
                                    "subCaption": "<?php echo $f_interval; ?>",
                                    "numberPrefix": "",
                                    "showPercentValues": "1",
                                    "showPercentInTooltip": "0",
                                    "decimals": "1",
                                    "plotFillAlpha": "50",
                                    "baseFont": "Segoe UI",
                                    "baseFontSize": "13",
                                    "showBorder": "1",
                                    "startingAngle": "45",
                                    "theme": "fint",
                                    "showLegend": "1",
                                    "enableMultiSlicing": "0",
                                    "defaultCenterLabel": "<?php echo "Total: " . $total; ?>",
                                    "showShadow": "1",
                                    "enableSmartLabels": "0",
                                    "labelDistance": "10",
                                },
                                "data": [<?php echo json_encode($data); ?>]
                            }
                        }).render();

                    });
                </script>
                <?php
            } else {
                echo "<center>*There isn't enough information to draw this graph yet. Check back later.*</center>";
            }
        }
    }

}
