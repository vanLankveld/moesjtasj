<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Converts a 6-digit hex value (like #FFFFFF) philips Hue HSB value
 * @param string $hex
 * @return array an Array with three key-value pairs: ['H'] with value from 0 to 65536, ['S']: values 0 - 255 and ['B']: values 0 - 255
 */
function hex_to_hsb($hex)
{
    $hex = str_replace("#", "", $hex);
    
    $R = hexdec(substr($hex,0,2));
    $G = hexdec(substr($hex,2,2));
    $B = hexdec(substr($hex,4,2));
    
    return rgb_to_hsb($R, $G, $B);
}

/**
 * Converts a 24-bit RGB value to a philips Hue HSB value
 * @param integer $R Red value from 0 to 255
 * @param integer $G Green value from 0 to 255
 * @param integer $B Blue value from 0 to 255
 * @return array an Array with three key-value pairs: ['H'] with value from 0 to 65536, ['S']: values 0 - 255 and ['B']: values 0 - 255
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

    $B = $var_Max;

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
        {
            $H++;
        }
        if ($H > 1)
        {
            $H--;
        }
    }
    
    

    $HSL['H'] = round($H * 65536);
    $HSL['S'] = round($S * 255);
    $HSL['B'] = round($B * 255);

    return $HSL;
}

//Test
//
//$hsv = rgb_to_hsb(0, 0, 255);
//
//echo $hsv['H'] . "<br/>";
//echo $hsv['S'] . "<br/>";
//echo $hsv['V'] . "<br/>";
//
//$hsv = hex_to_hsb('#0000FF');
//
//echo $hsv['H'] . "<br/>";
//echo $hsv['S'] . "<br/>";
//echo $hsv['V'] . "<br/>";


?>