<?php

/**
 * 日月黄道平分点坐标、视坐标、速度、已知经度反求时间等方面的计算
 */
class XL
{
    /**
     * 星历函数(日月球面坐标计算)
     *
     * 地球经度计算, 返回Date分点黄经, 传入世纪数, 取项数
     */
    public static function E_Lon($t, $n)
    {
        return XL0_calc(0, 0, $t, $n);
    }

    /**
     * 星历函数(日月球面坐标计算)
     *
     * 月球经度计算, 返回Date分点黄经, 传入世纪数, n是项数比例
     */
    public static function M_Lon($t, $n)
    {
        return XL1_calc(0, $t, $n);
    }

    /**
     * 地球速度, t是世纪数, 误差小于万分3
     */
    public static function E_v($t)
    {
        $f = 628.307585 * $t;

        return 628.332
            + 21      * sin(1.527 + $f)
            + 0.44    * sin(1.48  + $f  * 2) 
            + 0.129   * sin(5.82  + $f) * $t 
            + 0.00055 * sin(4.21  + $f) * $t * $t;
    }

    /**
     * 月球速度计算, 传入世经数
     */
    public static function M_v($t)
    {
        // 误差小于5%
        $v = 8399.71 - 914 * sin( 0.7848 + 8328.691425 * $t + 0.0001523 * $t * $t );

        // 误差小于0.3%
        $v -= 179 * sin( 2.543  + 15542.7543 * $t )  
            + 160 * sin( 0.1874 +  7214.0629 * $t )
            + 62  * sin( 3.14   + 16657.3828 * $t )
            + 34  * sin( 4.827  + 16866.9323 * $t )
            + 22  * sin( 4.9    + 23871.4457 * $t )
            + 12  * sin( 2.59   + 14914.4523 * $t )
            + 7   * sin( 0.23   +  6585.7609 * $t )
            + 5   * sin( 0.9    + 25195.624  * $t )
            + 5   * sin( 2.32   -  7700.3895 * $t )
            + 5   * sin( 3.88   +  8956.9934 * $t )
            + 5   * sin( 0.49   +  7771.3771 * $t )
        ;
        
        return $v;
    }

    /**
     * 月日视黄经的差值
     */
    public static function MS_aLon($t, $Mn, $Sn)
    {
        return self::M_Lon($t, $Mn) + gxc_moonLon($t) - ( self::E_Lon($t, $Sn) + gxc_sunLon($t) + pi() );
    }

    /**
     * 太阳视黄经
     */
    public static function S_aLon($t, $n)
    {
        // 注意, 这里的章动计算很耗时
        return self::E_Lon($t, $n) + nutationLon2($t) + gxc_sunLon($t) + pi();
    }

    /**
     * 已知地球真黄经求时间
     */
    public static function E_Lon_t($W)
    {
        $v = 628.3319653318;
        
        // v的精度0.03%, 详见原文
        $t  = ( $W - 1.75347 ) / $v;
        $v = self::E_v($t);

        // 再算一次v有助于提高精度, 不算也可以
        $t += ( $W - self::E_Lon($t, 10) ) / $v;
        $v = self::E_v($t);   

        $t += ( $W - self::E_Lon($t, -1) ) / $v;
        
        return $t;
    }

    /**
     * 已知真月球黄经求时间
     */
    public static function M_Lon_t($W)
    {
        $v = 8399.70911033384;

        $t = ( $W - 3.81034 ) / $v;

        // v的精度0.5%, 详见原文
        $t += ( $W - self::M_Lon($t, 3) ) / $v;
        $v = self::M_v($t);

        $t += ( $W - self::M_Lon($t, 20) ) / $v;
        $t += ( $W - self::M_Lon($t, -1) ) / $v;
        
        return $t;
    }

    /**
     * 已知月日视黄经差求时间
     */
    public static function MS_aLon_t($W)
    {
        $v = 7771.37714500204;
        
        $t  = ( $W + 1.08472 ) / $v;
        
        // v的精度0.5%, 详见原文
        $t += ( $W - self::MS_aLon($t, 3, 3) ) / $v;
        $v = self::M_v($t) - self::E_v($t);
        
        $t += ( $W - self::MS_aLon($t, 20, 10) ) / $v;
        $t += ( $W - self::MS_aLon($t, -1, 60) ) / $v;
        
        return $t;
    }

    /**
     * 已知太阳视黄经反求时间
     */
    public static function S_aLon_t($W)
    {
        $v = 628.3319653318;

        // v的精度0.03%, 详见原文
        $t  = ( $W - 1.75347 - pi() ) / $v;
        $v = self::E_v($t);

        // 再算一次v有助于提高精度, 不算也可以
        $t += ( $W - self::S_aLon($t, 10) ) / $v;
        $v = self::E_v($t); 

        $t += ( $W - self::S_aLon($t, -1) ) / $v;

        return $t;
    }

    /**
     * 已知月日视黄经差求时间, 高速低精度, 误差不超过600秒 (只验算了几千年)
     */
    public static function MS_aLon_t2($W)
    {
        $v = 7771.37714500204;
        
        $t = ( $W + 1.08472 ) / $v;
        $t2 = $t * $t;
        
        $t -= (
            - 0.00003309 * $t2 
            + 0.10976 * cos( 0.784758 + 8328.6914246 * $t + 0.000152292 * $t2 )
            + 0.02224 * cos( 0.18740  + 7214.0628654 * $t - 0.00021848  * $t2 )
            - 0.03342 * cos( 4.669257 + 628.307585   * $t ) 
        ) / $v;
        
        $L = self::M_Lon($t, 20) - (4.8950632
            + 628.3319653318 * $t
            + 0.000005297    * $t * $t
            + 0.0334166 * cos(4.669257 +  628.307585 * $t)
            + 0.0002061 * cos(2.67823  +  628.307585 * $t) * $t
            + 0.000349  * cos(4.6261   + 1256.61517  * $t)
            - 20.5 / rad()
        );

        $v = 7771.38
            - 914 * sin( 0.7848 + 8328.691425 * $t + 0.0001523 * $t * $t )
            - 179 * sin( 2.543  + 15542.7543  * $t )
            - 160 * sin( 0.1874 + 7214.0629   * $t );

        $t += ( $W - $L ) / $v;
        
        return $t;
    }

    /**
     * 已知太阳视黄经反求时间, 高速低精度, 最大误差不超过600秒
     */
    public static function S_aLon_t2($W)
    {
        $v = 628.3319653318;
        
        $t =  ( $W - 1.75347 - pi() ) / $v;
        
        $t -= (0.000005297 * $t * $t
            + 0.0334166 * cos( 4.669257 + 628.307585 * $t)
            + 0.0002061 * cos( 2.67823  + 628.307585 * $t) * $t
        ) / $v;
        
        $t += ( $W - self::E_Lon($t, 8) - pi()
            + (20.5 + 17.2 * sin(2.1824 - 33.75705 * $t)) / rad() ) / $v;

        return $t;
    }

    /**
     * 月亮被照亮部分的比例
     */
    public static function moonIll($t)
    {
        $t2 = $t  * $t;
        $t3 = $t2 * $t;
        $t4 = $t3 * $t;

        $dm = pi() / 180;

        // 日月平距角
        $D = (297.8502042 + 445267.1115168 * $t - 0.0016300 * $t2 + $t3 / 545868 - $t4 / 113065000) * $dm;
        // 太阳平近点
        $M = (357.5291092 +  35999.0502909 * $t - 0.0001536 * $t2 + $t3 / 24490000                ) * $dm;
        // 月亮平近点
        $m = (134.9634114 + 477198.8676313 * $t + 0.0089970 * $t2 + $t3 / 69699  - $t4 / 14712000 ) * $dm;

        $a = pi() - $D + (
            -6.289 * sin($m)
            +2.100 * sin($M)
            -1.274 * sin($D * 2 - $m)
            -0.658 * sin($D * 2)
            -0.214 * sin($m * 2)
            -0.110 * sin($D)
        ) * $dm;

        return (1 + cos($a)) / 2;
    }

    /**
     * 转入地平纬度及地月质心距离, 返回站心视半径(角秒)
     */
    public static function moonRad($r, $h)
    {
        return cs_sMoon() / $r * (1 + sin($h) * cs_rEar() / $r);
    }
    
    /**
     * 求月亮近点时间和距离, t为儒略世纪数力学时
     */
    public static function moonMinR($t, $min)
    {
        $a = 27.55454988 / 36525;

        if ($min) {
            $b = -10.3302 / 36525;
        } else {
            $b = 3.4471 / 36525;
        }

        // 平近(远)点时间
        $t = $b + $a * int2(($t - $b) / $a + 0.5);

        // 初算二次
        $dt =2 / 36525;
        $r1 = XL1_calc( 2, $t - $dt, 10 );
        $r2 = XL1_calc( 2, $t,       10 );
        $r3 = XL1_calc( 2, $t + $dt, 10 );
        $t += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * $dt / 2;
        $dt = 0.5 / 36525;
        $r1 = XL1_calc( 2, $t - $dt, 20 );
        $r2 = XL1_calc( 2, $t,       20 );
        $r3 = XL1_calc( 2, $t + $dt, 20 );
        $t += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * $dt / 2;
        // 精算
        $dt  = 1200 / 86400 / 36525;
        $r1  = XL1_calc( 2, $t - $dt, -1 );
        $r2  = XL1_calc( 2, $t,       -1 );
        $r3  = XL1_calc( 2, $t + $dt, -1 );
        $t  += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * $dt         / 2;
        $r2 += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * ($r3 - $r1) / 8;
    
        $re = [$t, $r2];

        return $re;
    }

    /**
     * 月亮升交点
     */
    public static function moonNode($t, $asc)
    {
        $a = 27.21222082 / 36525;

        if ($asc) {
            $b = 21 / 36525;
        } else {
            $b = 35 / 36525;
        }

        // 平升(降)交点时间
        $t = $b + $a * int2(($t - $b) / $a + 0.5);

        $dt = 0.5  / 36525;
        $w  = XL1_calc( 1, $t, 10 );
        $w2 = XL1_calc( 1, $t + $dt, 10 );
        $v  = ($w2 - $w) / $dt;
        $t -= $w / $v;
        
        $dt = 0.05 / 36525;
        $w  = XL1_calc( 1, $t, 40 );
        $w2 = XL1_calc( 1, $t + $dt, 40 );
        $v  = ($w2 - $w) / $dt;
        $t -= $w / $v;
        
        $w  = XL1_calc( 1, $t, -1 );
        $t -= $w / $v;

        $re = [$t, XL1_calc( 0, $t, -1 )];

        return $re;
    }

    /**
     * 地球近远点
     */
    public static function earthMinR($t, $min)
    {
        $a = 365.25963586 / 36525;

        if ($min) {
            $b = 1.7 / 36525;
        } else {
            $b = 184.5 / 36525;
        }

        // 平近(远)点时间
        $t = $b + $a * int2(($t - $b) / $a + 0.5);
        
        // 初算二次
        $dt = 3 / 36525;
        $r1 = XL0_calc( 0, 2, $t - $dt, 10 );
        $r2 = XL0_calc( 0, 2, $t,       10 );
        $r3 = XL0_calc( 0, 2, $t + $dt, 10 );
        // 误差几个小时
        $t += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * $dt / 2;
        
        $dt = 0.2 / 36525;
        $r1 = XL0_calc( 0, 2, $t - $dt, 80 );
        $r2 = XL0_calc( 0, 2, $t,       80 );
        $r3 = XL0_calc( 0, 2, $t + $dt, 80 );
        // 误差几分钟
        $t += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * $dt / 2;
        
        // 精算
        $dt = 0.01 / 36525;
        $r1 = XL0_calc( 0, 2, $t - $dt, -1 );
        $r2 = XL0_calc( 0, 2, $t,       -1 );
        $r3 = XL0_calc( 0, 2, $t + $dt, -1 );
        // 误差小于秒
        $t  += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * $dt / 2;

        $r2 += ($r1 - $r3) / ($r1 + $r3 - 2 * $r2) * ($r3 - $r1) / 8;

        $re = [$t, $r2];

        return $re;
    }

}