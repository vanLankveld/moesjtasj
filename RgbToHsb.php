<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function rgb_to_hsb($R, $G, $B)
{
    $HSL = array();

    $var_R = ($R / 255);
    $var_G = ($G / 255);
    $var_B = ($B / 255);

    $var_Min = min($var_R, $var_G, $var_B);
    $var_Max = max($var_R, $var_G, $var_B);
    $del_Max = $var_Max - $var_Min;

    $V = $var_Max;

    if ($del_Max == 0)
    {
        $H = 0;
        $S = 0;
    } else
    {
        $S = $del_Max / $var_Max;

        $del_R = ( ( ( $var_Max - $var_R ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
        $del_G = ( ( ( $var_Max - $var_G ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;
        $del_B = ( ( ( $var_Max - $var_B ) / 6 ) + ( $del_Max / 2 ) ) / $del_Max;

        if ($var_R == $var_Max)
        {
            $H = $del_B - $del_G;
        } 
        else if ($var_G == $var_Max)
        {
            $H = ( 1 / 3 ) + $del_R - $del_B;
        } 
        else if ($var_B == $var_Max)
        {
            $H = ( 2 / 3 ) + $del_G - $del_R;
        }

        if ($H < 0)
            $H++;
        if ($H > 1)
            $H--;
    }
    
    

    $HSL['H'] = round($H * 65536);
    $HSL['S'] = round(255 - ($S * 255));
    $HSL['V'] = round($V * 255);

    return $HSL;
}

$hsv = rgb_to_hsb(0, 0, 255);

echo $hsv['H'] . "<br/>";
echo $hsv['S'] . "<br/>";
echo $hsv['V'] . "<br/>";
?>