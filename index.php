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
        <link href="css/style.css" rel="stylesheet" type="text/css">
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
                console.log("Connection established");
            };

            //In deze functie staat de code die uitgevoerd wordt wanneer er een bericht vanuit de websocket ontvangen wordt
            websocket.onmessage = function(e) {
                //e is het bericht dat binnenkomt
                
                var commandArr = e.data.toString().split("_");
                console.log(commandArr[0]);
                if (commandArr[0] == "startTimer")
                {
                    startTimer(parseInt(commandArr[1]));
                }
                //Meer dingen ......
            };


            //verstuur test bericht uit textveld
            function sendMessage()
            {
                websocket.send($("#tekst").val());
            }



            //========================================= Einde Websockets code ===========================================

            var time;
            var timerFunction;
            var timerStart = false;


            function startTimer(length) {
                if (timerStart == false)
                {
                    timerStart = true;
                    time = length + 1;
                    timerFunction = setInterval(function() {
                        updateTimer();
                    }, 1000);
                }
            }

            function updateTimer() {
                if (time > 0) {
                    time--;
                    $('#timer').text(time);
                }
                else if (timerStart)
                {
                    clearInterval(timerFunction);
                    timerFunction = null;
                    alert('BOEM!!!');
                    timerStart = false;
                    
                    
                    stelVraag("Hoeveel is 2 + 3 ?");
                }
            }

            function start() {
                if ($("#tekst").val() == "")
                {
                    alert('Naam is niet ingevuld');
                } else {
                    websocket.send("start_" + $("#tekst").val());
                    $("#tekst").hide();
                    $("#button1").hide();
                }
            }



            function stelVraag(vraag)
            {
                $('#vraag').text(vraag);
            }


        </script>
    </head>
    <body>
        <input type="text" id="tekst">
        <button onclick="start();" id="button1">start</button>
        <div id="timer"></div>
        <div id="vraag"></div>
    </body>
</html>