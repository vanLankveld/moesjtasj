<?php
if (isset($_POST['filter'])) {
    $url = file_get_contents("json/stats.json");
    $arr = json_decode($url, true);


    if ($_POST['filter'] == "leerlingGoedFout") {
        foreach ($arr as $speler) {

            $aantalGoedEersteBeurt = 0;
            $aantalFoutEersteBeurt = 0;
            $aantalGoedTweedeBeurt = 0;
            $aantalFoutTweedeBeurt = 0;
            $totaalVragen = 0;
            $naam = "";


            foreach ($speler as $spelerInfoKey => $spelerInfoValue) {
                if ($spelerInfoKey == "games") {
                    foreach ($spelerInfoValue as $gameKey => $gameInfo) {
                        foreach ($gameInfo as $gameKeyInfo => $gameValueInfo) {
                            if ($gameKeyInfo == "vragen") {
                                foreach ($gameValueInfo as $vraagKey => $vraagValue) {
                                    $totaalVragen++;
                                    foreach ($vraagValue as $vraagDetailsKey => $vraagDetailsValue) {
                                        if ($vraagDetailsKey == "eerstePogingGoed") {
                                            if ($vraagDetailsValue == true) {
                                                $aantalGoedEersteBeurt++;
                                            } else {
                                                $aantalFoutEersteBeurt++;
                                            }
                                        } else if ($vraagDetailsKey == "tweedePogingGoed") {
                                            if ($vraagDetailsValue == true) {
                                                $aantalGoedTweedeBeurt++;
                                            } else {
                                                $aantalFoutTweedeBeurt++;
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                } else {
                    if ($spelerInfoKey == "naam") {
                        $naam = $spelerInfoValue;
                    }
                   // echo $spelerInfoKey . " - " . $spelerInfoValue . "<br/>";
                }
            }
            //   echo "aantal goed eerste beurt " . $aantalGoedEersteBeurt . "<br/>";
            //   echo "aantal fout eerste beurt " . $aantalFoutEersteBeurt . "<br/>";
            //   echo "aantal goed tweede beurt " . $aantalGoedTweedeBeurt . "<br/>";
            //   echo "aantal fout tweede beurt " . $aantalFoutTweedeBeurt . "<br/>";
            echo "naam: ".$naam."<br/>";
            echo "totaal aantal goede antwoorden: " . ($aantalGoedEersteBeurt + $aantalGoedTweedeBeurt) . "<br/>";
            echo "totaal aantal foute antwoorden: " . ($aantalFoutEersteBeurt + $aantalFoutTweedeBeurt) . "<br/>";
            echo "totaal aantal vragen: ".$totaalVragen."<br/>";
            echo "<br/><br/>";
        }
    }

    /*
      foreach ($arr as $speler) {
      foreach ($speler as $spelerInfoKey => $spelerInfoValue) {
      if ($spelerInfoKey == "games") {
      foreach ($spelerInfoValue as $gameKey => $gameInfo) {
      foreach ($gameInfo as $gameKeyInfo => $gameValueInfo) {
      if ($gameKeyInfo == "vragen") {
      foreach ($gameValueInfo as $vraagKey => $vraagValue) {
      foreach ($vraagValue as $vraagDetailsKey => $vraagDetailsValue) {
      echo $vraagDetailsKey . " - " . $vraagDetailsValue . "<br/>";
      }
      }
      } else {
      echo $gameKeyInfo . " - " . $gameValueInfo . "<br/>";
      }
      }
      }
      } else {
      echo $spelerInfoKey . " - " . $spelerInfoValue;
      }
      }
      }
     */
} else {
    ?>
    <form action="#" method="POST">
        <select name="filter">
            <option value="leerlingGoedFout">Leerling goed/fout</option>
        </select>
        <input type="submit" value="verstuur">
    </form>

    <?php
}
?>

