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

include_once 'bestanden/config.php';

class Game
{

    public $started = false;
    public $players = array();
    private $hueLamp;
    private $selectedQuestions = array();
    private $currentQuestion;
    private $messageGoodAnswer = "answer_true";
    private $messageWrongAnswer = "answer_false";
    private $questionTimerLength = 60;
    private $secondChanceTimerLength = 120;
    private $secondChance;
    private $standardCount = 10;
    private $gameLogger;

    public function __construct($hueLamp)
    {
        $this->hueLamp = $hueLamp;
        $this->gameLogger = new GameLogger();
    }

    public function addPlayer($clientId, $username)
    {
        $this->players[$clientId] = new Player($clientId);
        $this->players[$clientId]->started = true;
        $this->players[$clientId]->userName = $username;
        $this->players[$clientId]->displayName = $this->getPlayerName($username);
    }

    public function removePlayer($clientId)
    {
        unset($this->players[$clientId]);
    }

    public function tryStart()
    {
        $stop = "";
        $length = $this->questionTimerLength;
        foreach ($this->players as $startedClient)
        {
            if (!$startedClient->started)
            {
                echo "false";
                return;
            }
        }
        $this->hueLamp->alert(false);
        if ($this->secondChance)
        {
            $length = $this->secondChanceTimerLength;
            return "sendQuestion";
        } else
        {
            $stop = $this->getNewQuestion();
        }
        if ($stop == "")
        {
            $this->startTimer($length);
        } else
        {
            return $stop;
        }
    }

    public function tryReview()
    {
        foreach ($this->players as $player)
        {
            $answer = $player->currentAnswer;
            if ($answer === "")
            {
                return "";
            }
        }
        echo "All answers received\n";
        $returnMessage = $this->reviewAnswers();
        foreach ($this->players as $player)
        {
            $player->currentAnswer = "";
        }
        return $returnMessage;
    }

    public function reviewAnswers()
    {
        $returnMessage = $this->messageGoodAnswer;
        $answerCorrect = true;
        $this->hueLamp->alert(false);
        foreach ($this->players as $player)
        {
            $player->started = false;
        }
        foreach ($this->players as $player)
        {
            $answer = $player->currentAnswer;
            $playerAnswerCorrect = $this->currentQuestion->checkAnswer($answer);
            if ($answerCorrect)
            {
                $answerCorrect = $this->currentQuestion->checkAnswer($answer);
            }
            if (!$answerCorrect)
            {
                echo "Answered: $answer\n";
                $returnMessage = $this->messageWrongAnswer;
            }

            if (!$this->secondChance)
            {
                $player->currentLogEntry->eerstePogingGoed = $playerAnswerCorrect;
            } else
            {
                $player->currentLogEntry->tweedePogingGoed = $playerAnswerCorrect;
            }
        }

        //0 , 186 , 62
        if (!$answerCorrect)
        {
            $this->hueLamp->setHueRGB(255, 0, 0);
        } else
        {
            $this->hueLamp->setHueRGB(0, 100, 35);
        }

        $this->hueLamp->setOnOff(true);
        return $returnMessage;
    }

    public function setupNewQuestion()
    {
        //$this->questions[0];
        if (count($this->selectedQuestions) >= $this->standardCount)
        {
            return "stop";
        }
        $questionsAsked = "";
        $notInString = "";
        if (count($this->selectedQuestions) > 0)
        {
            foreach ($this->selectedQuestions as $question)
            {
                $questionsAsked .= "$question,";
            }
            $questionsAsked = rtrim($questionsAsked, ', ');
            $notInString = "NOT IN ($questionsAsked)";
        }
        $query = "SELECT * FROM vraag
                WHERE vraag.id $notInString
                ORDER BY RAND()
                LIMIT 1";

        //echo "$query\n";

        $result = mysql_query($query) or die(mysql_error());
        
        $newQuestion = null;

        while ($waardes = mysql_fetch_array($result))
        {
            $id = $waardes['id'];
            $questionText = utf8_encode($waardes['vraag']);
            $image = $waardes['imgUrl'];
            $subject = $waardes['soort'];
            $type = $waardes['type'];
            $multipleChoiceAnswers = array($waardes['antwoord1']);
            if ($type == 'multiple')
            {
                array_push($multipleChoiceAnswers, $waardes['antwoord2']);
                array_push($multipleChoiceAnswers, $waardes['antwoord3']);
                array_push($multipleChoiceAnswers, $waardes['antwoord4']);
            }
            $correctAnswer = $waardes['juisteAntwoord'];
            $this->currentQuestion = new Question($id, $questionText, $image, $subject, $type, $multipleChoiceAnswers, $correctAnswer);
        }

        array_push($this->selectedQuestions, $this->currentQuestion->id);
        $this->secondChance = false;
        return "";
    }
    
    public function getCurrentQuestion()
    {
        return $this->currentQuestion;
    }
    
    public function trySetTimerBrightness($clientId)
    {
        $this->players[$clientId]->finalSeconds = true;
        foreach ($this->players as $player)
        {
            $set = $player->finalSeconds;
            if (!$set)
            {
                return;
            }
        }

        foreach ($this->players as $player)
        {
            $player->finalSeconds = false;
        }
        $this->setHueTimerAlert();
    }

    public function tryHueQuestionStart($clientId)
    {
        $this->players[$clientId]->questionStart = true;

        foreach ($this->players as $player)
        {
            $set = $player->questionStart;
            if (!$set)
            {
                return;
            }
        }
        $this->hueLamp->setHueRGB(50, 0, 255);
        $this->hueLamp->setOnOff(true);
    }

    public function setHueTimerAlert()
    {
        $totalTime = floatval($this->questionTimerLength);
        if ($this->secondChance)
        {
            $totalTime = floatval($this->secondChanceTimerLength);
        }

        echo "10 seconds left...";

        $this->hueLamp->alert(true);
    }

    public function getPlayerName($userName)
    {
        $returnName = "";

        $query = "SELECT * FROM speler
                WHERE login = '$userName';";

        $result = mysql_query($query) or die(mysql_error());

        while ($waardes = mysql_fetch_array($result))
        {
            $voornaam = $waardes['naam'];
            $tussenVoegsel = $waardes['tussenvoegsel'];
            $achternaam = $waardes['achternaam'];

            $returnName = "$voornaam $tussenVoegsel $achternaam";
        }
        return $returnName;
    }
    
    public function logPlayerData()
    {
        foreach ($this->players as $player)
        {
            if ($player->currentLogEntry != null)
            {
                $this->gameLogger->logQuestion($player);
            }
        }
    }

}
