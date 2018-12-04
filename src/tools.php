<?php

/**
 * 传入普通纪年或天文纪年, 传回天文纪年
 */
function year2Ayear($c)
{
    $y = preg_replace('/[^0-9Bb\*-]/', '', (string) $c);
    $q = substr($y, 0, 1);
    // 通用纪年法(公元前)
    if ($q === 'B' || $q === 'b' || $q === '*') {
        $y = 1 - substr($y, 1, strlen($y));
        if ($y > 0) {
            // die('通用纪法的公元前纪法从B.C.1年开始。并且没有公元0年');
            return -10000;
        }
    } else {
        $y -= 0;
    }
    if ($y < -4712) {
        // die('超过B.C. 4713不准'); 
    }
    if ($y > 9999) {
        // die('超过9999年的农历计算很不准。');
    }

    return $y;
}

/**
 * 传入天文纪年, 传回显示用的常规纪年
 */
function Ayear2year($y)
{
    $y -= 0;
    if ($y <= 0) {
        return 'B' . (-$y + 1);
    }
    return ''.$y;
}

/**
 * 时间串转为小时
 */
function timeStr2hour($s)
{
    $s = preg_replace('/[^0-9:\.]/', '', $s);
    $s = explode(':', $s);

    if (count($s) === 1) {
        $a = substr($s[0], 0, 2) - 0;
        $b = substr($s[0], 2, 2) - 0;
        $c = substr($s[0], 4, 2) - 0;
    } else if (count($s) === 2) {
        $a = $s[0] - 0;
        $b = $s[1] - 0;
        $c = 0;
    } else {
        $a = $s[0] - 0;
        $b = $s[1] - 0;
        $c = $s[2] - 0;
    }
    
    return $a + $b / 60 + $c / 3600;
}
