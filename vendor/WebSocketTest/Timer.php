<?php

namespace WebSocketTest;

include 'bestanden/config.php';
include 'Utility.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use WebSocketTest\HueLamp;
use WebSocketTest\Question;

class Timer implements MessageComponentInterface {

    protected $clients;
    protected $started = false;
    protected $startedClients = array();
    protected $clientAnswers = array();
    protected $hueLamp;
    private $selectedQuestions = array();
    private $currentQuestion;

    public function __construct() {
        $this->clients = new \SplObjectStorage;

        $this->hueLamp = new HueLamp("http://192.168.1.102", "newdeveloper", getQuoraId());
    }

    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->startedClients[$conn->resourceId] = false;
        $this->clientAnswers[$conn->resourceId] = "";

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        echo sprintf('Connection %d sends message "%s" to server' . "\n"
                , $from->resourceId, $msg);

        $responseMsg = "";

        if (strpos($msg, '_') !== false) {
            $msgParts = explode('_', $msg);
            switch ($msgParts[0]) {
                case "start":
                    $responseMsg = "start_" . $msgParts[1];
                    $this->startedClients[$from->resourceId] = true;
                    echo "client " . $from->resourceId . " started=" . $this->startedClients[$from->resourceId] . "\n";
                    $this->tryStart();
                    break;
                case "answer":
                    $this->clientAnswers[$from->resourceId] = $msgParts[1];
                    echo $from->resourceId . " answered: '" . $msgParts[1] . "'.\n";
                    $responseMsg = $this->tryReview();
                    break;
            }
        }

        echo "Response message: $responseMsg";

        if ($responseMsg != "") {
            $this->sendToAllClients($responseMsg);
        }
    }

    private function sendToAllClients($msg) {
        foreach ($this->clients as $client) {
            $client->send($msg);
        }
    }

    private function startTimer($length) {
        foreach ($this->clients as $client) {
            $message = "startTimer_$length";
            echo "sending '$message' to client " . $client->resourceId . "\n";
            $client->send($message);
        }
    }

    public function onClose(ConnectionInterface $conn) {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function tryStart() {
        foreach ($this->startedClients as $startedClient) {
            if (!$startedClient) {
                return;
            }
        }
        $this->getQuestion();
        $this->startTimer(3);
    }

    private function tryReview() {
        foreach ($this->clientAnswers as $answer) {
            if ($answer === "") {
                return "";
            }
        }
        echo "All answers received\n";
        return $this->reviewAnswers();
    }

    private function reviewAnswers() {

        foreach ($this->clientAnswers as $answer) {
            if (!$this->currentQuestion->checkAnswer($answer)) {
                $this->hueLamp->setHueRGB(255, 0, 0);
                $this->hueLamp->setOnOff(true);
                return "answer_false";
            }
        }

        //0 , 186 , 62
        $this->hueLamp->setHueRGB(0, 255, 0);
        $this->hueLamp->setOnOff(true);
        return "answer_true";
    }

    private function resetAnswers() {
        foreach ($this->clientAnswers as $client => $answer) {
            $this->clientAnswers[$client] = "";
        }
    }

    private function getQuestion() {
        //$this->questions[0];
        $questionsAsked = "";
        $notInString = "";
        if (count($this->selectedQuestions) > 0) {
            foreach ($this->selectedQuestions as $question) {
                $questionsAsked .= "$question,";
            }
            $questionsAsked = rtrim($questionsAsked, ', ');
            $notInString = "NOT IN ($questionsAsked)";
        }
        $query = "SELECT * FROM vraag
                RIGHT OUTER JOIN antwoord ON vraag.id=antwoord.id
                WHERE vraag.id $notInString
                ORDER BY RAND()
                LIMIT 1";

        echo "$query\n";

        $result = mysql_query($query) or die(mysql_error());

        while ($waardes = mysql_fetch_array($result)) {

            $id = $waardes['id'];
            $questionText = $waardes['vraag'];
            $image = $waardes['imgUrl'];
            $subject = $waardes['soort'];
            $type = $waardes['type'];
            $multipleChoiceAnswers = null;
            if ($type == 'multiple') {
                $A = $waardes['antwoord1'];
                $B = $waardes['antwoord2'];
                $C = $waardes['antwoord3'];
                $D = $waardes['antwoord4'];
                $multipleChoiceAnswers = array('A' => $A,
                    'B' => $B,
                    'C' => $C,
                    'D' => $D);
            }
            $correctAnswer = $waardes['juisteAntwoord'];
            array_push($this->selectedQuestions, $id);
            $this->currentQuestion = new Question($id, $questionText, $image, $subject, $type, $multipleChoiceAnswers, $correctAnswer);
        }
        
        echo "question_".json_encode($this->currentQuestion)."\n";
        $this->sendToAllClients(json_encode($this->currentQuestion));
    }

}

?>
