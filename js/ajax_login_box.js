
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */


function startLoggingIn(username, password, cookies, reload) {
    if ($('#btn_login').val() == "Login") {
        $('#btn_login').val('Logging in..');
        $.post("../ajax/ajax_login.php", {
            username: username,
            password: password,
            cookies: cookies,
        }, function (stuff) {
            $('#btn_login').val('Login');
            if (stuff == '1') {
                if (confirm('Account does not exist!\nDo you want to register a new account now?')) {
                    self.location = "register.php";
                }
            } else if (stuff == '2') {
                if (confirm('Your account is not activated!\n\nPlease check your email address for instructions or click "OK" to resend another activation email.')) {
                    self.location = "activate.php";
                }
            } else if (stuff == '3') {
                alert('Incorrect password!');
            } else if (stuff == '4') {
                alert('You have attempted with 5 incorrect password in a row.\n\nLogin for this account has been disabled. Try again in next 15 minutes!');
            } else {
                $('#login_area').html(stuff);
            }
            ajax_render_main_menu();
            if (reload) {
                location.reload();
            }
        });
    }
}

function ajax_login_box() {
    var username = $('#username').val();
    var password = $('#password').val();
    if (username && username.length > 0 && password && password.length > 0) {
        //var setCookies = promtCookies();
        startLoggingIn(username, password);
    }
    return false;
}

function ajax_logout_box() {
    if (confirm("Are you sure you want to logout?")) {
        $('#login_area').html('<center><br><img src="/images/loading11.gif"/><br><div style="color: #fff;text-shadow: 0px 1px #000;font-family: Arial, Helvetica, sans-serif;font-size: 12px;"><p>Logging out..</p></div></center>');
        $.get("../ajax/ajax_logout.php", {
        }, function (stuff) {
            $('#login_area').html(stuff);
            ajax_render_main_menu();
        });
    }
}

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) != -1)
            return c.substring(name.length, c.length);
    }
    return "";
}

function loadLoginCookies(reload) {
    //alert('cookie loaded - '+getCookie("login_username"));
    var savedUsername = getCookie("login_username");
    var savedPassword = getCookie("login_password");
    var logoutBtnShowing = document.getElementById("username");
    if (!logoutBtnShowing && savedUsername && savedUsername.length > 0 && savedPassword && savedPassword.length > 0) {
        $('#username').val(savedUsername);
        $('#password').val(savedPassword);
        startLoggingIn(savedUsername, savedPassword, true, reload);
    }
}

function promtCookies() {
    var savedUsername = getCookie("login_username");
    var savedPassword = getCookie("login_password");
    var inputUsername = $('#username').val();
    var inputPassword = $('#password').val();
    //alert(savedUsername+'-'+inputUsername);
    if (inputUsername && inputPassword && ((savedUsername != inputUsername) || (savedPassword != inputPassword))) {
        var yesno = confirm('Do you want to remember your username and password for next time?\n\nIf yes, these information will be stored in your browser\'s cookie for 7 days if you don\'t click logout button.');
        if (yesno) {
            return true;
        } else {
            return false;
        }
    }
}

var checkingSession = null;
function checkSession() {
    checkingSession = window.setInterval(function () {
        var logoutBtnShowing = document.getElementById("ajax_logout_box_btn");
        if (logoutBtnShowing == null) {
            //do nathing
        } else {
            $.get("../ajax/ajax_session_check.php", {
            }, function (stuff) {
                $('#login_area').html(stuff);
                loadLoginCookies();
            });
        }
    }, 10000);
}

function stopCheckingSession() {
    clearInterval(checkingSession);
}

function ajax_render_main_menu() {
    $.get("../ajax/ajax_render_menu.php", {
    }, function (data) {
        document.getElementById("main_menu_header").innerHTML = data;
    });
}