<?php

require 'vendor/autoload.php';

$Y = 2018;
$M = 2;
$D = 4;
$his = '05:28:29';
$his = '05:28:30';
// $his = '11:14:20';
// $his = '11:14:21';
$J = 116.383333;

$t = timeStr2hour($his);

// var_dump($t);

$jd = JDClass::JD(
	year2Ayear($Y),
	$M - 0,
	$D - 0 + $t / 24
);

// var_dump($jd);

$curTZ = -8;

$ob = new \stdClass;

obb::mingLiBaZi( $jd + $curTZ / 24 - Constant::J2000, $J / Constant::radd, $ob );

$s = "$Y-$M-$D $his -> {$ob->bz_jn}年 {$ob->bz_jy}月 {$ob->bz_jr}日 {$ob->bz_js}时\n";

echo "$s";
