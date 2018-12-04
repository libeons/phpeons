<?php

require 'vendor/autoload.php';

echo rad2mrad(1000);die;

$Y = 2018;
$M = 12;
$D = 4;
$his = '17:34:16';
$J = 116.383333;

$t = timeStr2hour($his);

$jd = JDClass::JD(
	year2Ayear($Y),
	$M - 0,
	$D - 0 + $t / 24
);

$curTZ = -8;

$ob = new \stdClass;

obb::mingLiBaZi( $jd + $curTZ / 24 - Constant::J2000, $J / Constant::radd, $ob );

var_dump($ob);
