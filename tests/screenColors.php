<?php

require __DIR__."/../vendor/autoload.php";

$screen=new PSP\Screen(48,25);

for($i=0;$i<$screen->charNumber();$i++){
	$screen->poke($i,$i%64,$i%16);
}

//robokong
$screen->setColorPalette(["#000000","#ffffff","#ff2626","#2b84ff","#7d21ff","#0ba34f","#0b34ff","#f3ff00","#ff5900","#ffd70f","#ff63db","#4a425e","#8d79a8","#61ffb8","#6eccff","#d5c5e8"]);

echo $screen->toPng("/tmp/colors.png");
echo "ok\n";