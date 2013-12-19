<?php

namespace WebSocketTest;

include_once 'bestanden/config.php';
include_once 'Utility.php';
include_once 'LogEntry.php';

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use WebSocketTest\HueLamp;
use WebSocketTest\Question;
use WebSocketTest\Player;
use WebsocketTest\LogEntry;
use WebSocketTest\GameLogger;

class Timer implements MessageComponentInterface
{

    protected $clients;
    protected $games = array();
    protected $clientLampIds = array();
    protected $hueLamps = array();
//    protected $started = false;
//    protected $players = array();
//    protected $hueLamp;
//    private $selectedQuestions = array();
//    private $currentQuestion;
//    private $messageGoodAnswer = "answer_true";
//    private $messageWrongAnswer = "answer_false";
//    private $questionTimerLength = 60;
//    private $secondChanceTimerLength = 120;
//    private $secondChance;
//    protected $standardCount = 10;
//    private $gameLogger;

    public function __construct()
    {
        $ip = "http://192.168.1.102";
        $this->hueLamps[1] = new HueLamp($ip, "newdeveloper", 1);
        $this->hueLamps[2] = new HueLamp($ip, "newdeveloper", 2);
        $this->hueLamps[3] = new HueLamp($ip, "newdeveloper", 3);
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        echo "New connection! ({$conn->resourceId})\n";
        echo "Number of connections: " . $this->clients->count() . "\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        echo sprintf('Connection %d sends message "%s" to server' . "\n"
                , $from->resourceId, $msg);

        $responseMsg = "";

        $targetGame = null;

        if (strpos($msg, '_') !== false)
        {
            $clientId = $from->resourceId;
            $msgParts = explode('_', $msg);
            switch ($msgParts[0])
            {
                case "join":
                    $lampId = intval($msgParts[2]);
                    $this->clientLampIds[$clientId] = $lampId;
                    if (!isset($this->games[$lampId]))
                    {
                        $this->games[$lampId] = new Game($this->hueLamps[$lampId]);
                    }
                    $this->games[$lampId]->addPlayer($clientId, $msgParts[1]);
                    echo "client " . $clientId . " Joined game with lamp: " . $lampId . "\n";
                    break;
                case "start":
                    $lampId = $this->clientLampIds[$clientId];
                    echo "client " . $clientId . " started=" . $this->players[$clientId]->started . "\n";
                    $responseMsg = "start_" . $this->players[$clientId]->displayName;
                    $this->games[$lampId]->tryStart();
                    $targetGame = $this->games[$lampId];
                    break;
                case "answer":
                    $gameKey = $this->clientLampIds[$clientId];
                    $this->games[$gameKey]->players[$clientId]->currentAnswer = $msgParts[1];
                    $timeLeft = intval($msgParts[2]);
                    if (!$this->games[$gameKey]->secondChance)
                    {
                        $this->games[$gameKey]->players[$clientId]->currentLogEntry = new LogEntry($this->currentQuestion->id, $timeLeft);
                    } else
                    {
                        $this->games[$gameKey]->players[$clientId]->currentLogEntry->tijdTweedePoging = $timeLeft;
                    }
                    echo $clientId . " answered: '" . $msgParts[1] . "'.\n";
                    $responseMsg = $this->games[$gameKey]->tryReview();
                    break;
                case "setBrightness":
                    $gameKey = $this->clientLampIds[$clientId];
                    $this->games[$gameKey]->trySetTimerBrightness($from->resourceId);
                    $targetGame = $this->games[$gameKey];
                    break;
                case "tryagain":
                    $gameKey = $this->clientLampIds[$clientId];
                    echo $clientId . " requests: '" . $msgParts[1] . "'.\n";
                    $this->games[$gameKey]->secondChance = true;
                    $this->games[$gameKey]->players[$from->resourceId]->started = true;
                    $targetGame = $this->games[$gameKey];
                    if ($this->games[$gameKey]->tryStart() == "sendQuestion")
                    {
                        $this->sendCurrentQuestionToClients($targetGame);
                    }
                    break;
                case "newquestion":
                    $gameKey = $this->clientLampIds[$clientId];
                    $this->games[$gameKey]->secondChance = false;
                    $this->games[$gameKey]->logPlayerData();
                    $this->games[$gameKey]->players[$clientId]->started = true;
                    $targetGame = $this->games[$gameKey];
                    $responseMsg = $this->games[$gameKey]->tryStart();
                    if ($responseMsg == "sendQuestion")
                    {
                        $this->sendCurrentQuestionToClients($targetGame);
                    }
                    break;
                case "questionStart":
                    $gameKey = $this->clientLampIds[$clientId];
                    $this->games[$gameKey]->tryHueQuestionStart($from->resourceId);
                    break;
            }
        }

        echo "Response message: $responseMsg\n";

        if ($responseMsg != "")
        {
            $this->sendToAllClients($responseMsg, $targetGame);
        }
    }

    private function sendToAllClients($msg, Game $game)
    {
        foreach ($game->players as $player)
        {
            foreach ($this->clients as $client)
            {
                if ($client->resourceId == $player->clientId)
                {
                    $client->send($msg);
                }
            }
        }
    }
    
    //Search for:
    //startTimer
    //tryStart
    //tryReview
    //reviewAnswers
    //getNewQuestion

    public function onClose(ConnectionInterface $conn)
    {
        // The connection is closed, remove it, as we can no longer send it messages
        $this->clients->detach($conn);
        $lampId = $this->clientLampIds[$conn->resourceId];
        unset($this->games[$lampId]->players[$conn->resourceId]);

        echo "Connection {$conn->resourceId} has disconnected\n";
        echo "Number of connections: " . $this->clients->count() . "\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function sendCurrentQuestionToClients(Game $game)
    {
        $questionJson = "question_" . json_encode($game->getCurrentQuestion());
        echo $questionJson . "\n";
        $this->sendToAllClients($questionJson, $game);
    }
}

?>
