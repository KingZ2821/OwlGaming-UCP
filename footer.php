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
?>
</div>
</div>
<div class="footer">
    <div class="footer_wrap">
        <div class="logo_zasprex"><a href="http://mtasa.com/" target="new"><img src="images/zasprex_logo.png" alt="" /></a></div> <!-- Do not remove -->
        <div class="your_logo"><a href="#"><img src="images/your_logo.png" alt="" /></a></div>
        <!---->
        <div class="quick_links">
            <div class="linkset1">
                <ul>
                    <h3>Quick Links</h3>
                    <li><a class="hover" href="ucp.php" target="_self">User Control Panel</a></li>
                    <li><a class="hover" href="http://forums.owlgaming.net" target="_blank">Community Forums</a></li>
                    <li><a class="hover" href="support.php" target="_self">Mantis / Bugs report</a></li>
                    <li><a class="hover" href="https://www.facebook.com/owlgamingcommunity" target="_blank">Facebook Fanpage</a></li>
                    <li><a class="hover" href="http://www.youtube.com/channel/UCov1MwxOPcO_Pi_b5JGT_rQ" target="_blank">Youtube Channel</a></li>

                </ul>
            </div>
            <div class="linkset2">
                <!--
                <ul>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                </ul>
                -->
            </div>
            <div class="linkset3">
                <!--
                <ul>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                    <li><a class="hover" href="index.php" target="_self">Home</a></li>
                </ul>
                -->
            </div>
            <div class="back_top">
                <h3 class="back_top_h3">Back to top</h3>
                <a href="#top"><img class="backtop_img" src="images/arrow.jpg" alt="" border="0" /></a>
            </div>
        </div>

    </div>

</div>
<center><font size="1">
    Copyright © <?php echo date("Y"); ?> OwlGaming Community. All rights reserved.
    </font></center>


<!--<script type="text/javascript" src="http://code.jquery.com/jquery-latest.min.js"></script>-->
<script type="text/javascript" src="js/jquery.nivo.slider.pack.js"></script>
<script type="text/javascript" src="js/static.js"></script>
<script type="text/javascript">
    $(document).ready(function () {
        load_slider();
        setTimeout(function () {
            ajax_load_server_statistics();
        }, 50);
        //checkSession();
        ajax_render_main_menu();
// Create two variable with the names of the months and days in an array
        var monthNames = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        var dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"]

        function setDates() {
            // Create a newDate() object
            var newDate = new Date();
// Extract the current date from Date object
            newDate.setDate(newDate.getDate());
            newDate = convertDateToUTC(newDate);
// Output the day, date, month and year    
            $('.clock-Date').html(dayNames[newDate.getDay()] + " " + newDate.getDate() + ' ' + monthNames[newDate.getMonth()] + ' ' + newDate.getFullYear());
        }
        setDates();
        setInterval(setDates, 10000);

        setInterval(function () {
            // Create a newDate() object and extract the seconds of the current time on the visitor's
            var seconds = new Date().getUTCSeconds();
            // Add a leading zero to seconds value
            $(".clock-sec").html((seconds < 10 ? "0" : "") + seconds);
        }, 1000);

        setInterval(function () {
            // Create a newDate() object and extract the minutes of the current time on the visitor's
            var minutes = new Date().getUTCMinutes();
            // Add a leading zero to the minutes value
            $(".clock-min").html((minutes < 10 ? "0" : "") + minutes);
        }, 1000);

        setInterval(function () {
            // Create a newDate() object and extract the hours of the current time on the visitor's
            var hours = new Date().getUTCHours();
            // Add a leading zero to the hours value
            $(".clock-hours").html((hours < 10 ? "0" : "") + hours);
        }, 1000);

        function convertDateToUTC(date) {
            return new Date(date.getUTCFullYear(), date.getUTCMonth(), date.getUTCDate(), date.getUTCHours(), date.getUTCMinutes(), date.getUTCSeconds());
        }
    });
</script>
</body>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-74375466-2', 'auto');
  ga('send', 'pageview');

</script>
</html>
