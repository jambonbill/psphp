<?php
//test charset (Load)
require __DIR__."/../vendor/autoload.php";


//$screen=new PSP\Screen(16,16);

$charset=new PSP\Charset();

//dump data
//var_dump($charset->data());//exit;

//$charset->toPng("/tmp/charset-dump.png");
var_dump($charset->char(0));



//dump all chars
/*
for($i=0;$i<$screen->charNumber();$i++){
	$screen->poke($i,$i,$i);
}

$screen->toPng("/tmp/charset.png");
*/

exit("ok\n");