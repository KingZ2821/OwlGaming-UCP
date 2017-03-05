
/* 
 * ***********************************************************************************************************************
 * Copyright (c) 2015 OwlGaming Community - All Rights Reserved
 * All rights reserved. This program and the accompanying materials are private property belongs to OwlGaming Community
 * Unauthorized copying of this file, via any medium is strictly prohibited
 * Proprietary and confidential
 * Last updated by Maxime, 05-07-2015
 * ***********************************************************************************************************************
 */


function load_perks_gui(step) {
    $('#lib_bot').html('<center><img src="../images/loading3.gif"/><p><b>&nbsp;&nbsp;Loading..</b></p></center>');
    $.post("../ajax/ajax_perks.php", {
        step: step,
    }, function (stuff) {
        $('#lib_bot').slideUp(0);
        $('#lib_bot').html(stuff);
        $('#lib_bot').slideDown(500);
    });
}

function activate_perk(step) {
    if ($('#btnActivate').val() === "Activate") {
        $('#btnActivate').val('Activating..');
        $.post("../ajax/ajax_perks.php", {
            step: step,
            tier: $('#tier').val(),
            fname: $('#fname').val()
        }, function (stuff) {
            if (true || stuff === "ok") {
                $('#lib_bot').slideUp(0);
                $('#lib_bot').html(stuff);
                $('#lib_bot').slideDown(500);
            } else {
                alert(stuff);
                $('#btnActivate').val('Activate');
            }
        });
    }
}


