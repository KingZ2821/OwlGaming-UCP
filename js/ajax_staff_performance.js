
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 07-10-2015
 * ***********************************************************************************************************************
 */

function load_team_performance( interval, load_to) {
    var from, to;
    if (interval == 'custom') {
        from = $('#from').val();
        to = $('#to').val();
        if (isNaN(new Date(from).getTime()) || isNaN(new Date(to).getTime())) {
            return alert('Invalid datetime interval!');
        }
    }
    
    $('#' + load_to).slideUp(500, function () {
        $.post("../ajax/ajax_staff_performance.php", {
            action: "load_team_performance",
            interval: interval,
            from: from,
            to: to,
        }, function (stuff) {
            $('#' + load_to).html(stuff);
            $('#' + load_to).slideDown(500);
        });
    });
}

function load_individual_performance( load_to ) {
    var to = $('#to').val();
    if (to != '' && isNaN(new Date(to).getTime())) {
        return alert('Invalid timepoint!');
    }
    $('#' + load_to).slideUp(500, function () {
        $.post("../ajax/ajax_staff_performance.php", {
            action: "load_individual_performance",
            target: $("#target option:selected").val(),
            targetname: $("#target option:selected").text(),
            interval: $("#interval option:selected").val(),
            to: to,
        }, function (stuff) {
            $('#' + load_to).html(stuff);
            $('#' + load_to).slideDown(500);
        });
    });
}

function menu_switch(to, load_to) {
    $('#' + load_to).slideUp(500, function () {
        $.post("../ajax/ajax_staff_performance.php", {
            action: "menu_switch",
            to: to,
            load_to: load_to,
        }, function (stuff) {
            $('#' + load_to).html(stuff);
            $('#' + load_to).slideDown(500);
        });
    });
}



