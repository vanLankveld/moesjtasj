<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Player
 *
 * @author Gebruiker
 */
namespace WebSocketTest;

class Player
{
    public $userName;
    public $started;
    public $questionStart;
    public $currentAnswer;
    public $finalSeconds;        
    
    function __construct()
    {
        $this->userName = "";
        $this->started = false;
        $this->questionStart = false;
        $this->currentAnswer = "";
        $this->finalSeconds = false;
    }

}
