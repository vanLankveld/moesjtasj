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
    public $displayName;
    public $started;
    public $questionStart;
    public $currentAnswer;
    public $finalSeconds;
    public $currentLogEntry;
    
    function __construct()
    {
        $this->userName = "";
        $this->displayName = "";
        $this->started = false;
        $this->questionStart = false;
        $this->currentAnswer = "";
        $this->finalSeconds = false;
        $this->currentLogEntry = null;
    }

}
