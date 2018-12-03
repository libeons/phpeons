<?php

/**
* 岁差旋转
*/

/**
 * J2000赤道转Date赤道
 */
function CDllr_J2D($t, $llr, $mx)
{
    $Z  = prece($t, 'Z',  $mx) + $llr[0];
    $z  = prece($t, 'z',  $mx);
    $th = prece($t, 'th', $mx);

    $cosW = cos($llr[1]);
    $cosH = cos($th);
    $sinW = sin($llr[1]);
    $sinH = sin($th);

    $A = $cosW * sin($Z);
    $B = $cosH * $cosW * cos($Z) - $sinH * $sinW;
    $C = $sinH * $cosW * cos($Z) + $cosH * $sinW;

    return [
        rad2mrad( atan2($A, $B) + $z ), 
        asin($C),
        $llr[2],
    ];
}

/**
 * Date赤道转J2000赤道
 */
function CDllr_D2J($t, $llr, $mx)
{
    $Z  = -prece($t, 'z',  $mx) + $llr[0];
    $z  = -prece($t, 'Z',  $mx);
    $th = -prece($t, 'th', $mx);

    $cosW = cos($llr[1]);
    $cosH = cos($th);
    $sinW = sin($llr[1]);
    $sinH = sin($th);

    $A = $cosW * sin($Z);
    $B = $cosH * $cosW * cos($Z) - $sinH * $sinW;
    $C = $sinH * $cosW * cos($Z) + $cosH * $sinW;

    return [
        rad2mrad( atan2($A, $B) + $z ),
        asin($C),
        $llr[2],
    ];
}

/**
 * 黄道球面坐标_J2000转Date分点, t为儒略世纪数
 */
function HDllr_J2D($t, $llr, $mx)
{
    // J2000黄道旋转到Date黄道(球面对球面), 也可直接由利用球面旋转函数计算, 但交角接近为0时精度很低
    $r = [ $llr[0], $llr[1], $llr[2] ];

    $r[0] += prece($t, 'fi', $mx);
    $r = llrConv($r,  prece($t, 'w', $mx));

    $r[0] -= prece($t, 'x',  $mx);
    $r = llrConv($r, -prece($t, 'E', $mx));

    return $r;
}

/**
 * 黄道球面坐标_Date分点转J2000, t为儒略世纪数
 */
function HDllr_D2J($t, $llr, $mx)
{
    $r = [ $llr[0], $llr[1], $llr[2] ];

    $r = llrConv($r,  prece($t, 'E', $mx));
    $r[0] += prece($t, 'x',  $mx);

    $r = llrConv($r, -prece($t, 'w', $mx));
    $r[0] -= prece($t, 'fi', $mx);

    $r[0] = rad2mrad( $r[0] );

    return $r;
}