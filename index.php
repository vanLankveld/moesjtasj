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
                if (commandArr[0] == "startTimer") {
                    startTimer(parseInt(commandArr[1]));
                } else if (commandArr[0] == "answer") {
                    checkAnswer(commandArr[1]);
                } else if (commandArr[0] == "start") {
                    playerJoined(commandArr[1]);
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
            var vraagGesteld = false;
            var timeForQ = 5;


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
                    timerStart = false;

                    if (vraagGesteld == false)
                    {
                        stelVraag("Hoeveel is 2 + 3 ?");
                        $("#players").hide();
                        $("#antwoord").show();
                    } else if (vraagGesteld == true)
                    {
                        $("#antwoord").attr('disabled', 'disabled');
                        var antwoord;
                        if ($("#antwoord").val() == "") {
                            antwoord = "$$$$@@@@$$$$";
                        } else {
                            antwoord = $("#antwoord").val();
                        }
                        websocket.send("answer_" + antwoord);
                    }
                }
            }



            function playerJoined(player) {
                console.log(player + " joined");
                $("#players").append("<br/>" + player + " joined");
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
                vraagGesteld = true;
                $('#vraag').text(vraag);
                startTimer(timeForQ);
            }


            //kijken of het antwoord goed of fout is
            function checkAnswer(trueOrFalse)
            {
                trueOrFalse = $.trim(trueOrFalse.toString());
                if (trueOrFalse == "true")
                {
                    console.log('goed');
                } else if (trueOrFalse === "false")
                {
                    console.log('fout');
                }
            }




        </script>
    </head>
    <body>
        <input type="text" id="tekst">
        <button onclick="start();" id="button1">start</button>
        <div id="timer"></div>
        <div id="players"></div>
        <div id="vraag"></div>
        <div><input type="text" id="antwoord" style="display:none; width: 20px;" name="antwoord"</div>
    </body>
</html>