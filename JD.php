<?php

class JD
{
    public $Y = 2000;
    public $M = 1;
    public $D = 1;
    public $h = 12;
    public $m = 0;
    public $s = 0;

    public static $Weeks = ['日', '一', '二', '三', '四', '五', '六', '七'];

    /**
     * 公历转儒略日
     */ 
    public static function JD($y, $m, $d)
    {
        $n = 0;
        $G = 0;
        // 判断是否为格里高利历日1582*372+10*31+15
        if($y * 372 + $m * 31 + int2($d) >= 588829) {
            $G = 1;
        }
        if ($m <= 2) {
            $m += 12;
            $y--;
        }
        // 加百年闰
        if ($G) {
            $n = int2($y / 100);
            $n = 2 - $n + int2($n / 4);
        }

        return int2(365.25  * ($y + 4716))
             + int2(30.6001 * ($m + 1)) + $d + $n - 1524.5;
    }

    /**
     * 儒略日数转公历
     */  
    public static function DD($jd)
    {
        $r = new static;
        // 取得日数的整数部份A及小数部分F
        $D = int2($jd + 0.5);
        $F = $jd + 0.5 - $D;
        $c = 0;

        if ($D >= 2299161) {
            $c  = int2(($D - 1867216.25) / 36524.25);
            $D += 1 + $c - int2($c / 4);
        }

        // 年数
        $D += 1524;
        $r->Y = int2(($D - 122.1) / 365.25);
        // 月数
        $D -= int2(365.25 * $r->Y);
        $r->M = int2($D / 30.601); 
        // 日数
        $D -= int2(30.601 * $r->M);
        $r->$D = $D; 
        
        if($r->M > 13) {
            $r->M -= 13;
            $r->Y -= 4715;
        } else {
            $r->M -= 1;
            $r->Y -= 4716;
        }

        // 日的小数转为时分秒
        $F *= 24;
        $r->h = int2($F);
        $F -= $r->h;
        
        $F *= 60;
        $r->m = int2($F);
        $F -= $r->m;
        
        $F *= 60;
        $r->s = $F;
        
        return $r;
    }

    /**
     * 日期转为串
     */  
    public static function DD2str($r)
    {
        $Y = '     ' . $r->Y;
        $M = '0'     . $r->M;
        $D = '0'     . $r->D;

        $h = $r->h;
        $m = $r->m;
        $s = int2($r->s + 0.5);
        
        if ($s >= 60) {
            $s -= 60;
            $m++;
        }
        if ($m >= 60) {
            $m -= 60;
            $h++;
        }
        
        $h = '0' . $h;
        $m = '0' . $m;
        $s = '0' . $s;

        $Y = substr($Y, strlen($Y) - 5, 5);
        $M = substr($M, strlen($M) - 2, 2);
        $D = substr($D, strlen($D) - 2, 2);

        $h = substr($h, strlen($h) - 2, 2);
        $m = substr($m, strlen($m) - 2, 2);
        $s = substr($s, strlen($s) - 2, 2);

        return $Y . '-' . $M . '-' . $D . ' ' . $h . ':' . $m . ':' . $s;
    }

    /**
     * JD转为串
     */  
    public static function JD2str($jd)
    {
       $r = self::DD( $jd );

       return self::DD2str( $r );
    }

    /**
     * 公历转儒略日
     */
    public function toJD()
    {
        return self::JD(
            $this->Y,
            $this->M,
            $this->D + ( ($this->s / 60 + $this->m) / 60 + $this->h) / 24
        ); 
    }

    /**
     * 儒略日数转公历
     */
    public function setFromJD($jd)
    {
        $r = self::DD($jd);

        $this->Y = $r->Y;
        $this->M = $r->M;
        $this->D = $r->D;
        $this->m = $r->m;
        $this->h = $r->h;
        $this->s = $r->s;
    }

    /**
     * 提取jd中的时间(去除日期)
     */
    public static function timeStr($jd)
    {
        var h,m,s;
        $jd += 0.5;
        $jd = ($jd - int2($jd));

        $s = int2($jd * 86400 + 0.5);

        $h = int2($s / 3600);
        $s -= $h * 3600;

        $m = int2($s / 60);
        $s -= $m * 60;
        
        $h = '0' . $h;
        $m = '0' . $m;
        $s = '0' . $s;
    
        return substr($h, strlen($h) - 2, 2) . ':'
             . substr($m, strlen($m) - 2, 2) . ':'
             . substr($s, strlen($s) - 2, 2);
    }

    /**
     * 星期计算
     */
    public static function getWeek($jd)
    {
        return int2($jd + 1.5 + 7000000) % 7;
    }

    /**
     * 求y年m月的第n个星期w的儒略日数
     */
    public static function nnweek($y, $m, $n, $w)
    {
        // 月首儒略日
        $jd = self::JD($y, $m, 1.5);
        // 月首的星期
        $w0 = ($jd + 1 + 7000000) % 7;      
        // jd-w0+7*n是和n个星期0,起算下本月第一行的星期日(可能落在上一月)。加w后为第n个星期w
        $r  = $jd - $w0 + 7 * $n + $w;   

        // 第1个星期w可能落在上个月,造成多算1周,所以考虑减1周
        if ($w >= $w0) {
            $r -= 7;
        }

        if ((int) $n === 5) {
            // 下个月
            $m++;
            if ($m > 12) {
                $m = 1;
                $y++;
            }
            // r跑到下个月则减1周
            if ($r >= self::JD($y, $m, 1.5)) {
                $r -= 7;
            }
        }

        return $r;
    }
}