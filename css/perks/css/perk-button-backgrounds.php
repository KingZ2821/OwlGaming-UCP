<?php

/* NOT IN USE (perkbuttonsg) */

/* Generates a CSS file for usage with perkbuttons */

header("Content-type: text/css", true);

echo "/* Created by Chase for OwlGaming.net */\n\n\n";

require_once("../resources/active-perks.php");

foreach ($enabled_perks as $perk) {

echo "/* Perk - $perk_name[$perk] ($perk) */\n
.perk-button.btn-bg-perk_".$perk." {
  background-image: url('../resources/gloss_layer.png'), url('../resources/perks/".$perk."_blurred.png');
  background-size: cover, cover;
  background-attachment: scroll, scroll;
}
.perk-button.btn-bg-perk_".$perk.":hover {
  background-image: url('../resources/gloss_layer.png'), url('../resources/perks/".$perk.".png');
  background-size: cover, cover;
  background-attachment: scroll, scroll;
  box-shadow: rgba(0, 0, 0, 0.49) 0px 0px 10px 0px;
  -webkit-transform: scale(1);
  -ms-transform: scale(1);
  transform: scale(1);
}
.perk-button.btn-bg-perk_".$perk.":active {
  background-image: url('../resources/gloss_layer.png'), url('../resources/perks/".$perk.".png');
  background-size: cover, cover;
  background-attachment: scroll, scroll;
  -webkit-transform: scale(1) translate(0px, -5px);
  -ms-transform: scale(1) translate(0px, -5px);
  transform: scale(1) translate(0px, -5px);
}\n\n";
    
}

unset($perk); // release reference

?>