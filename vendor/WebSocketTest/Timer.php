<?php

namespace WebSocketTest;

include_once 'bestanden/config.php';
include_once 'Utility.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use WebSocketTest\HueLamp;
use WebSocketTest\Question;

class Timer implements MessageComponentInterface
{

    protected $clients;
    protected $started = false;
    protected $startedClients = array();
    protected $clientAnswers = array();
    protected $clientCalled10Seconds = array();
    protected $clientCalledQuestionstart = array();
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

        $this->startedClients[$conn->resourceId] = false;
        $this->clientAnswers[$conn->resourceId] = "";
        $this->clientCalled10Seconds[$conn->resourceId] = false;
        $this->clientCalledQuestionstart[$conn->resourceId] = false;

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
                    $responseMsg = "start_" . $msgParts[1];
                    $this->startedClients[$from->resourceId] = true;
                    echo "client " . $from->resourceId . " started=" . $this->startedClients[$from->resourceId] . "\n";
                    $this->selectedQuestions = array();
                    $this->tryStart();
                    break;
                case "answer":
                    $this->clientAnswers[$from->resourceId] = $msgParts[1];
                    echo $from->resourceId . " answered: '" . $msgParts[1] . "'.\n";
                    $responseMsg = $this->tryReview();
                    break;
                case "setBrightness":
                    $this->trySetTimerBrightness($from->resourceId);
                    break;
                case "tryagain":
                    echo $from->resourceId . " requests: '" . $msgParts[1] . "'.\n";
                    $this->secondChance = true;
                    $this->startedClients[$from->resourceId] = true;
                    $this->tryStart();
                    break;
                case "newquestion":
                    $this->secondChance = false;
                    $this->startedClients[$from->resourceId] = true;
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

        unset($this->startedClients[$conn->resourceId]);
        unset($this->clientAnswers[$conn->resourceId]);
        unset($this->clientCalled10Seconds[$conn->resourceId]);
        unset($this->clientCalledQuestionstart[$conn->resourceId]);

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
        foreach ($this->startedClients as $startedClient)
        {
            if (!$startedClient)
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
        foreach ($this->clientAnswers as $answer)
        {
            if ($answer === "")
            {
                return "";
            }
        }
        echo "All answers received\n";
        $returnMessage = $this->reviewAnswers();
        $this->clientAnswers = array_fill_keys(array_keys($this->clientAnswers), "");
        return $returnMessage;
    }

    private function reviewAnswers()
    {
        $this->hueLamp->alert(false);
        $this->startedClients = array_fill_keys(array_keys($this->startedClients), false);
        foreach ($this->clientAnswers as $answer)
        {
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
            array_push($this->selectedQuestions, $id);
            $this->currentQuestion = new Question($id, $questionText, $image, $subject, $type, $multipleChoiceAnswers, $correctAnswer);
        }
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
        $this->clientCalled10Seconds[$clientId] = true;
        foreach ($this->clientCalled10Seconds as $set)
        {
            if (!$set)
            {
                return;
            }
        }
        $this->clientCalled10Seconds = array_fill_keys(array_keys($this->clientCalled10Seconds), false);
        $this->setHueTimerAlert();
    }

    private function tryHueQuestionStart($clientId)
    {
        $this->clientCalledQuestionstart[$clientId] = true;
        foreach ($this->clientCalledQuestionstart as $set)
        {
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

}

?>
