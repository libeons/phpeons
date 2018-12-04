<?php

/**
 * 蒙气改正
 */

/**
 * 大气折射, h是真高度
 */
function MQC($h)
{
    return  0.0002967 / tan( $h  + 0.003138 / ($h  + 0.08919) );
}

/**
 * 大气折射, ho是视高度
 */
function MQC2($ho)
{
    return -0.0002909 / tan( $ho + 0.002227 / ($ho + 0.07679) );
}
