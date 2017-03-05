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
//$root = (empty($_SERVER['DOCUMENT_ROOT']) ? getenv("DOCUMENT_ROOT") : $_SERVER['DOCUMENT_ROOT']);
?>
<div class="news_holder">
    <div class="news_top"></div>
    <div class="news">
        <div id="news_aboutus" class="news_container">
            <img src="images/news_bulletin.png" alt="" />
            OwlGaming is a roleplaying game community based on a Grand Theft Auto modification named Multi Theft Auto. <br><br>
            OwlGaming is a long-term roleplaying project introduced on January 1st, 2014, with the ambition of housing well-experienced roleplayers and put them under our wing; we intend on bringing roleplayers the best of experiences in their roleplaying career for as long as possible!<br><br>
            <center><iframe height="200" width="355" src="https://www.youtube.com/embed/videoseries?list=PLEUfyQ8lqKZok90h2fTAg64ZpDjQiPPqS&autoplay=<?php echo (($_SERVER["REQUEST_URI"] == '/register.php' ) ? 1 : 0); ?>&version=3&loop=1&rel=0&showsearch=0&cc_load_policy=1&showinfo=0&controls=0" frameborder="0"></iframe></center><br>
            Our development team consists of professional programmers and the most experienced scripters in MTA RP servers in recent years. With a strong and stable development team, our MTA server script is continuously improving and growing.<br><br>
            We possess over a well-developed and matured script which not only serves as a major attraction to our server, but to increase the joy our roleplayers gain from staying at Owl.
        </div>
        <div id="news_commits" class="news_container">
            <img src="images/news_commits.png" alt="" />
            <?php
            require_once "$root/classes/Rss.class.php";
            $rss = new Rss();
            $rss->outputFeedDev();
            ?>
        </div>
        <div id="news_roster" class="news_container">
            <img src="images/news_roster.png" alt="" />
            <?php
            require_once './classes/User.class.php';
            $user = new User();
            $staffs = $user->output_all_staff();
            ?>
        </div>
        <script>
            function hide_all_news(time) {
                $('#news_aboutus').slideUp(time);
                $('#news_commits').slideUp(time);
                $('#news_roster').slideUp(time);
            }
            hide_all_news(0);
            $('#news_aboutus').slideDown(0);
            function switchNews() {
                hide_all_news(500);
                if ($('#news_aboutus').is(":visible")) {
                    $('#news_commits').slideDown(500, function () {
                        $('#btn_switch').html('▼ Staff');
                    });
                } else if ($('#news_commits').is(":visible")) {
                    $('#news_roster').slideDown(500, function () {
                        $('#btn_switch').html('▲ About us');
                    });
                } else if ($('#news_roster').is(":visible")) {
                    $('#news_aboutus').slideDown(500, function () {
                        $('#btn_switch').html('▼ Live Release Notes');
                    });
                }
            }
        </script>
    </div>
    <div class="news_bottom"></div>
    <a href="#" style="text-decoration: none;" onclick="switchNews();
            return false;"><b><div id="btn_switch" >▼ Live Release Notes</div></b></a>
</div>
<div class="right_holder">
    <div class="stats_box">
        <div class="statsrigh_top"></div>
        <div class="statsright_middle">
            <img class="stats_img" src="images/stats.png" alt="" />
            <div id="server_stats" style>
                <center>
                    <br>
                    <img src="/images/loading11.gif"/>
                    <br>
                    <div style="color: #fff;text-shadow: 0px 1px #000;font-family: Arial, Helvetica, sans-serif;font-size: 12px;">
                        <p>Querying..</p>
                    </div>
                </center>
            </div>
        </div>
        <div class="statsright_bottom"></div>
    </div>
    <br>
    <div id="fb-root"></div>
    <script>(function (d, s, id) {
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id))
                return;
            js = d.createElement(s);
            js.id = id;
            js.src = "//connect.facebook.net/en_US/sdk.js#xfbml=1&version=v2.4&appId=373160042763030";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));</script>
    <div class="fb-page" style="margin-left: 12px; margin-top: 5px;" data-href="https://www.facebook.com/owlgamingcommunity" data-width="290" data-height="214" data-colorscheme="dark" data-small-header="false" data-adapt-container-width="true" data-hide-cover="false" data-show-facepile="true" data-show-posts="false"><div class="fb-xfbml-parse-ignore"><blockquote cite="https://www.facebook.com/owlgamingcommunity"><a href="https://www.facebook.com/owlgamingcommunity">OwlGaming Community</a></blockquote></div></div>
    <!--
    <div class="stats_box">
        <div class="statsrigh_top"></div>
        <div class="statsright_middle">
            <img class="stats_img" src="images/gallery.png" alt="" />
            <div id="server_stats" style="height:171px;">
                <center>
                </center>
            </div>
        </div>
        <div class="statsright_bottom"></div>
    </div>
    -->
</div>


