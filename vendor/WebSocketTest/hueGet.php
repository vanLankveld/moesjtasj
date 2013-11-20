<?php
function getQuoraId() {
    $url = "http://192.168.1.102/api/newdeveloper/lights/";

    $json = file_get_contents($url);

    $nameArray = (json_decode($json, true));
    foreach ($nameArray as $key => $value) {
        if ($value['name'] == 'quora') {
            return $key;
        }
    }
}
?>