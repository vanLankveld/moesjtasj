<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of LogEntry
 *
 * @author Gebruiker
 */
namespace WebSocketTest;

class LogEntry
{
    public $vraagId;
    public $tijdEerstePoging;
    public $eerstePogingGoed;
    public $tijdTweedePoging;
    public $tweedePogingGoed;
    
    function __construct($vraagId, $tijdEerstePoging)
    {
        $this->vraagId = $vraagId;
        $this->tijdEerstePoging = $tijdEerstePoging;
    }
}
