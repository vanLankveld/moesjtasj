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
        <link href="css/style.css" rel="stylesheet" type="text/css" />
        <!-- non-retina iPhone pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-57x57.png" sizes="57x57">
        <!-- non-retina iPhone iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-60x60.png" sizes="60x60">
        <!-- non-retina iPad pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-72x72.png" sizes="72x72">
        <!-- non-retina iPad iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-72x72.png" sizes="76x76">
        <!-- retina iPhone pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-114x114.png" sizes="114x114">
        <!-- retina iPhone iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-120x120.png" sizes="120x120">
        <!-- retina iPad pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-144x144.png" sizes="144x144">
        <!-- retina iPad iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-152x152.png" sizes="152x152">
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
                console.log(e.data.toString());
                //e is het bericht dat binnenkomt
                var commandArr = e.data.toString().split("_");
                if (commandArr[0] == "startTimer") {
                    startTimer(parseInt(commandArr[1]));
                } else if (commandArr[0] == "answer") {
                    checkAnswer(commandArr[1]);
                } else if (commandArr[0] == "start") {
                    playerJoined(commandArr[1]);
                } else if (commandArr[0] == "question") {
                    setQuestion(commandArr[1]);
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
            var timeForQ = 20;
            var vraag;
            var vak;
            var type;

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
                        $(".container").attr('display', 'block');
                        $(".container").show();
                        stelVraag(vraag);
                        $("#players").hide();
                        if (type == 'multiple') {
                         //   $("#multiple").show();
                        } else if (type == 'single') {
                            $("#antwoord").show();
                        }
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
                if (type == 'multiple') {

                } else if (type == 'single') {

                }
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

            function setQuestion(json) {
                var obj = JSON.parse(json);
                vak = obj['subject'];
                type = obj['type'];
                vraag = obj['questionText'];
            }

        </script>
    </head>
    <body>
        <input type="text" id="tekst">
        <button onclick="start();" id="button1">start</button>
        <div id="players"></div>
        <div id="timer"></div>
        <div class="container"  style="display:none;">
            <div class="tweederde">
                <p class="vraag" id="vraag"> </p>
                <img id="img" alt=""/>
            </div>
            <div class="eenderde">
                <input type="text" name="antwoord" class="antwoord" id="antwoord"  style="display:none;" />
                <div id="multiple"  style="display:none;">
                    <input type="radio" name="group1" value=""><br/>
                    <input type="radio" name="group1" value=""><br/>
                    <input type="radio" name="group1" value=""><br/>
                    <input type="radio" name="group1" value=""><br/>
                </div>

                <div class="statusbalk">
                    <ul>
                        <li>
                            <span class="active">1</span>
                        </li>
                        <li>
                            <span>2</span>
                        </li>
                        <li>
                            <span>3</span>
                        </li>
                        <li>
                            <span>4</span>
                        </li>
                        <li>
                            <span>5</span>
                        </li>
                        <li>
                            <span>6</span>
                        </li>
                        <li>
                            <span>7</span>
                        </li>
                        <li>
                            <span>8</span>
                        </li>
                        <li>
                            <span>9</span>
                        </li>
                        <li>
                            <span>10</span>
                        </li>
                    </ul>
                    <div class="potlood">
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
