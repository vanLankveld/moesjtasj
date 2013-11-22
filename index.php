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

            //VARS =====================


            var time;
            var timerFunction;
            var timerStart = false;
            var vraagGesteld = false;
            var timeForQ;
            var vraag;
            var vak;
            var type;
            var antwoord1;
            var antwoord2;
            var antwoord3;
            var antwoord4;
            var vraagOpnieuw = false;
            var antwoord;



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
                    timeForQ = parseInt(commandArr[1]);
                    startTimer(3);
                } else if (commandArr[0] == "answer") {
                    checkAnswer(commandArr[1]);
                } else if (commandArr[0] == "start") {
                    playerJoined(commandArr[1]);
                } else if (commandArr[0] == "question") {
                    setQuestion(commandArr[1]);
                }
                //Meer dingen ......
            };

            //========================================= Einde Websockets code ===========================================


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
                if (time === 10) {
                    websocket.send("setBrightness_" + time);
                }
                if (time > 1) {
                    time--;
                    //$('#timer').text(time);
                    console.log(time);
                }
                else if (timerStart)
                {
                    clearInterval(timerFunction);
                    timerFunction = null;
                    timerStart = false;
                    if (vraagGesteld) {
                        //timer stoppen en antwoord opslaan/versuren
                        vraagGesteld = false;
                        saveAnswer();
                        console.log('save answer');
                    } else {
                        //vraag weergeven en timer starten 
                        hidePlayers();
                        showContainer();
                        showQuestion();
                        if (type === "enkel") {
                            showEnkel();
                        }
                        else if (type === "multiple") {
                            showMultiple();
                        }
                        startTimer(timeForQ);
                        vraagGesteld = true;
                        websocket.send("questionStart_");
                    }
                }
            }


            //=================================== vraag laten zien
            function showQuestion() {
                $("#vraag").append(vraag);
            }



            //=================================== speler invoerveld functies
            function showPlayers() {
                $("#players").attr('display', 'block');
                $("#players").show();
            }

            function hidePlayers() {
                $("#players").attr('display', 'none');
                $("#players").hide();
            }

            //=================================== container functies
            function showContainer() {
                $(".container").attr('display', 'block');
                $(".container").show();
            }

            function hideContainer() {
                $(".container").attr('display', 'none');
                $(".container").hide();
            }

            //=================================== Multiple functies

            function showMultiple() {
                $("#multiple").attr('display', 'block');
                $("#multiple").show();
                $("#labelAnwoord0").append(antwoord1);
                $("#labelAnwoord1").append(antwoord2);
                $("#labelAnwoord2").append(antwoord3);
                $("#labelAnwoord3").append(antwoord4);
            }

            function disableMultiple() {
                $("#antwoord0").attr('disabled', 'disabled');
                $("#antwoord1").attr('disabled', 'disabled');
                $("#antwoord2").attr('disabled', 'disabled');
                $("#antwoord3").attr('disabled', 'disabled');
            }

            function hideMultiple() {
                $(".#antwoord0").attr('display', 'none');
                $(".#antwoord1").attr('display', 'none');
                $(".#antwoord2").attr('display', 'none');
                $(".#antwoord3").attr('display', 'none');
                $(".#antwoord0").hide();
                $(".#antwoord1").hide();
                $(".#antwoord2").hide();
                $(".#antwoord3").hide();
            }

            //=================================== enkele vraag functies

            function showEnkel() {
                $("#antwoord").attr('display', 'block');
                $("#antwoord").show();
            }

            function disableEnkel() {
                $("#antwoord").attr('disabled', 'disabled');
            }

            function hideEnkel() {
                $(".#antwoord").attr('display', 'none');
                $(".#antwoord").hide();
            }

            //=================================== speler is joined de lobby

            function playerJoined(player) {
                console.log(player + " joined");
                $("#players").append("<br/>" + player + " joined");
            }

            //=================================== je naam opsturen en naar de server sturen dat je wilt starten

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

            //=================================== vraag laten zien op het scherm

            function stelVraag(vraag)
            {
                vraagGesteld = true;
                $('#vraag').text(vraag);
                startTimer(timeForQ);
            }

            //=================================== kijken of alles goed is of dat er iets fout was

            function checkAnswer(trueOrFalse)
            {
                trueOrFalse = $.trim(trueOrFalse.toString());
                if (trueOrFalse == "true")
                {
                    vraagOpnieuw = false;
                    websocket.send("newquestion_");
                } else if (trueOrFalse === "false")
                {
                    vraagOpnieuw = true;
                    websocket.send("answer_" + antwoord);
                }
            }

            //==================================== vraag / antwoord / type etc opslaan

            function setQuestion(json) {
                var obj = JSON.parse(json);
                vak = obj['subject'];
                type = obj['type'];
                vraag = obj['questionText'];
                if (type == 'multiple') {
                    var antwoorden = obj['multipleChoiceAnswers'];
                    antwoord1 = antwoorden[0];
                    antwoord2 = antwoorden[1];
                    antwoord3 = antwoorden[2];
                    antwoord4 = antwoorden[3];
                }
            }

            //=================================== timer op 0 zetten om de vraag meteen op te sturen

            function timerToZero() {
                time = 0;
            }

            //=================================== antwoord opslaan

            function saveAnswer() {
                //wanneer het een enkele vraag is
                if (type === "enkel") {
                    antwoord = $("#antwoord").val();
                }
                //wanneer het een multiple choice vraag is 
                else if (type === "multiple") {
                    var labelNmmr = ($('input[name=antwoordMult]:checked', '#multipleForm').val());
                    antwoord = ($("#labelAnwoord" + (labelNmmr)).text());
                }
                sendAnswer();
            }

            //=================================== antwoord versturen

            function sendAnswer() {
                //antwoord opsturen
                if (antwoord === "") {
                    antwoord = "$$$$@@@@$$$$";
                }
                antwoord = $.trim(antwoord);
                console.log('antwoord dat opgestuurd wordt = ' + antwoord);
                websocket.send("answer_" + antwoord);
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
                    <form id="multipleForm">
                        <!-- A -->
                        A<label id="labelAnwoord0">
                            <input type="radio" class="radio" name="antwoordMult" value="0" id="antwoord1">
                            <span class="radio"></span>
                        </label><br/>
                        <!-- B -->
                        B<label id="labelAnwoord1">
                            <input type="radio" class="radio" name="antwoordMult" value="1" id="antwoord2">
                            <span class="radio"></span>
                        </label><br/>
                        <!-- C -->
                        C<label id="labelAnwoord2">
                            <input type="radio" class="radio" name="antwoordMult" value="2" id="antwoord3">
                            <span class="radio"></span>
                        </label><br/>
                        <!-- D -->
                        D<label id="labelAnwoord3">
                            <input type="radio" class="radio" name="antwoordMult" value="3" id="antwoord4">
                            <span class="radio"></span>
                        </label>
                    </form>
                </div>
                <button class="submitAnswer" onclick="timerToZero();">geef antwoord</button>
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
