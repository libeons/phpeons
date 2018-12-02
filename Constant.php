<?php

class Constant
{
	/**
	 * 圆周率
	 */
	const pi = 3.141592653589793;

	/**
	 * 圆周率 * 2
	 */
	const pi2 = self::pi * 2;

	/**
	 * 圆周率 * 0.5
	 */
	const pi_2 = self::pi / 2;
	
	/**
	 * 每弧度的角秒数
	 */
	const rad = 180 * 3600 / self::pi;

	/**
	 * 地球赤道半径(千米)
	 */
	const cs_rEar = 6378.1366; 

	/**
	 * 平均半径
	 */
	const cs_rEarA = 0.99834 * self::cs_rEar; 

	/**
	 * 地球极赤半径比
	 */
	const cs_ba = 0.99664719; 

	/**
	 * 地球极赤半径比的平方
	 */
	const cs_ba2 = self::cs_ba * self::cs_ba;

	/**
	 * 天文单位长度(千米)
	 */
	const cs_AU = 1.49597870691e8; 

	/**
	 * sin(太阳视差)
	 */
	const cs_sinP = self::cs_rEar / self::cs_AU;   

	/**
	 * 太阳视差
	 * 
	 * const cs_PI = asin(self::cs_sinP);
	 */
	const cs_PI = 4.26352097959108 / 100000;

	/**
	 * 光速(行米/秒)
	 */
	const cs_GS = 299792.458; 

	/**
	 * 每天文单位的光行时间(儒略世纪)
	 */
	const cs_Agx = self::cs_AU / self::cs_GS / 86400 / 36525;

	/**
	 * 行星会合周期
	 */
	const cs_xxHH = [116, 584, 780, 399, 378, 370, 367, 367];

	/**
	 * 行星名称
	 */
	const xxName = ['地球', '水星', '金星', '火星', '木星', '土星', '天王星', '海王星', '冥王星'];

	/**
	 * 每弧度的度数
	 */
	const radd = 180 / self::pi;

	/**
	 * 儒略日期TT时 2451545.0
	 */
	const J2000 = 2451545;

	/**
	 * 月亮与地球的半径比(用于半影计算)
	 */
	const cs_k = 0.2725076; 

	/**
	 * 月亮与地球的半径比(用于本影计算)
	 */
	const cs_k2 = 0.2722810; 

	/**
	 * 太阳与地球的半径比(对应959.64)
	 */
	const cs_k0 = 109.1222;  

	/**
	 * 用于月亮视半径计算
	 */
	const cs_sMoon = self::cs_k * self::cs_rEar * 1.0000036 * self::rad;

	/**
	 * 用于月亮视半径计算
	 */
	const cs_sMoon2 = self::cs_k2 * self::cs_rEar * 1.0000036 * self::rad;

	/**
	 * 用于太阳视半径计算
	 */
	const cs_sSun = 959.64; 
}
