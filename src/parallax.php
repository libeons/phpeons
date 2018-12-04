<?php

/**
 * 视差修正
 */
function parallax($z, $H, $fa, $high)
{
    // z赤道坐标, fa地理纬度, H时角, high海拔(千米)
    $dw = 1;
    if ($z[2] < 500) {
        $dw = Constant::cs_AU;
    }

    $z[2] *= $dw;

    $f = Constant::cs_ba;
    $u = atan($f * tan($fa));
    $g = $z[0] + $H;

    // 站点与地地心向径的赤道投影长度
    $r0 = Constant::cs_rEar * cos($u)      + $high * cos($fa);
    // 站点与地地心向径的轴向投影长度
    $z0 = Constant::cs_rEar * sin($u) * $f + $high * sin($fa);
    $x0 = $r0 * cos($g);
    $y0 = $r0 * sin($g);

    $s = llr2xyz($z);
    $s[0] -= $x0;
    $s[1] -= $y0;
    $s[2] -= $z0;
    $s = xyz2llr($s);

    $z[0] = $s[0];
    $z[1] = $s[1];
    $z[2] = $s[2] / $dw;
}