<?php

/**
 * 取整数部分
 */
function int2($v)
{
    return floor($v);
}

/**
 * 对超过0-2PI的角度转为0-2PI
 */
function rad2mrad($v)
{ 
    $v = fmod($v, 2 * Constant::pi);

    if ($v < 0) {
        return $v + 2 * Constant::pi;
    }

    return $v;
}

/**
 * 对超过-PI到PI的角度转为-PI到PI
 */
function rad2rrad($v)
{
    $v = fmod($v, 2 * Constant::pi);

    if ($v <= -Constant::pi) {
        return $v + 2 * Constant::pi;
    }
    if ($v >   Constant::pi) {
        return $v - 2 * Constant::pi;
    }

    return $v;
}

/**
 * 临界余数(a与最近的整倍数b相差的距离)
 */
function mod2($a, $b)
{ 
    $c = $a / $b;
    $c -= floor($c);

    if ($c > 0.5) {
        $c -= 1;
    }

    return $c * $b;
}

/**
 * 球面转直角坐标
 */
function llr2xyz($JW)
{ 
    $r = [0, 0, 0];

    $J = $JW[0];
    $W = $JW[1];
    $R = $JW[2];

    $r[0] = $R * cos($W) * cos($J);
    $r[1] = $R * cos($W) * sin($J);
    $r[2] = $R * sin($W); 

    return $r;
}

/**
 * 直角坐标转球
 */
function xyz2llr($xyz)
{ 
    $r = [0, 0, 0];

    $x = $xyz[0];
    $y = $xyz[1];
    $z = $xyz[2];

    $r[2] = sqrt( $x * $x + $y * $y + $z * $z );
    $r[1] = asin( $z / $r[2] );
    $r[0] = rad2mrad( atan2($y, $x) );

    return $r;
}

/**
 * 球面坐标旋转
 * 黄道赤道坐标变换, 赤到黄E取负
 */
function llrConv($JW, $E)
{ 
    $r = [0, 0, 0];

    $J = $JW[0];
    $W = $JW[1];

    $r[0] = atan2( sin($J) * cos($E) - tan($W) * sin($E),  cos($J) );
    $r[1] = asin ( cos($E) * sin($W) + sin($E) * cos($W) * sin($J) );
    $r[2] = $JW[2];
    $r[0] = rad2mrad($r[0]);

    return $r;
}

/**
 * 赤道坐标转为地平坐标
 */
function CD2DP($z, $L, $fa, $gst)
{ 
    // 转到相对于地平赤道分点的赤道坐标
    $a = [
        $z[0] + Constant::pi / 2 - $gst - $L,
        $z[1],
        $z[2],
    ];
    
    $a = llrConv($a, Constant::pi / 2 - $fa);

    $a[0] = rad2mrad(Constant::pi / 2 - $a[0]);
    
    return $a;
}

/**
 * 求角度差
 */
function j1_j2($J1, $W1, $J2, $W2)
{ 
    $dJ = rad2rrad($J1 - $J2);
    $dW = $W1 - $W2;

    if (abs($dJ) < 1 / 1000 && abs($dW) < 1 / 1000) {
        $dJ *= cos(($W1 + $W2) / 2);
        return sqrt($dJ * $dJ + $dW * $dW);
    }

    return acos( sin($W1) * sin($W2) + cos($W1) * cos($W2) * cos($dJ) );
}

/**
 * 日心球面转地心球面, Z星体球面坐标, A地球球面坐标
 * 本函数是通用的球面坐标中心平移函数, 行星计算中将反复使用
 */
function h2g($z, $a)
{
    // 地球
    $a = llr2xyz($a);
    // 星体
    $z = llr2xyz($z);

    $z[0] -= $a[0];
    $z[1] -= $a[1];
    $z[2] -= $a[2];

    return xyz2llr($z);
}

/**
 * 视差角(不是视差)
 */
function shiChaJ($gst, $L, $fa, $J, $W)
{ 
    // 天体的时角
    $H = $gst + $L - $J; 
    
    return rad2mrad( atan2(sin($H), tan($fa) * cos($W) - sin($W) * cos($H)) );
}
