<?php
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use WebSocketTest\Timer;

    require '/vendor/autoload.php';

    $server = IoServer::factory(
        new WsServer(
            new Timer()
        )
      , 8080
    );

    $server->run();

?>
