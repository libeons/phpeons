<?php

/**
 * 天文基本问题
 */

/**
 * 返回朔日的编号, jd应在朔日附近, 允许误差数天
 */
function suoN($jd)
{
    return floor( ($jd + 8) / 29.5306 );
}

/**
 * 太阳光行差, t是世纪数
 */
function gxc_sunLon($t)
{
    // 平近点角
    $v = -0.043126    + 628.301955  * $t - 0.000002732  * $t * $t;
    $e =  0.016708634 - 0.000042037 * $t - 0.0000001267 * $t * $t;
    // 黄经光行差
    return ( -20.49552 * (1 + $e * cos($v)) ) / Constant::rad;
}

/**
 * 黄纬光行差
 */
function gxc_sunLat($t)
{
    return 0;
}

/**
 * 月球经度光行差, 误差0.07"
 */
function gxc_moonLon($t)
{
    return -3.4E-6;
}

/**
 * 月球纬度光行差, 误差0.006"
 */
function gxc_moonLat($t)
{
    return 0.063 * sin(0.057 + 8433.4662 * $t + 0.000064 * $t * $t) / Constant::rad;
}

/**
 * 传入T是2000年首起算的日数(UT), dt是deltatT(日), 精度要求不高时dt可取值为0
 */
function pGST($T, $dt)
{
    // 返回格林尼治平恒星时(不含赤经章动及非多项式部分), 即格林尼治子午圈的平春风点起算的赤经
    $t = ($T + $dt) / 36525;
    $t2 = $t  * $t;
    $t3 = $t2 * $t;
    $t4 = $t3 * $t;
    // T是UT, 下一行的t是力学时(世纪数)
    return Constant::pi2 * (0.7790572732640 + 1.00273781191135448 * $T)
        + (
            0.014506 + 
            4612.15739966 * $t + 
            1.39667721 * $t2 - 0.00009344 * $t3 + 
            0.00001882 * $t4
        ) / Constant::rad;
}

/**
 * 传入力学时J2000起算日数, 返回平恒星时
 */
function pGST2($jd)
{
    $dt = dt_T($jd);
    
    return pGST($jd - $dt, $dt);
}

/**
 * 太阳升降计算
 * jd儒略日(须接近L当地平午UT), L地理经度, fa地理纬度, sj=-1升, sj=1降
 */
function sunShengJ($jd, $L, $fa, $sj)
{
    $jd = floor($jd + 0.5) - $L / Constant::pi2;
    
    for ($i = 0; $i < 2; $i++) {
        // 黄赤交角
        $T = $jd / 36525;
        $E = (84381.4060 - 46.836769 * $T) / Constant::rad;

        // 儒略世纪年数,力学时
        $t = $T + (32 * ($T + 1.8) * ($T + 1.8) - 20) / 86400 / 36525;

        $J = (48950621.66 + 6283319653.318 * $t + 53 * $t * $t - 994
             +334166 * cos( 4.669257+  628.307585 * $t)
             +  3489 * cos( 4.6261  + 1256.61517 * $t )
             +2060.6 * cos( 2.67823 +  628.307585 * $t ) * $t) / 10000000;

        // 太阳黄经以及它的正余弦值
        $sinJ = sin($J);
        $cosJ = cos($J);

        // 恒星时(子午圈位置)
        $gst = (0.7790572732640 + 1.00273781191135448 * $jd) * Constant::pi2
             + (0.014506 + 4612.15739966 * $T + 1.39667721 * $T * $T) / Constant::rad;

        // 太阳赤经
        $A = atan2( $sinJ * cos($E), $cosJ );
        // 太阳赤纬
        $D = asin ( sin($E)* $sinJ );
        
        // 太阳在地平线上的cos(时角)计算
        $cosH0 = (sin(-50 * 60 / Constant::rad) - sin($fa) * sin($D) ) / ( cos($fa) * cos($D) );
        if (abs($cosH0) >= 1) {
            return 0;
        }

        // (升降时角-太阳时角)/太阳速度
        $jd += rad2rrad($sj * acos($cosH0) - ($gst + $L - $A) ) / 6.28;
    }

    // 返回格林尼治UT
    return $jd;
}

/**
 * 时差计算(高精度),t力学时儒略世纪数
 */
function pty_zty($t)
{
    $t2 = $t  * $t;
    $t3 = $t2 * $t;
    $t4 = $t3 * $t;
    $t5 = $t4 * $t;
    
    $L  = ( 1753470142 + 628331965331.8 * $t + 5296.74 * $t2 + 0.432 * $t3 - 0.1124 * $t4 - 0.00009 * $t5 ) / 1000000000
        + Constant::pi - 20.5 / Constant::rad;

    // 黄经章
    $dL = -17.2 * sin(2.1824 - 33.75705 * $t) / Constant::rad;
    // 交角章
    $dE =   9.2 * cos(2.1824 - 33.75705 * $t) / Constant::rad;
    // 真黄赤交角
    $E  = hcjj($t) + $dE;

    // 地球坐标
    $z[0] = XL0_calc(0, 0, $t, 50) + Constant::pi + gxc_sunLon($t) + $dL;
    $z[1] = - ( 2796 * cos(3.1987 + 8433.46616 * $t)
            +   1016 * cos(5.4225 + 550.75532  * $t)
            +    804 * cos(3.88   + 522.3694   * $t) ) / 1000000000;

    // z太阳地心赤道坐标
    $z = llrConv( $z, $E );
    $z[0] -= $dL * cos($E);

    $L = rad2rrad($L - $z[0]);

    // 单位是周(天)
    return $L / Constant::pi2;
}

/**
 * 时差计算(低精度), 误差约在1秒以内, t力学时儒略世纪数
 */
function pty_zty2($t)
{
    $L = ( 1753470142 + 628331965331.8 * $t + 5296.74 * $t * $t ) / 1000000000 + Constant::pi;
    $z = [0, 0, 0];
    $E = (84381.4088 - 46.836051 * $t) / Constant::rad;

    // 地球坐标
    $z[0] = XL0_calc(0, 0, $t, 5) + Constant::pi;
    $z[1] = 0;

    // z太阳地心赤道坐标
    $z = llrConv( $z, $E );
    $L = rad2rrad( $L - $z[0]);

    // 单位是周(天)
    return $L / Constant::pi2;
}
