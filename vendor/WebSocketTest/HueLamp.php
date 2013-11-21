<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of HueLamp
 *
 * @author Gebruiker
 */

namespace WebSocketTest;

class HueLamp
{
    private $url;
    
    private $turnedOn;
    private $alert;   
    
    private $H;
    private $S;
    private $B;
    
    public function __construct($bridgeUrl, $username, $lampNr)
    {
        $this->url = "$bridgeUrl/api/$username/lights/$lampNr/state";
        $this->turnedOn = false;
        
        $color = hex_to_hsb("#FFFFFF");
        
        $this->H = $color['H'];
        $this->S = $color['S'];
        $this->B = $color['B'];
        
        $this->alert = false;
        $this->setHue();
    }
    
    public function setOnOff($on)
    {
        $this->turnedOn = true;
        
        $this->setHue();
    }
    
    public function setHueRGB($R, $G, $B)
    {
        $color = rgb_to_hsb($R, $G, $B);
        
        $this->H = $color['H'];
        $this->S = $color['S'];
        $this->B = $color['B'];
        
        $this->setHue();
    }
    
    public function setHueHex($hex)
    {
        $color = hex_to_hsb($hex);
        
        $this->H = $color['H'];
        $this->S = $color['S'];
        $this->B = $color['B'];
        
        $this->setHue();
    }
    
    public function setHueHSB($H, $S, $B)
    {        
        $this->H = $H;
        $this->S = $S;
        $this->B = $B;
        
        $this->setHue();
    }
    
    public function setBrightness($B)
    {        
        $this->B = $B;
        
        $this->setHue();
    }
    
    public function alert($on)
    {
        $this->alert = $on;
        
        $this->setHue();
    }
        
    private function setHue()
    {
        //echo "Hue bridge Url: $this->url\n";
        echo "Set Hue data...\n";

        $turnOnString = $this->turnedOn ? 'true' : 'false';
        $alertOnString = $this->alert ? 'lselect' : 'none';

        $data = "{\"on\":$turnOnString, \"sat\":$this->S, \"bri\":$this->B,\"hue\":$this->H, \"alert\":\"$alertOnString\"}";

        echo "Hue Data JSON: $data\n";

        $headers = array('Content-Type: application/json');

        $ch = curl_init($this->url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        curl_exec($ch);
    }
    
}
