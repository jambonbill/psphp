<?php
//Test LZW decoder
require __DIR__."/../vendor/autoload.php";

$str='MBugC6ALoAĂĄĆĈĊąćĉăďčĒČđċĐĎĖĚęĕĝĘĔĠėēģěĞġĤĜĨĦĢĪĥğĩħįīİĬıĭĲĮĺĹļĸľķķ90ĵńĴņĳňĻĿĬłŅŉĽŀĖtpCyAAXwŊőęeŋŎŊ08Šăō3QśĶŪŎsgACŗIůŐūčJCŢŤşġŖAŲCĲŭůűŗŮKŵŏŻAtHŕƄƀƊĮƂŰAŇũLŲAŮƔſĲƌŸcLbAƂƇźŜTgFSAVIBUƪƬƮBƝƑƞƍŽƶĩŞŜŲBOƭƯƱƫǂưƪƍ0ơƣƥū6QGŮb0B5QHlAǓǕǐŮaABĈǑAǟǡAǣǥǢǞǠĈHqǤǠvǐ9ǚǔǖHfǰĐvpǰBǲǘAeUǿǗǙǩǧǫǦǨǬȉǾǪĢǣǛǷǙȓǝȏȎȈȌȋȊǭǯǥǲGǴȖȌƘųǾȀǙȃȅȁȚȝșȍțȐĤGuǾȔȷȗc8MĈv3DPcMŲDɀɂɄAɆDNAȽAǮǤ4ȜȹǣZ4ȦƀɒǦȁȫǭȇǠzwwŮɈ9wxƍ';

$B64=PSP\LZW::decode($str);

echo "$B64\n";

$screen=new PSP\Screen(48,27);
$screen->fromB64($B64);

$screen->toPng("/tmp/test.png");

echo "ok\n";