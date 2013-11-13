<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <script src="js/jquery.js"></script>
        <?php
        $exec = exec("hostname"); //the "hostname" is a valid command in both windows and linux
        $hostname = trim($exec); //remove any spaces before and after
        $ip = gethostbyname($hostname); //resolves the hostname using local hosts resolver or DNS
        ?>

        <script type="text/javascript">

            //============================================= Websockets code =============================================

            //Openen van de websocket, het adres van de websocket is in dit geval ws://localhost:8080 
            var websocket = new WebSocket("ws://<?php echo $ip; ?>:8080");

            //In deze functie staat code die uitgevoerd wordt wanneer er een verbinding is met de websocket
            websocket.onopen = function(e) {
                $('#display').text("Connection established");
            };

            //In deze functie staat de code die uitgevoerd wordt wanneer er een bericht vanuit de websocket ontvangen wordt
            websocket.onmessage = function(e) {
                //e is het bericht dat binnenkomt
                $("#display").append("<br/>" + e.data.toString());
                $('#tekst').val("");
                //Meer dingen ......
            };

            //Verstuur een bericht naar de websocket
            function sendMessage(message)
            {
                websocket.send(message);
            }





            //verstuur test bericht uit textveld
            function sendTestMessage()
            {

                websocket.send($("#tekst").val());
            }



            //========================================= Einde Websockets code ===========================================

            var time;
            var timerFunction;

            function startTimer(length) {
                time = 0;
                timerFunction = setInterval(function() {
                    updateTimer(length);
                }, 1000);
            }

            function updateTimer(length) {
                if (time < length) {
                    time++;
                    $('#display').text(time);
                }
                else
                {
                    clearInterval(timerFunction);
                    timerFunction = null;
                }
            }
        </script>
    </head>
    <body>
        <input type="text" id="tekst" ></input>
        <button onclick="sendTestMessage();">send test message</button>
        <span id="display"></span><br/><br/>

    </body>
</html>