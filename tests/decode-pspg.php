<?php

require __DIR__."/../vendor/autoload.php";

$decoder=new PSP\Pspg();

$decoder->load("record.pspg");

echo "ok\n";