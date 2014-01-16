<?php
include 'bestanden/config.php';
$user = $_POST['username'];
$pass = hash('sha512' , $_POST['password']);
$query = "SELECT * FROM speler 
          WHERE login = '$user' 
          AND password = '$pass';";
$name = "notSet";
$result = mysql_query($query) or die(mysql_error());
while ($waardes = mysql_fetch_array($result)) {
    $name = $waardes['naam'];
}
echo $name;
?>
