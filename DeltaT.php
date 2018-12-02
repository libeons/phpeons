<?php

class DeltaT
{
    /**
     * TD - UT1 计算表
     */
    private static $dt_at = [
        -4000, 108371.7, -13036.80, 392.000,  0.0000,
         -500,  17201.0,   -627.82,  16.170, -0.3413,
         -150,  12200.6,   -346.41,   5.403, -0.1593,
          150,   9113.8,   -328.13,  -1.647,  0.0377,
          500,   5707.5,   -391.41,   0.915,  0.3145,
          900,   2203.4,   -283.45,  13.034, -0.1778,
         1300,    490.1,    -57.35,   2.085, -0.0072,
         1600,    120.0,     -9.81,  -1.532,  0.1403,
         1700,     10.2,     -0.91,   0.510, -0.0370,
         1800,     13.4,     -0.72,   0.202, -0.0193,
         1830,      7.8,     -1.81,   0.416, -0.0247,
         1860,      8.3,     -0.13,  -0.406,  0.0292,
         1880,     -5.4,      0.32,  -0.183,  0.0173,
         1900,     -2.3,      2.06,   0.169, -0.0135,
         1920,     21.2,      1.69,  -0.304,  0.0167,
         1940,     24.2,      1.22,  -0.064,  0.0031,
         1960,     33.2,      0.51,   0.231, -0.0109,
         1980,     51.0,      1.29,  -0.026,  0.0032,
         2000,    63.87,       0.1,       0,       0,
         2005,     64.7,      0.21,       0,       0,
         2012,     66.8,      0.22,       0,       0,
         2018,     69.0,      0.36,       0,       0,
         2028,     72.6,
    ];

    /**
     * 二次曲线外推 (dt_ext)
     */
    public static function ext($y, $jsd)
    {
        $dy = ($y - 1820) / 100;

        return -20 + $jsd * $dy * $dy;
    }

    /**
     * 计算世界时与原子时之差, 传入年 (dt_calc)
     */
    public static function calc($y)
    {
        $length = count(self::$dt_at);

        // 表中最后一年
        $y0 = self::$dt_at[ $length-2 ];
        // 表中最后一年的deltatT 
        $t0 = self::$dt_at[ $length-1 ];

        if ($y >= $y0) {
            // jsd是y1年之后的加速度估计
            // 瑞士星历表jsd=31, NASA网站jsd=32, skmap的jsd=29
            $jsd = 31;
            if ($y > $y0 + 100) {
                return self::ext($y, $jsd);
            }
            // 二次曲线外推
            $v  = self::ext($y,  $jsd);
            // ye年的二次外推与te的差
            $dv = self::ext($y0, $jsd) - $t0;

            return $v - $dv * ($y0 + 100 - $y) / 100;
        }

        $d = self::$dt_at;

        for ($i = 0; $i < $length; $i += 5) {
            if ($y < $d[ $i+5 ]) {
                break;
            }
        }

        $t1 = ($y - $d[ $i ]) / ($d[ $i+5 ] - $d[ $i ]) * 10;
        $t2 = $t1 * $t1;
        $t3 = $t2 * $t1;

        return $d[ $i+1 ] + $d[ $i+2 ] * $t1 + $d[ $i+3 ] * $t2 + $d[ $i+4 ] * $t3;
    }

    /**
     * 传入儒略日(J2000起算), 计算 TD - UT(单位: 日) (dt_T)
     */
    public static function T($t)
    {
        return self::calc($t / 365.2425 + 2000) / 86400.0;
    }
}

if (!function_exists('dt_ext')) {
    /**
     * 二次曲线外推 dt_ext
     */
    function dt_ext($y, $jsd)
    {
        return DeltaT::ext($y, $jsd);
    }
}

if (!function_exists('dt_calc')) {
    /**
     * 计算世界时与原子时之差, 传入年 (dt_calc)
     */
    function dt_calc($y)
    {
        return DeltaT::calc($y);
    }
}

if (!function_exists('dt_T')) {
    /**
     * 传入儒略日(J2000起算), 计算 TD - UT(单位: 日) (dt_T)
     */
    function dt_T($t)
    {
        return DeltaT::T($t);
    }
}
