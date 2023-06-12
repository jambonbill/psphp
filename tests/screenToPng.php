<?php

require __DIR__."/../vendor/autoload.php";

$screen=new PSP\Screen(16,16);

for($i=0;$i<$screen->charNumber();$i++){
	$screen->poke($i,1,$i);
}

echo $screen->toPng("/tmp/screen.png");
