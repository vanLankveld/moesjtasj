<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace WebSocketTest;

include_once 'bestanden/config.php';

use WebSocketTest\Player;

/**
 * Description of PlayerLogger
 *
 * @author Gebruiker
 */
class GameLogger
{
    public $gameId;
    
    public function __construct()
    {
        $this->gameId = $this->newGameLog();
    }
    
    private function newGameLog()
    {
        $date  = date("Y-m-d H:i:s");
        $query = "INSERT INTO game (datum)
                  VALUES('$date');";
        mysql_query($query) or die(mysql_error());
        return mysql_insert_id();
    }
    
    public function logQuestion($player)
    {
        $logEntry = $player->currentLogEntry;
        
        $playerUserName = $player->userName;
        $selectPlayerId = "SELECT id FROM speler WHERE login='$playerUserName';";
        $result = mysql_query($selectPlayerId) or die(mysql_error());
        
        $gameId = $this->gameId;
        $playerId = intval(mysql_fetch_row($result)[0]);
        $vraagId = intval($logEntry->vraagId);
        $tijdEerstePoging = intval($logEntry->tijdEerstePoging);
        $eerstePogingGoed = intval($logEntry->eerstePogingGoed);
        $tijdTweedePoging = intval($logEntry->tijdTweedePoging);
        $tweedePogingGoed = intval($logEntry->tweedePogingGoed);
        
        
        $query = "INSERT INTO gamestats "
                . "VALUES("
                . "$gameId, $playerId, $vraagId, "
                . "$tijdEerstePoging, $eerstePogingGoed,"
                . "$tijdTweedePoging, $tweedePogingGoed"
                . ");";
        
        mysql_query($query);
        
        echo "\n========== LOG =============\n";
        echo "spel: $gameId\n";
        echo "speler: $playerId\n";
        echo "vraag: $vraagId\n";
        echo "tijd eerste poging: $tijdEerstePoging\n";
        echo "eerste keer goed: $eerstePogingGoed\n";
        echo "tijd tweede poging: $tijdTweedePoging\n";
        echo "tweede keer goed: $tweedePogingGoed\n";
        echo "========== LOG =============\n\n";
    }
}
