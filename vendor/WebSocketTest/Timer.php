<?php

namespace WebSocketTest;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

include('RgbToHsb.php');

class Timer implements MessageComponentInterface
{
    protected $clients;
    protected $started = false;
    protected $startedClients = array();
    protected $clientAnswers = array();

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);

        $this->startedClients[$conn->resourceId] = false;
        $this->clientAnswers[$conn->resourceId] = "";

        echo "New connection! ({$conn->resourceId})\n";
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

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e)
    {
        echo "An error has occurred: {$e->getMessage()}\n";

        $conn->close();
    }

    private function tryStart()
    {
        foreach ($this->startedClients as $startedClient)
        {
            if (!$startedClient)
            {
                return;
            }
        }
        $this->startTimer(3);
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
        return $this->reviewAnswers();
    }

    private function reviewAnswers()
    {
        //TODO correct antwoord uit database halen
        $correct = "5";

        foreach ($this->clientAnswers as $answer)
        {
            if ($answer != $correct)
            {
                return "answer_false";
            }
        }
        return "answer_true";
    }

    private function resetAnswers()
    {
        foreach ($this->clientAnswers as $client => $answer)
        {
            $this->clientAnswers[$client] = "";
        }
    }

    /**
     * Sets the state of a specified Hue lamp
     * @param type $bridgeUrl Url of the Hue bridge
     * @param type $username Username
     * @param type $lampNr Lamp number
     * @param type $newColor The new color in an array with 'H' 'S' 'B' keys
     * @param type $turnOn true: lamp is turned on, false: lamp is turned off
     */
    private function setHueLamp($bridgeUrl, $username, $lampNr, $newColor, $turnOn) //$newColor is een array met een 'H' 'S' en 'B' waarde
    {
        $url = "$bridgeUrl/api/$username/lights/$lampNr/state";

        echo "Hue bridge Url: $url\n";

        $h = $newColor['H'];
        $s = $newColor['S'];
        $b = $newColor['B'];

        $turnOnString = $turnOn ? 'true' : 'false';

        $data = "{\"on\":$turnOnString, \"sat\":$s, \"bri\":$b,\"hue\":$h}";

        echo "Hue Data JSON: $data\n";

        $headers = array('Content-Type: application/json');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        
        echo curl_exec($ch);
    }

}

?>
