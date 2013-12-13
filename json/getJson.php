<?php

if (!isset($_GET['spelerId']) && !isset($_GET['gameId'])) {
    exit();
}
if (isset($_GET['spelerId'])) {
    $spelerId = $_GET['spelerId'];
    include '../bestanden/config.php';
    $spelerArray = array();
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
  WHERE s.id = " . $spelerId . "
  ;";

    $result = mysql_query($query) or die(mysql_error());
    while ($waardes = mysql_fetch_array($result)) {
        $spelerArray[$waardes['playerId']] = array();
        $spelerArray[$waardes['playerId']]['naam'] = $waardes['playerNaam'];
        $spelerArray[$waardes['playerId']]['tussenvoegsel'] = $waardes['playerTussenvoegsel'];
        $spelerArray[$waardes['playerId']]['achternaam'] = $waardes['playerAchternaam'];
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
    gs.tijdTweedePoging , tijdTweedePoging,
    g.datum as gameDatum
    FROM gamestats gs , game g
    WHERE gs.spelerId = " . $waardes['playerId'] . "
    AND g.id = gs.gameId
    ;";
        $result2 = mysql_query($query2) or die(mysql_error());
        while ($waardes2 = mysql_fetch_array($result2)) {
            if (!key_exists($waardes2['gameId'], $spelerArray[$waardes['playerId']]['games'])) {
                $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']] = array();
            }
            $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['datum'] = $waardes2['gameDatum'];
            if (!key_exists('vragen', $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']])) {
                $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'] = array();
            }
            if (!key_exists($waardes2['vraagId'], $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'])) {
                $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'][$waardes2['vraagId']] = array();
            }
            $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'][$waardes2['vraagId']]['tijdEerstePoging'] = $waardes2['tijdEerstePoging'];
            $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'][$waardes2['vraagId']]['eerstePogingGoed'] = $waardes2['eerstePogingGoed'];
            $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'][$waardes2['vraagId']]['tijdTweedePoging'] = $waardes2['tijdTweedePoging'];
            $spelerArray[$waardes['playerId']]['games'][$waardes2['gameId']]['vragen'][$waardes2['vraagId']]['tweedePogingGoed'] = $waardes2['tweedePogingGoed'];
        }
    }
    echo (json_encode($spelerArray));
} else if (isset($_GET['gameId'])) {
    //**************************************************************************************************
    $gameId = $_GET['gameId'];
    include '../bestanden/config.php';
    $gameArray = array();
    $query = "
  SELECT 
  gameId as gameId,
  naam as playerNaam,
  tussenvoegsel as playerTussnvoegsel,
  achternaam as playerAchternaam
  FROM speler s , gamestats gs
  WHERE gs.gameId = " . $gameId . "
  AND gs.spelerId = s.id
  GROUP BY s.id
  ;";

    $result = mysql_query($query) or die(mysql_error());
    print_r($result);
    while ($waardes = mysql_fetch_array($result)) {
        echo "<pre>";
        print_r($waardes);
        echo "</pre>";
    }
   
}
?>


