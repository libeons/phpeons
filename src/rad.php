<?php

/**
 * 将弧度转为字串, ext为小数保留位数
 *
 * tim=0 输出格式示例: -23°59' 48.23"
 * tim=1 输出格式示例:  18h 29m 44.52s
 */
function rad2strE($d, $tim, $ext)
{
    $s  = ' ';
    $w1 = '°';
    $w2 = "'";
    $w3 = '"';

    if ($d < 0) {
        $d = -$d;
        $s = '-';
    }
    if ($tim) {
        $d *= 12 / Constant::pi;
        $w1 = 'h ';
        $w2 = 'm';
        $w3 = 's';
    } else {
        $d *= 180 / Constant::pi;
    }

    $a = floor($d);
    $d = ($d - $a) * 60;

    $b = floor($d);
    $d = ($d - $b) * 60;

    $c = floor($d);

    $Q = pow(10, $ext);

    $d = floor( ($d - $c) * $Q + 0.5 );

    if ($d >= $Q) {
        $d -= $Q;
        $c++;
    }
    if ($c >= 60) {
        $c -= 60;
        $b++;
    }
    if ($b >= 60) {
        $b -= 60;
        $a++;
    }

    $a = '   '   . $a;
    $b = '0'     . $b;
    $c = '0'     . $c;
    $d = '00000' . $d;

    $s .= substr($a, strlen($a) - 3, 3) . $w1;
    $s .= substr($b, strlen($b) - 2, 2) . $w2;
    $s .= substr($c, strlen($c) - 2, 2);

    if ($ext) {
        $s .= '.' . substr($d, strlen($d) - $ext, $ext) . $w3;
    }
    
    return $s;
}

/**
 * 将弧度转为字串, 保留2位
 */
function rad2str($d, $tim)
{
    return rad2strE($d, $tim, 2);
}

/**
 * 将弧度转为字串, 精确到分
 *
 * 输出格式示例: -23°59'
 */
function rad2str2($d)
{
    $s  = '+';
    $w1 = '°';
    $w2 = "'";

    if ($d < 0) {
        $d = -$d;
        $s = '-';
    }
    
    $d *= 180 / Constant::pi;
    
    $a = floor($d);
    $b = floor( ($d - $a) * 60 + 0.5 );

    if ($b >= 60) {
        $b -= 60;
        $a++;
    }
    
    $a = '   ' . $a;
    $b = '0'   . $b;

    $s .= substr($a, strlen($a) - 3, 3) . $w1;
    $s .= substr($b, strlen($b) - 2, 2) . $w2;

    return $s;
}

/**
 * 秒转为分秒, fx为小数点位数, fs为1转为"分秒"格式否则转为"角度分秒"格式
 */
function m2fm($v, $fx, $fs)
{
    $gn = '';

    if ($v < 0) {
        $v  = -$v;
        $gn = '-';
    }
    
    $f = floor($v / 60);
    $m = $v - $f * 60;
    
    if (!$fs) {
        return $gn . $f . "'" . sprintf('%.' . $fx . 'f', $m) . '"' ;
    }
    if ($fs === 1) {
        return $gn . $f . '分' . sprintf('%.' . $fx . 'f', $m) . '秒';
    }
    if ($fs === 2) {
        return $gn . $f . 'm' . sprintf('%.' . $fx . 'f', $m) . 's';
    }

    return '';
}


/**
 * 串转弧度, f=1表示输入的s为时分秒
 */
function str2rad($s, $f)
{
    $fh = 1;
    $f  = $f ? 15 : 1;

    if (strpos($s, '-') != false) {
        $fh = -1;
    }

    $search = ['h', 'm', 's', '-', '°', "'", '"'];
    $s = str_replace($search, ' ', $s);

    // 多个空格合并为1个空格
    $s = preg_replace('/ +/', ' ', $s);
    // 去除前后空格
    $s = trim($s);

    $s = explode(' ', $s);

    return $fh * ($s[0] * 3600 + $s[1] * 60 + $s[2] * 1) / Constant::rad * $f;
}
