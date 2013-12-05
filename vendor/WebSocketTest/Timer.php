<?php

namespace WebSocketTest;

include_once 'bestanden/config.php';
include_once 'Utility.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use WebSocketTest\HueLamp;
use WebSocketTest\Question;
use WebSocketTest\Player;

class Timer implements MessageComponentInterface
{
    protected $clients;
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

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
        $ip = "http://192.168.1.102";
        $this->hueLamp = new HueLamp($ip, "newdeveloper", getQuoraId());
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->player[$conn->resourceId] = new Player();

        echo "New connection! ({$conn->resourceId})\n";
        echo "Number of connections: " . $this->clients->count() . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo sprintf('Connection %d sends message "%s" to server' . "\n"
                , $from->resourceId, $msg);

        $responseMsg = "";

        if (strpos($msg, '_') !== false)
        {
            $msgParts = explode('_', $msg);
            switch ($msgParts[0])
            {
                case "start":
                    $this->players[$from->resourceId]->started = true;
                    $this->players[$from->resourceId]->userName = $msgParts[1];
                    $this->players[$from->resourceId]->displayName = getPlayerName($msgParts[1]);
                    echo "client " . $from->resourceId . " started=" . $this->players[$from->resourceId]->started . "\n";
                    $this->selectedQuestions = array();
                    $responseMsg = "start_" . $this->players[$from->resourceId]->displayName;
                    $this->tryStart();
                    break;
                case "answer":
                    $this->clientAnswers[$from->resourceId] = $msgParts[1];
                    $this->players[$from->resourceId]->currentAnswer = $msgParts[1];
                    echo $from->resourceId . " answered: '" . $msgParts[1] . "'.\n";
                    $responseMsg = $this->tryReview();
                    break;
                case "setBrightness":
                    $this->trySetTimerBrightness($from->resourceId);
                    break;
                case "tryagain":
                    echo $from->resourceId . " requests: '" . $msgParts[1] . "'.\n";
                    $this->secondChance = true;
                    $this->players[$from->resourceId]->started = true;
                    $this->tryStart();
                    break;
                case "newquestion":
                    $this->secondChance = false;
                    $this->players[$from->resourceId]->started = true;
                    $responseMsg = $this->tryStart();
                    break;
                case "questionStart":
                    $this->tryHueQuestionStart($from->resourceId);
                    break;
            }
        }

        echo "Response message: $responseMsg\n";

        if ($responseMsg != "")
        {
            $this->sendToAllClients($responseMsg);
        }
    }

    private function sendToAllClients($msg)
    {
        foreach ($this->clients as $client)
        {
            $client->send($msg);
        }
    }

    private function startTimer($length)
    {
        foreach ($this->clients as $client)
        {
            $message = "startTimer_$length";
            echo "sending '$message' to client " . $client->resourceId . "\n";
            $client->send($message);
        }
    }

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        unset($this->players[$conn->resourceId]);

        echo "Connection {$conn->resourceId} has disconnected\n";
        echo "Number of connections: " . $this->clients->count() . "\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function tryStart()
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
            $this->sendCurrentQuestionToClients();
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

    private function tryReview()
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

    private function reviewAnswers()
    {
        $this->hueLamp->alert(false);
        foreach ($this->players as $player)
        {
            $player->started = false;
        }
        foreach ($this->players as $player)
        {
            $answer = $player->currentAnswer;
            if (!$this->currentQuestion->checkAnswer($answer))
            {
                $this->hueLamp->setHueRGB(255, 0, 0);
                $this->hueLamp->setOnOff(true);
                echo "Answered: $answer\n";
                return $this->messageWrongAnswer;
            }
        }

        //0 , 186 , 62
        $this->hueLamp->setHueRGB(0, 100, 35);
        $this->hueLamp->setOnOff(true);
        return $this->messageGoodAnswer;
    }

    private function getNewQuestion()
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

        echo "$query\n";

        $result = mysql_query($query) or die(mysql_error());

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
        $this->sendCurrentQuestionToClients();
        $this->secondChance = false;
        return "";
    }

    private function sendCurrentQuestionToClients()
    {
        $questionJson = "question_" . json_encode($this->currentQuestion);
        echo $questionJson . "\n";
        $this->sendToAllClients($questionJson);
    }

    private function trySetTimerBrightness($clientId)
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

    private function tryHueQuestionStart($clientId)
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

    private function setHueTimerAlert()
    {
        $totalTime = floatval($this->questionTimerLength);
        if ($this->secondChance)
        {
            $totalTime = floatval($this->secondChanceTimerLength);
        }

        echo "10 seconds left...";

        $this->hueLamp->alert(true);
    }
    
    private function getPlayerName($userName)
    {
        $returnName = "";
        
        $query = "SELECT * FROM speler
                WHERE login = $userName;";

        $result = mysql_query($query) or die(mysql_error());

        while ($waardes = mysql_fetch_array($result))
        {
            $voornaam = $waardes['naam'];
            $tussenVoegsel = $waardes['achternaam'];
            $achternaam = $waardes['tussenvoegsel'];
            
            $returnName = "$voornaam $tussenVoegsel $achternaam";
        }
        return $returnName;
    }

}

?>
