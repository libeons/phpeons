<?php

//=================================三角函数=======================================
if (!function_exists('int2')) {
    /**
     * [eph0.js]
     *
     * 取整数部分
     */
    function int2(v)
    {
        return floor($v);
    }
}

if (!function_exists('mod2')) {
    /**
     * [eph0.js]
     *
     * 求余
     */
    function mod2(v, n)
    {
        return ($v % $n + $n) % $n;
    }
}
