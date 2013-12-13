<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Game
 *
 * @author Gebruiker
 */
namespace WebSocketTest;

class Game
{
    private $id;
    protected $started = false;
    protected $players = array();
    protected $hueLamp;
    private $selectedQuestions = array();
    private $currentQuestion;
    private $messageGoodAnswer = "answer_true";
    private $messageWrongAnswer = "answer_false";
    private $questionTimerLength = 60;
    private $secondChanceTimerLength = 120;
    private $secondChance;
    protected $standardCount = 10;
    private $gameLogger;
    
    
}
