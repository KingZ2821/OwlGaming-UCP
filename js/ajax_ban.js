
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */

function load_bans(limit, start, loadto, search_by, search_key) {
    var search_key_input = $('input[type="input"][id="keyword"]').val();
    if (search_key_input && search_key_input.length > 0) {
        search_key = search_key_input;
        var search_by_input = $("#keyword_type option:selected").val();
        if (search_by_input) {
            search_by = search_by_input;
        }
    }
    $.post("../ajax/ajax_ban.php", {
        action: "load_bans",
        limit: limit,
        start: start,
        load_to: loadto,
        search_by: search_by,
        search_key: search_key,
    }, function (stuff) {
        $('#lib_mid').html(stuff);
    });
}

function load_all_bans() {
    clear_filter();
    load_bans();
}

function clear_filter() {
    $('input[type="input"][id="keyword"]').val('');
}

function load_ban(id) {
    $.post("../ajax/ajax_ban.php", {
        action: "load_ban",
        id: id,
    }, function (stuff) {
        $('#ban_detail').html(stuff);
    });
}

function lift(id) {
    if (confirm("Are you sure that you want to lift the ban record #" + id + "?\nThis action can not be undone.")) {
        $.post("../ajax/ajax_ban.php", {
            action: "lift_ban",
            id: id,
        }, function (stuff) {
            alert(stuff);
            //$('#ban_detail').html(stuff);
        });
    }
}


function add_ban_gui() {
    $.post("../ajax/ajax_ban.php", {
        action: "add_ban_gui",
    }, function (stuff) {
        $('#lib_mid').html(stuff);
    });
}

function validate_ban_duration(dur) {
    if (!dur || isNaN(dur)) {
        return false;
    }
    if (dur < 0 || dur > 168) {
        return false;
    }
    return true;
}

function add_ban_goto(step) {
    if (step == 0) {
        var div_to_hide = 'ab_step_1';
        var div_to_show = 'ab_step_0';
        $('#' + div_to_hide).slideUp(500, function () {
            $('#' + div_to_show).slideDown(500);
        });
    } else if (step == 1) {
        var div_to_hide = 'ab_step_0';
        var div_to_show = 'ab_step_1';
        if ($('#ab_step_0').is(":visible")) {
            div_to_hide = 'ab_step_0';
            div_to_show = 'ab_step_1';
        } else {
            div_to_hide = 'ab_step_3';
            div_to_show = 'ab_step_1';
            var method = $('input[type=radio][name=ab_step_1]:checked').val();
            if (method == 'automatic') {
                div_to_hide = 'ab_step_2';
            }
        }
        $('#' + div_to_hide).slideUp(500, function () {
            $('#' + div_to_show).slideDown(500);
        });
    } else if (step == 2) {
        if (validate_ban_duration($('#ab_duration').val())) {
            var method = $('input[type=radio][name=ab_step_1]:checked').val();
            $('#ab_step_1').slideUp(500, function () {
                if (method == 'automatic') {
                    $('#ab_step_2').slideDown(500);
                } else {
                    $('#ab_step_3').slideDown(500);
                }
            });
        } else {
            alert('Ban duration must be from 0 to 168 hours, in which 0 means permanent.');
        }
    }
}

function validate(method, acc1, acc2, serial, ip) {
    if (method == 'automatic') {
        if (!acc1 || acc1.length < 3) {
            alert('Account name must be longer than 3 characters.');
            return false;
        }
    } else {
        var has_something = false;
        if (acc2 && acc2.length > 0) {
            has_something = true;
            if (!acc2 || acc2.length < 3) {
                alert('Account name must be longer than 3 characters.');
                return false;
            }
        }
        if (serial && serial.length > 0) {
            has_something = true;
            if (!serial || serial.length != 32) {
                alert('Serial number must be exactly in 32 characters.');
                return false;
            }
        }
        if (ip && ip.length > 0) {
            has_something = true;
        }
        if (!has_something) {
            alert('You must input at least one item to ban.');
        }
        return has_something;
    }
    return true;
}

function add_ban() {
    var method = $('input[type=radio][name=ab_step_1]:checked').val();
    var account1 = $('#ab_account_1').val();
    var account2 = $('#ab_account_2').val();
    var serial = $('#ab_serial').val();
    var ip = $('#ab_ip').val();
    if (validate(method, account1, account2, serial, ip)) {
        if ($('#ab_submit_btn1').val() == 'Save' && $('#ab_submit_btn2').val() == 'Save') {
            $('#ab_submit_btn1').val('Saving');
            $('#ab_submit_btn2').val('Saving');
            $.post("../ajax/ajax_ban.php", {
                action: "add_ban",
                method: method,
                account1: account1,
                account2: account2,
                serial: serial,
                ip: ip,
            }, function (stuff) {
                $('#ab_submit_btn1').val('Save');
                $('#ab_submit_btn2').val('Save');
                if (stuff == 'ok') {
                    load_bans();
                } else {
                    alert(stuff);
                }
            });
        }
    }
}


