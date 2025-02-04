
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */

function ajax_load_logs_GUI() {
    $.get("../ajax/ajax_load_logs_gui.php", {
        test: 0,
    }, function (data) {
        document.getElementById("logs_top").innerHTML = data;
    });
}

function validateLogTypes() {
    var logtypes = $('input[type="checkbox"][name="logtype\\[\\]"]:checked').map(function () {
        return this.value;
    }).get();
    if (logtypes.length < 1) {
        $('#logtype_reminder').text("(Please choose at least one type of logs!)");
        return false;
    }
    return true;
}

function validateKeyword() {
    return $('input[type="input"][name="keyword"]').val().length > 0;
}

function validateTimeIntervals() {
    /*
     var end_point = $("#end_point option:selected").val();
     var start_point = $("#start_point option:selected").val();
     if (end_point <= start_point) {
     $('#logtype_reminder').text("('End Point' time must be deeper in the past than 'Start Point' time!)");
     return false;
     } else if (end_point - start_point > 2196) {
     $('#logtype_reminder').text("('Start Point' and 'End Point' times can't be more than 3 months different. Please shorten the time interval between 2 points.)");
     return false;
     }*/
    return true;
}

function onLogsSubmit() {
    var search_btn = $('input[type="submit"][id="search_btn"]');
    if (search_btn.val() == "Search") {
        load_server_logs();
    }
}

function load_server_logs(limit, start, load_to) {
    //$('#lib_mid').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');

    if (validateLogTypes() && validateTimeIntervals()) {
        var search_btn = $('input[type="submit"][id="search_btn"]');
        search_btn.val("Searching..");
        var logtypes1 = $('input[type="checkbox"][name="logtype\\[\\]"]:checked').map(function () {
            return this.value;
        }).get();
        $('#logs_result').html('<br><img src="../images/loading3.gif"/><p>&nbsp;&nbsp;Querying..</p>');
        $.post("../ajax/ajax_logs_start_searching.php", {
            logTypes: logtypes1,
            keyword: $('#keyword').val(),
            keyword_type: $("#keyword_type option:selected").val(),
            end_point: $("#end_point option:selected").val(),
            start_point: $("#start_point option:selected").val(),
            max_results: $("#max_results").val(),
            limit: limit,
            start: start,
            load_to: load_to,
        }, function (data) {
            $('#logs_result').html(data);
            search_btn.val("Search");
            //$('#logs_loading').html('<center><input type="button" id="hide_logs" value="Clear Screen" onclick="clear_logs_screen();" /> </center>')

        });
    }
}

function clear_logs_screen() {
    $('#logs_result').html('');
}