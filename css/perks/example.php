<?php

require_once("resources/active-perks.php");

?>

<head>
    <link rel="stylesheet" type="text/css" href="css/perk-buttons.css">
    <script src="https://ajax.googleapis.com/ajax/libs/webfont/1.4.7/webfont.js"></script><script>WebFont.load({
  google: {
    families: ["Open Sans:300,300italic,400,400italic,600,600italic,700,700italic,800,800italic"]
  }
});</script>
</head>
<body>
  <div class="perks-block">
    <?php 
    foreach ($enabled_perks as $perk) {
        // <div class="perk-button-text">'.$perk_name[$perk].'</div>
    echo '
    <a class="w-inline-block perk-button btn-bg-perk_general" href="#'.$perk.'" data-ix="new-interaction">
    </a>';
    }
    unset($perk);
    ?>
  </div>
</body>
