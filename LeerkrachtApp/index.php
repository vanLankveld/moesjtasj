<?php
/////////// DB CONNECT //////////////
// db geg
$con = mysqli_connect('localhost', 'root', '', 'quora');

// Check connection
if (mysqli_connect_errno($con)) {
    echo "Failed to connect to MySQL: " . mysqli_connect_error();
}
/////////// OPHALEN VRAGEN //////////////
//select db vraag
$result = mysqli_query($con, "SELECT * FROM vraag");

//db vragen naar array
$vragen = array();
while ($row = mysqli_fetch_array($result)) {
    $vragen[$row['id']] = array('vraag' => $row['vraag'], 'groep' => $row['groep'], 'moeilijkheidsGraad' => $row['moeilijkheidsGraad'], 'soort' => $row['soort'], 'imgUrl' => $row['imgUrl'], 'type' => $row['type'], 'antwoord1' => $row['antwoord1'], 'antwoord2' => $row['antwoord2'], 'antwoord3' => $row['antwoord3'], 'antwoord4' => $row['antwoord4'], 'juisteAntwoord' => $row['juisteAntwoord']);
}


/////////// TOEVOEGEN VRAGEN //////////////
// vragen sturen
if (isset($_POST['submit'])) {
    $imgUrl = '';
    // upload image	
    if (isset($_FILES["imgUrl"]["name"]) && $_FILES["imgUrl"]["name"] != "" && $_FILES["imgUrl"]["name"] != NULL) {
        $allowedExts = array("gif", "jpeg", "jpg", "png");
        $temp = explode(".", $_FILES["imgUrl"]["name"]);
        $extension = end($temp);
        if ((($_FILES["imgUrl"]["type"] == "image/gif") || ($_FILES["imgUrl"]["type"] == "image/jpeg") || ($_FILES["imgUrl"]["type"] == "image/jpg") || ($_FILES["imgUrl"]["type"] == "image/pjpeg") || ($_FILES["imgUrl"]["type"] == "image/x-png") || ($_FILES["imgUrl"]["type"] == "image/png")) && ($_FILES["imgUrl"]["size"] < 20000000000000) && in_array($extension, $allowedExts)) {
            if ($_FILES["imgUrl"]["error"] > 0) {
                echo "Return Code: " . $_FILES["imgUrl"]["error"] . "<br>";
            } else {
                /* ////////////////// image info
                  echo "Upload: " . $_FILES["imgUrl"]["name"] . "<br>";
                  echo "Type: " . $_FILES["imgUrl"]["type"] . "<br>";
                  echo "Size: " . ($_FILES["imgUrl"]["size"] / 1024) . " kB<br>";
                  echo "Temp imgUrl: " . $_FILES["imgUrl"]["tmp_name"] . "<br>";
                 */

                $path_parts = pathinfo($_FILES["imgUrl"]["name"]);
                $imgUrl = $path_parts['filename'] . '-' . time() . '.' . $path_parts['extension'];

                if (file_exists("../images/questionImg/" . $imgUrl)) {
                    echo $imgUrl . " already exists. ";
                } else {
                    move_uploaded_file($_FILES["imgUrl"]["tmp_name"], "../images/questionImg/" . $imgUrl);

                    $imgUrl = "images/questionImg/" . $imgUrl;
                    echo "Plaatje geupload: " . $imgUrl;
                }
            }
        } else {
            echo "Ongeldig plaatje";
        }
    };


    // add to db
    $_POST["antwoord1"] = str_replace("<p>", "", $_POST["antwoord1"]);
    $_POST["antwoord1"] = str_replace("</p>", "", $_POST["antwoord1"]);
    $_POST["antwoord2"] = str_replace("<p>", "", $_POST["antwoord2"]);
    $_POST["antwoord2"] = str_replace("</p>", "", $_POST["antwoord2"]);
    $_POST["antwoord3"] = str_replace("<p>", "", $_POST["antwoord3"]);
    $_POST["antwoord3"] = str_replace("</p>", "", $_POST["antwoord3"]);
    $_POST["antwoord4"] = str_replace("<p>", "", $_POST["antwoord4"]);
    $_POST["antwoord4"] = str_replace("</p>", "", $_POST["antwoord4"]);

    $sql = "INSERT INTO vraag (vraag, groep, moeilijkheidsGraad, soort, imgUrl, type, antwoord1, antwoord2, antwoord3, antwoord4, juisteAntwoord )
	VALUES
	('$_POST[vraag]','$_POST[groep]','$_POST[moeilijkheidsGraad]','$_POST[soort]','" . $imgUrl . "','$_POST[type]','$_POST[antwoord1]','$_POST[antwoord2]','$_POST[antwoord3]','$_POST[antwoord4]','$_POST[juisteAntwoord]')";


    if (!mysqli_query($con, $sql)) {
        die('Error: ' . mysqli_error($con));
    }

    // refresh page
    /*
      $page = $_SERVER['PHP_SELF'];
      $sec = "0";
      header("Refresh: $sec; url=$page");
     */
}

mysqli_close($con);
?>
<!DOCTYPE html>
<html>
    <head>
        <style>
            input, select{
                width:200px;
            }
            .meerdere{
                display:none;
            }
        </style>

    </head>
    <body>
        <div id="this"></div>
        <form method="post" action="index.php" enctype="multipart/form-data">
            Vraag: <input type="text" name="vraag"><br>

            Groep: <input type="number" name="groep" min="3" max="8"><br>

            <span title='moeilijkheidsGraad'>Graad:</span> <input type="number" name="moeilijkheidsGraad" min="1" max="10"><br>

            Soort: <input type="text" name="soort"><br>

            Plaatje: <input type="file" name="imgUrl"><br/>

            Type: <select name='type' id='type'>
                <option value="enkel" selected="selected">enkel</option>
                <option value="multiple">multiple</option>
            </select><br>

            Antwoord1: <textarea name="antwoord1" style="width:100%"></textarea><br/>

            <div class='meerdere'>
                Antwoord2: <textarea name="antwoord2" style="width:100%"></textarea><br/>

                Antwoord3: <textarea name="antwoord3" style="width:100%"></textarea><br/>

                Antwoord4: <textarea name="antwoord4" style="width:100%"></textarea><br/>

                juisteAntwoord: <select name='juisteAntwoord' id='juisteAntwoord'>
                    <option value="0" selected="selected">Antwoord1</option>
                    <option value="1">Antwoord2</option>
                    <option value="2">Antwoord3</option>
                    <option value="3">Antwoord4</option>
                </select> <br>
            </div>
            <input name="submit" type="submit" value="Submit">
        </form>
        <p></p>
        <h2>Bestaande vragen:</h2>
        <?php
        foreach ($vragen as $index => $vraag) {
            echo '<p>';
            foreach ($vraag as $key => $value) {
                echo '<B>' . $key . '</B>' . ': ' . $value . '<br/>';
            }
            echo '</p>';
        }
        ?>
        <script src="js/jquery.js"></script>
        <script src="js/tinymce/tinymce.min.js"></script>

        <script>
            $("select#type")
                    .change(function() {
                var str = $("select#type option:selected").text();
                if (str = 'multiple') {
                    $('.meerdere').toggle();
                }
            })

            tinymce.init({
                selector: "textarea",
            });

        </script>
    </body>
</html>
