<?php
//test charset (Load CTM)
require __DIR__."/../vendor/autoload.php";


//$screen=new PSP\Screen(16,16);

$charset=new PSP\Charset();
$charset->debug(true);


$charset->loadCtm("testin.ctm");


$charset->toPng("/tmp/custom-charset.png");


exit("ok\n");