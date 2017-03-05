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

class User {

    protected $root;
    protected $db;

    function __construct() {
        $root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
        $this->root = $root;
        require_once "$this->root/config.inc.php";
    }

    function dbConnect($conn = false) {
        if ($conn) {
            $this->db = $conn;
        } else {
            if (!$this->db) {
                require_once "$this->root/classes/Database.class.php";
                $this->db = new Database("MTA");
                $this->db->connect();
            }
        }
    }

    function dbClose() {
        @$this->db->close();
    }
    
    function getConn() {
        return $this->db;
    }

    protected function start_session() {
        require_once "$this->root/classes/Session.class.php";
        $session = new Session();
        $session->start_session('_owlgaming');
        return $session;
    }

    protected function is_logged() {
        if (session_status() == PHP_SESSION_NONE) {
            $this->start_session();
        }
        return isset($_SESSION['userid']) and is_numeric($_SESSION['userid']) and $_SESSION['userid'] > 0;
    }

    function login($username, $password) {
        //First we fetch salt and some other stuff
        $sql = "SELECT *, g.ip AS gip FROM accounts a LEFT JOIN google_authenticator g ON a.id=g.userid WHERE username='" . $this->db->escape($username) . "' ";
        $record = $this->db->query_first($sql);
        $salt = $record['salt'];
        $userid = $record['id'];
        $username = $record['username'];
        $email = $record['email'];
        $serverPassword = $record['password'];
        $activated = $record['activated'];
        $admin_level = $record['admin'];
        $supporter_level = $record['supporter'];
        $vct_level = $record['vct'];
        $scripter_level = $record['scripter'];
        $mapper_level = $record['mapper'];

        //Account is not found.
        if (!isset($salt)) {
            echo 1;
            return 1;
        }

        //Account is not active.
        if ($activated == '-1') {
            echo 2;
            return 2;
        }

        // If the user exists we check if the account is locked from too many login attempts.
        if ($this->bruteforced($userid)) {
            // Account is locked 
            // Send an email to user saying their account is locked
            echo 4;
            return 4;
        }

        // Check if the password in the database matches
        if (md5(md5($password) . $salt) != $serverPassword) {
            $now = time();
            $this->db->query("INSERT INTO ucp_login_attempts(user_id, time) VALUES ('$userid', '$now')");
            echo 3;
            return 3;
        }

        //Everything so far is so good
        // Get the user-agent string of the user.
        //$user_browser = $_SERVER['HTTP_USER_AGENT'];
        //Before starting secure session, let's destroy all insecure session first, just in case.
        $this->logout();

        //Create new secure session
        $session = $this->start_session();

        //Make new session id
        $session->session_regenerate_id();

        $_SESSION['username'] = $username;
        $_SESSION['userid'] = $userid;
        $_SESSION['email'] = $email;
        $_SESSION['admin'] = $admin_level;
        $_SESSION['supporter'] = $supporter_level;
        $_SESSION['vct'] = $vct_level;
        $_SESSION['scripter'] = $scripter_level;
        $_SESSION['mapper'] = $mapper_level;
        if ($admin_level > 0 or $supporter_level > 0 or $vct_level > 0 or $scripter_level > 0 or $mapper_level > 0)
            $_SESSION['tc_backend'] = $record['tc_backend'];
        $groups = array(); //',';
        if ($admin_level == 1) {
            //$groups.='18,';
            array_push($groups, 18);
        } else if ($admin_level == 2) {
            //$groups.='17,';
            array_push($groups, 17);
        } else if ($admin_level == 3) {
            //$groups.='64,';
            array_push($groups, 64);
        } else if ($admin_level == 4) {
            //$groups.='15,';
            array_push($groups, 15);
        } else if ($admin_level == 5) {
            //$groups.='5,';
            array_push($groups, 5);
        }
        
        if ($supporter_level == 1) {
            //$groups.='30,';
            array_push($groups, 30);
        } else if ($supporter_level == 2) {
            array_push($groups, 31);
        }
        
        if ($vct_level == 1) {
            //$groups.='43,';
            array_push($groups, 43);
        } else if ($vct_level == 2) {
            //$groups.='39,';
            array_push($groups, 39);
        }
        
        if ($scripter_level > 0) {
            //$groups.='32,';
            array_push($groups, 32);
        }
        
        if ($mapper_level == 1) {
            //$groups.='28,';
            array_push($groups, 28);
        } else if ($mapper_level == 2) {
            //$groups.='44,';
            array_push($groups, 44);
        }
        
        $_SESSION['groups'] = implode(',', $groups);

        if (!is_null($record['enabled']) and is_numeric($record['enabled']) and $record['enabled'] == 1 and $record['gip'] and ! is_null($record['gip'])) { // two factor
            $_SESSION['ga_ip'] = $record['gip'];
        }

        //Check if user is banned.
        $ban = $this->db->query_first("SELECT b.id, CASE WHEN until IS NOT NULL THEN UNIX_TIMESTAMP(until) ELSE -1 END AS 'banned_until', "
                . "a.username AS 'admin', "
                . "reason "
                . "FROM bans b LEFT JOIN accounts a ON b.admin=a.id WHERE account=$userid AND (until IS NULL OR until > NOW())");
        if ($ban and $ban['id'] and is_numeric($ban['id'])) {
            $_SESSION['banned_until'] = $ban['banned_until'];
            ?>
            <script>
                alert("You're currently banned by <?php echo (!is_null($ban['admin']) ? ("admin " . $ban['admin']) : "SYSTEM"); ?>\nReason: <?php echo $ban['reason']; ?>\nDuration: <?php echo ($ban['banned_until'] == -1 ? "Permanent" : ("until " . date('M d, Y H:i', $ban['banned_until']) . " (Servertime)")); ?>\n\nSome features on UCP may be limited until your ban gets lifted.");
            </script>
            <?php
        } else {
            $_SESSION['banned_until'] = false;
        }

        //$_SESSION['login_string'] = hash('sha512', $serverPassword . $user_browser);
        // Login successful.
        include "$this->root/views/logged-in.php";
        return true;
    }

    private function bruteforced($user_id) {
        // Get timestamp of current time 
        $now = time();

        // All login attempts are counted from the past 15 minutes. 
        $valid_attempts = $now - (60 * 15);

        $this->db->query("SELECT time FROM ucp_login_attempts WHERE user_id = '$user_id' AND time > '$valid_attempts'");
        // If there have been more than 5 failed logins 
        $result = $this->db->affected_rows() > 5;
        @$this->db->free_result();
        return $result;
    }

    function logout() {
        $this->start_session();

        // Unset all session values 
        $_SESSION = array();

        // get session parameters 
        $params = session_get_cookie_params();

        // Delete the actual cookie. 
        setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);

        // Destroy session 
        return session_destroy();
        //include '../views/login.html';
    }

    function is_banned() {
        if (!$_SESSION['banned_until']) {
            return false;
        } else {
            if ($_SESSION['banned_until'] == -1) {
                return true;
            } else {
                return $_SESSION['banned_until'] > time();
            }
        }
    }

    private function get_all_staff($distinct = true) {
        $this->dbConnect();
        $return = $this->db->fetch_all_array("SELECT  " . ($distinct ? 'DISTINCT' : '') . "(id), username, admin, supporter, vct, mapper, adminreports, "
                        . "CASE WHEN (l.`from` IS NOT NULL AND l.`to` IS NOT NULL AND l.`from`<=NOW() AND l.`to`>=NOW() AND l.effective=1) THEN 1 ELSE 0 END AS 'has_loa' FROM accounts a LEFT JOIN account_loa l ON a.id=l.user_id "
                        . "WHERE (admin>0 and admin<6) OR supporter>0 OR vct>0 OR mapper>0 ORDER BY has_loa, admin DESC, supporter DESC, vct DESC, mapper DESC, adminreports DESC, username");
        $this->dbClose();
        return $return;
    }

    function output_all_staff($cache = 600) {
        // define the path and name of cached file
        $cachefile = "$this->root/cache/news_roster.php";
        // Check if the cached file is still fresh. If it is, serve it up and exit.
        if ($cache > 0 and file_exists($cachefile) && time() - $cache < filemtime($cachefile)) {
            include($cachefile);
        } else {
            // if there is either no file OR the file to too old, render the page and capture the HTML.
            $staffs = $this->get_all_staff();
            ob_start();
            echo "<html>";
            ?>
            <table width="100%">
                <tr>
                    <td valign="top">
                        <b>Administration Team</b><br>
                        <?php
                        foreach ($staffs as $key => $staff) {
                            if ($staff['admin'] > 0) {
                                if ($staff['has_loa'] == 1) {
                                    echo "<font color='#A3A3A3' >";
                                } else {
                                    echo "<font color='#FFF' >";
                                }
                                echo '■ ' . $staff['username'] . '<br>';
                                //unset($staffs[$key]);
                                echo "</font>";
                            }
                        }
                        ?>
                    </td>
                    <td valign="top">
                        <b>Support Team</b><br>
                        <?php
                        foreach ($staffs as $key => $staff) {
                            if ($staff['supporter'] > 0) {
                                if ($staff['has_loa'] == 1) {
                                    echo "<font color='#A3A3A3' >";
                                } else {
                                    echo "<font color='#FFF' >";
                                }
                                echo '■ ' . $staff['username'] . '<br>';
                                //unset($staffs[$key]);
                                echo "</font>";
                            }
                        }
                        ?>
                    </td>
                    <td valign="top">
                        <b>Vehicle Consultation Team</b><br>
                        <?php
                        foreach ($staffs as $key => $staff) {
                            if ($staff['vct'] > 0) {
                                if ($staff['has_loa'] == 1) {
                                    echo "<font color='#A3A3A3' >";
                                } else {
                                    echo "<font color='#FFF' >";
                                }
                                echo '■ ' . $staff['username'] . '<br>';
                                //unset($staffs[$key]);
                                echo "</font>";
                            }
                        }
                        ?>
                        <br><b>Mapping Team</b><br>
                        <?php
                        foreach ($staffs as $key => $staff) {
                            if ($staff['mapper'] > 0) {
                                if ($staff['has_loa'] == 1) {
                                    echo "<font color='#A3A3A3' >";
                                } else {
                                    echo "<font color='#FFF' >";
                                }
                                echo '■ ' . $staff['username'] . '<br>';
                                //unset($staffs[$key]);
                                echo "</font>";
                            }
                        }
                        ?>
                    </td>
                </tr>
            </table>
            <?php
            echo "</html>";
            // We're done! Save the cached content to a file
            $fp = fopen($cachefile, 'w');
            fwrite($fp, ob_get_contents());
            fclose($fp);
            // finally send browser output
            ob_end_flush();
        }
    }

}
