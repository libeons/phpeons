<?php

/**
 * 天文计算部分
 * 
 * 物件 SZJ : 用来计算日月的升起、中天、降落
 * 
 * 注意，上述函数或物件是纯天文学的，根据实际需要组合使用可以得到所需要的各种日月坐标，
 *     计算精度及计算速度也是可以根据需要有效控制的。
 */

/**
 * 日月升降物件
 * 
 * 日月的升中天降, 不考虑气温和气压的影响
 */
class SZJ
{
    /**
     * 站点地理经度, 向东测量为正
     */
    public static $L  = 0;  

    /**
     * 站点地理纬度
     */
    public static $fa = 0;  

    /**
     * TD-UT
     */
    public static $dt = 0;  

    /**
     * 黄赤交角
     */
    public static $E = 0.409092614;

    /**
     * 多天的升中降
     */
    protected $rts = [];

    /**
     * h地平纬度, w赤纬, 返回时角
     */
    public static function getH($h, $w)
    {
        $c = ( sin($h) - sin(self::fa) * sin($w) ) / cos(self::fa) / cos($w);
        if (abs($c) > 1) {
            return Constant::pi;
        }
        return acos($c);
    }
   
    /**
     * 章动同时影响恒星时和天体坐标,所以不计算章动。返回时角及赤经纬
     */
    public static function Mcoord($jd, $H0, $r)
    {
        // 低精度月亮赤经纬
        $z = m_coord( ($jd + self::dt) / 36525, 40, 30, 8 );
        // 转为赤道坐标
        $z = llrConv( $z, self::E );
        // 得到此刻天体时角
        $r['H'] = rad2rrad( pGST($jd, self::dt) + self::L - $z[0] );
        // 升起对应的时角
        if ($H0) {
            $r['H0'] = self::getH( 0.7275 * cs_rEar / $z[2] - 34 * 60 / Constant::rad, $z[1] );
        }
    }

    /** 
     * 月亮到中升降时刻计算, 传入jd含义与St()函数相同
     */
    public static function Mt($jd)
    {
        self::$dt = dt_T($jd);
        self::$E  = hcjj($jd / 36525);

        // 查找最靠近当日中午的月上中天, mod2的第1参数为本地时角近似值
        $jd -= mod2(0.1726222 + 0.966136808032357 * $jd - 0.0366 * self::dt + self::L / Constant::pi2, 1);

        $r = new \stdClass;
        $sv = Constant::pi2 * 0.966;
        $r->z = $r->x = $r->s = $r->j = $r->c = $r->h = $jd;
        // 月亮坐标
        self::Mcoord($jd, 1, $r);
        $r->s += (-$r->H0 - $r->H ) / $sv;
        $r->j += ( $r->H0 - $r->H ) / $sv;
        $r->z += (      0 - $r->H ) / $sv;
        $r->x += (Constant::pi - $r->H ) / $sv;

        self::Mcoord($r->s, 1, $r);
        $r->s += rad2rrad( -$r->H0 - $r->H ) / $sv;

        self::Mcoord($r->j, 1, $r);
        $r->j += rad2rrad( +$r->H0 - $r->H ) / $sv;

        self::Mcoord($r->z, 0, $r);
        $r->z += rad2rrad(     0 - $r->H ) / $sv;

        self::Mcoord($r->x, 0, $r);
        $r->x += rad2rrad( Constant::pi - $r->H ) / $sv;

        return $r;
    }

    /**
     * 章动同时影响恒星时和天体坐标,所以不计算章动。返回时角及赤经纬
     */
    public static function Scoord($jd, $xm, $r)
    {
        $xm = (int) $xm;

        // 太阳坐标(修正了光行差)
        $z = [
            XL::E_Lon( ($jd + self::dt) / 36525, 5 ) + Constant::pi - 20.5 / Constant::rad,
            0,
            1,
        ];
        // 转为赤道坐标
        $z = llrConv( $z, self::E );
        // 得到此刻天体时角
        $r->H = rad2rrad( pGST($jd, self::dt) + self::L - $z[0] );

        // 地平以下50分
        if ($xm === 10 || $xm === 1) {
            $r->H1 = self::getH(-50 * 60   / Constant::rad, $z[1]);
        }

        // 地平以下6度
        if ($xm === 10 || $xm === 2) {
            $r->H2 = self::getH(-6 * 3600  / Constant::rad, $z[1]);
        }

        // 地平以下12度
        if ($xm === 10 || $xm === 3) {
            $r->H3 = self::getH(-12 * 3600 / Constant::rad, $z[1]);
        }

        // 地平以下18度
        if ($xm === 10 || $xm === 4) {
            $r->H4 = self::getH(-18 * 3600 / Constant::rad, $z[1]);
        }
    }

    /**
     * 太阳到中升降时刻计算, 传入jd是当地中午12点时间对应的2000年首起算的格林尼治时间UT
     */
    public static function St($jd)
    {
        self::$dt = dt_T($jd);
        self::$E  = hcjj($jd / 36525);

        // 查找最靠近当日中午的日上中天, mod2的第1参数为本地时角近似值
        $jd -= mod2($jd + self::L / Constant::pi2, 1);

        $r = new \stdClass;
        $sv = Constant::pi2;
        $r->z = $r->x = $r->s = $r->j = $r->c = $r->h = $r->c2 = $r->h2 = $r->c3 = $r->h3 = $jd;
        $r->sm = '';
        
        // 太阳坐标
        self::Scoord($jd, 10, $r);
        // 升起
        $r->s += (-$r->H1 - $r->H ) / $sv;
        // 降落
        $r->j += ( $r->H1 - $r->H ) / $sv;

        // 民用晨
        $r->c += (-$r->H2 - $r->H ) / $sv;
        // 民用昏
        $r->h += ( $r->H2 - $r->H ) / $sv;
        // 航海晨
        $r->c2+= (-$r->H3 - $r->H ) / $sv;
        // 航海昏
        $r->h2+= ( $r->H3 - $r->H ) / $sv;
        // 天文晨
        $r->c3+= (-$r->H4 - $r->H ) / $sv;
        // 天文昏
        $r->h3+= ( $r->H4 - $r->H ) / $sv;

        // 中天
        $r->z += (    0 - $r->H ) / $sv;
        // 下中天
        $r->x += ( Constant::pi - $r->H ) / $sv;
        
        self::Scoord($r->s, 1, $r);
        $r->s += rad2rrad( -$r->H1 - $r->H ) / $sv;
        if ($r->H1 === Constant::pi) {
            $r->sm .='无升起.';
        }
        self::Scoord($r->j, 1, $r);
        $r->j += rad2rrad( +$r->H1 - $r->H ) / $sv;
        if ($r->H1 === Constant::pi) {
            $r->sm .='无降落.';
        }

        self::Scoord($r->c,  2, $r);
        $r->c += rad2rrad( -$r->H2 - $r->H ) / $sv;
        if ($r->H2 === Constant::pi) {
            $r->sm .='无民用晨.';
        }
        self::Scoord($r->h,  2, $r);
        $r->h += rad2rrad( +$r->H2 - $r->H ) / $sv;
        if ($r->H2 === Constant::pi) {
            $r->sm .='无民用昏.';
        }
        self::Scoord($r->c2, 3, $r);
        $r->c2 += rad2rrad( -$r->H3 - $r->H ) / $sv;
        if ($r->H3 === Constant::pi) {
            $r->sm .='无航海晨.';
        }
        self::Scoord($r->h2, 3, $r);
        $r->h2 += rad2rrad( +$r->H3 - $r->H ) / $sv;
        if ($r->H3 === Constant::pi) {
            $r->sm .='无航海昏.';
        }
        self::Scoord($r->c3, 4, $r);
        $r->c3 += rad2rrad( -$r->H4 - $r->H ) / $sv;
        if ($r->H4 === Constant::pi) {
            $r->sm .='无天文晨.';
        }
        self::Scoord($r->h3, 4, $r);
        $r->h3 += rad2rrad( +$r->H4 - $r->H ) / $sv;
        if ($r->H4 === Constant::pi) {
            $r->sm .='无天文昏.';
        }

        self::Scoord($r->z, 0, $r);
        $r->z += ( 0 - $r->H ) / $sv;
        self::Scoord($r->x, 0, $r);
        $r->x += rad2rrad( Constant::pi - $r->H ) / $sv;

        return $r;
    }

    /**
     * 多天升中降计算, jd是当地起始略日(中午时刻), sq是时区
     */
    public static function calcRTS($jd, $n, $Jdl, $Wdl, $sq)
    {
        if (empty(self::$rts)) {
            for ($i = 0; $i < 31; $i++) {
                self::$rts[ $i ] = new \stdClass;
            }
        }
        
        // 设置站点参数
        self::$L = $Jdl;
        self::$fa = $Wdl; // !!!
        $sq /= 24;
        
        for ($i = 0; $i < $n; $i++) {
            $r = self::$rts[ $i ];
            $r->Ms = $r->Mz = $r->Mj = '--:--:--';
        }
        
        for ($i = -1; $i <= $n; $i++) {
            // 太阳
            if($i >= 0 && $i < $n) {
                $r = self::St($jd + $i + $sq);
                // 升
                self::$rts[ $i ]->s = $JD.timeStr($r->s - $sq);
                // 中
                self::$rts[ $i ]->z = $JD.timeStr($r->z - $sq);
                // 降
                self::$rts[ $i ]->j = $JD.timeStr($r->j - $sq);
                // 晨
                self::$rts[ $i ]->c = $JD.timeStr($r->c - $sq);
                // 昏
                self::$rts[ $i ]->h = $JD.timeStr($r->h - $sq);
                // 光照时间,timeStr()内部+0.5,所以这里补上-0.5
                self::$rts[ $i ]->ch = $JD.timeStr($r->h - $r->c - 0.5);
                // 昼长
                self::$rts[ $i ]->sj = $JD.timeStr($r->j - $r->s - 0.5);
            }
            // 月亮
            $r = self::Mt($jd + $i + $sq);
            $c = int2($r->s - $sq + 0.5) - $jd;
            if ($c >= 0 && $c < $n) {
                self::$rts[ $c ]->Ms = JD::timeStr($r->s - $sq);
            }

            $c = int2($r->z - $sq + 0.5) - $jd;
            if ($c >= 0 && $c < $n) {
                self::$rts[ $c ]->Mz = JD::timeStr($r->z - $sq);
            }

            $c = int2($r->j - $sq + 0.5) - $jd;
            if ($c >= 0 && $c < $n) {
                self::$rts[ $c ]->Mj = JD::timeStr($r->j - $sq);
            }

        }
        self::$rts['dn'] = $n; // !!!
    }
}