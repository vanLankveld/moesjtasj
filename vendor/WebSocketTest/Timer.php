<?php

namespace WebSocketTest;

use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;

class Timer implements MessageComponentInterface
{

    protected $clients;
    protected $started = false;
    
    protected $startedClients = array();

    public function __construct()
    {
        $this->clients = new \SplObjectStorage;
    }

    public function onOpen(ConnectionInterface $conn)
    {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
        
        $this->startedClients[$conn->resourceId] = false;

        echo "New connection! ({$conn->resourceId})\n";
    }

    public function onMessage(ConnectionInterface $from, $msg)
    {
        $numRecv = count($this->clients) - 1;
        echo sprintf('Connection %d sends message "%s" to server' . "\n"
                , $from->resourceId, $msg);

        $responseMsg = "";

        if (strpos($msg, '_') !== false)
        {
            $msgParts = explode('_', $msg);
            switch ($msgParts[0])
            {
                case "start":
                    $responseMsg = $msgParts[1]. " start.";
                    $this->startedClients[$from->resourceId] = true;
                    echo "client ".$from->resourceId." started=".$this->startedClients[$from->resourceId]."\n";
                    $this->tryStart();
                    break;
            }
        }

        if ($responseMsg != "")
        {
            foreach ($this->clients as $client)
            {
                $client->send($responseMsg);
            }
        }
    }

    private function startTimer($length)
    {
        foreach ($this->clients as $client)
        {
            $message = "startTimer_$length";
            echo "sending '$message' to client ".$client->resourceId;
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
        $this->startTimer(30);
    }

}

?>
