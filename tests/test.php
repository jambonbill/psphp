<?php

require __DIR__."/../vendor/autoload.php";


$screen=new PSP\Screen(40,25);
//$screen->fromDb($r);
$screen->toPng("/tmp/screen.png");

echo "ok\n";