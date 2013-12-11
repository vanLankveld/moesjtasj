<?php

include 'bestanden/config.php';


$spelerArray = array();

include "bestanden/config.php";
$query = "
  SELECT 
  s.id as playerId,
  s.naam as playerNaam,
  s.tussenvoegsel as playerTussenvoegsel,
  s.achternaam as playerAchternaam,
  s.groep as playerGroep,
  s.geboorteDatum as playerGeboorteDatum,
  s.geslacht as playerGeslacht,
  s.login as playerLogin
  FROM speler s 
  ;";

$result = mysql_query($query) or die(mysql_error());
while ($waardes = mysql_fetch_array($result)) {
    $spelerArray[$waardes['playerId']] = array();
    $spelerArray[$waardes['playerId']]['naam'] = $waardes['playerNaam'];
    $spelerArray[$waardes['playerId']]['tussenvoegsel'] = $waardes['playerTussenvoegsel'];
    $spelerArray[$waardes['playerId']]['achernaam'] = $waardes['playerAchternaam'];
    $spelerArray[$waardes['playerId']]['groep'] = $waardes['playerGroep'];
    $spelerArray[$waardes['playerId']]['geboorteDatum'] = $waardes['playerGeboorteDatum'];
    $spelerArray[$waardes['playerId']]['geslacht'] = $waardes['playerGeslacht'];
    $spelerArray[$waardes['playerId']]['login'] = $waardes['playerLogin'];
    $spelerArray[$waardes['playerId']]['games'] = array();
    $query2 = "
    SELECT 
    gs.gameId  as gameId,
    gs.vraagId as vraagId,
    gs.spelerId as spelerId,
    gs.eerstePogingGoed , eerstePogingGoed,
    gs.tweedePogingGoed , tweedePogingGoed,
    gs.tijdEerstePoging , tijdEerstePoging,
    gs.tijdTweedePoging , tijdTweedePoging
    FROM gamestats gs , game g

    ;";
    $result2 = mysql_query($query2) or die(mysql_error());
    while ($waardes2 = mysql_fetch_array($result2)) {
         $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']] = array();
        //$spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['datum'] = $waardes2['gameDatum'];
        if (!key_exists('vragen', $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']])) {
            $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'] = array();
        }
        array_push($spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'], $waardes2['vraagId']);
    }
}
echo "<pre>";
print_r($spelerArray);
echo "</pre>";
?>


