<?php

require __DIR__."/../vendor/autoload.php";

$screen=new PSP\Screen(16,16);

for($i=0;$i<$screen->charNumber();$i++){
	$screen->poke($i,$i,$i);
}

echo $screen->toJson("/tmp/screen.json");
