<?php
function getQuoraId() {
    //get url voor alle lampen die verbonden zijn met de Hue
    $url = "http://192.168.1.102/api/newdeveloper/lights/";
    $json = file_get_contents($url);
    //content decoden naar json
    $nameArray = (json_decode($json, true));
    //loop om te kijken welk id bij de quora hoort
    foreach ($nameArray as $key => $value) {
        if ($value['name'] == 'quora') {
            return $key;
        }
    }
}
?>