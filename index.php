<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta name="viewport" content="user-scalable=1.0,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <title>Quora</title>
        <link href="css/input.css" rel="stylesheet" type="text/css" />
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="format-detection" content="telephone=no">
        <script src="js/jquery.js"></script>
        <script src="js/input.js"></script>
        <!-- non-retina iPhone pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-57x57.PNG" sizes="57x57">
        <!-- non-retina iPhone iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-60x60.png" sizes="60x60">
        <!-- non-retina iPad pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-72x72.PNG" sizes="72x72">
        <!-- non-retina iPad iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-76x76.PNG" sizes="76x76">
        <!-- retina iPhone pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-114x114.PNG" sizes="114x114">
        <!-- retina iPhone iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-120x120.PNG" sizes="120x120">
        <!-- retina iPad pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-144x144.PNG" sizes="144x144">
        <!-- retina iPad iOS 7 -->
        <link rel="apple-touch-icon" href="images/apple-touch-icon-152x152.png" sizes="152x152">
        <?php
        $exec = exec("hostname"); //the "hostname" is a valid command in both windows and linux
        $hostname = trim($exec); //remove any spaces before and after
        $ip = gethostbyname($hostname); //resolves the hostname using local hosts resolver or DNS

        include "bestanden/config.php";
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
            var imgUrl;
            var antwoord1;
            var antwoord2;
            var antwoord3;
            var antwoord4;
            var vraagOpnieuw = false;
            var antwoord;
            var naam;
            var lamp;

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
                        clearQuestion();
                        showQuestion();
                        if (type === "enkel") {
                            hideMultiple();
                            showEnkel();
                        }
                        else if (type === "multiple") {
                            hideEnkel();
                            showMultiple();
                        }
                        startTimer(timeForQ);
                        vraagGesteld = true;
                        websocket.send("questionStart_");
                    }
                }
            }


            //=================================== clearQuestion

            function clearQuestion() {
                $("#vraag").html('');
                $("#antwoord").val('');
            }




            //=================================== vraag laten zien
            function showQuestion() {
                $(".button").show();
                if (imgUrl !== "") {
                    $("#vraagPlaatje").attr("src", imgUrl);
                    $("#vraagPlaatje").show();
                } else {
                    $("#vraagPlaatje").hide();
                }
                $("#sketch").hide();
                if (type.toLowerCase() === "enkel") {
                    $("#container").attr("class", "reken");
                } else if (type.toLowerCase() === "multiple") {
                    $("#container").attr("class", "multi");
                }
                $("#vraag").append(vraag);
                tekstResize();
            }



            //=================================== speler invoerveld functies
            function showPlayers() {
                $("#players").attr('display', 'block');
                $("#players").show();
            }

            function hidePlayers() {
                $("#players").attr('display', 'none');
                $("#players").hide();
                $(".lobby").hide();
            }

            //=================================== container functies
            function showContainer() {
                $("#container").attr('display', 'block');
                $("#container").show();
            }

            function hideContainer() {
                $("#container").attr('display', 'none');
                $("#container").hide();
            }

            //=================================== Multiple functies

            function showMultiple() {
//                $('.multipleLabel').contents().filter(function() {
//                    return this.nodeType === 3;
//                }).remove();

                $("input:radio[name='antwoordMult']").each(function(i) {
                    this.checked = false;
                });

                $(".multipleValue").text("");
                $("#multiple").attr('display', 'block');
                $("#multiple").show();
                $("#antwoord0Value").append(antwoord1);
                $("#antwoord1Value").append(antwoord2);
                $("#antwoord2Value").append(antwoord3);
                $("#antwoord3Value").append(antwoord4);
                $(".radio").removeAttr('disabled');
            }

            function disableMultiple() {
                $(".radio").attr('disabled', 'disabled');
            }

            function hideMultiple() {
                $(".multipleLabel").attr('display', 'none');
                $("#multiple").hide();
            }

            //=================================== enkele vraag functies

            function showEnkel() {

                $("#antwoord").removeAttr('disabled');
                $("#antwoord").attr('display', 'block');
                $("#antwoord").show();
            }

            function disableEnkel() {
                $("#antwoord").attr('disabled', 'disabled');
            }

            function hideEnkel() {
                $("#antwoord").attr('display', 'none');
                $("#antwoord").hide();
            }

            //=================================== speler is joined de lobby

            function playerJoined(player) {
                console.log(player + " doet mee");
                $("#players").append("<br/>" + player + " doet mee!");
            }

            //=================================== je naam opsturen en naar de server sturen dat je wilt starten

            function start(naam) {
                //$("#tekst").val();
                websocket.send("start_" + naam);
                $("#tekst").hide();
                $("#button1").hide();
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
                console.log("vraag opnieuw = " + vraagOpnieuw);
                trueOrFalse = $.trim(trueOrFalse.toString());
                if (trueOrFalse === "true" || vraagOpnieuw === true)
                {
                    canvasReset(); // sketchpad leegmaken
                    console.log("nieuw vraag opvragen");
                    vraagOpnieuw = false;
                    websocket.send("newquestion_");
                } else if (trueOrFalse === "false" && vraagOpnieuw === false)
                {
                    console.log("opnieuw proberen");
                    vraagOpnieuw = true;
                    websocket.send("tryagain_");
                }
            }

            //==================================== vraag / antwoord / type etc opslaan

            function setQuestion(json) {
                var obj = JSON.parse(json);
                vak = obj['subject'];
                type = obj['type'];
                vraag = obj['questionText'];
                imgUrl = obj['image'].replace('\/', '/');
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
                $(".button").hide();
                if (time > 10) {
                    websocket.send("setBrightness_" + 10);
                }
                time = 0;
            }

            //=================================== antwoord opslaan

            function saveAnswer() {
                //wanneer het een enkele vraag is
                if (type === "enkel") {
                    disableEnkel();
                    antwoord = $("#antwoord").val();
                }
                //wanneer het een multiple choice vraag is 
                else if (type === "multiple") {
                    disableMultiple();
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

            //=================================== touchevent voor de submit
            $(".submitAnswer").on("touchend", function() {
                timerToZero();
            });

            $(".submitAnswer").on("touchmove", function() {
                timerToZero();
            });



            function playerJoin() {
                lamp = $("#lampSelect").val();
                naam = $("#player").val();
                start(naam);
                $("#player").attr("disabled","disabled");
                $("#lampSelect").attr("disabled","disabled");
                
            }

        </script>
    </head>
    <body>
        <div id="sketch">
            <div id="sketchbar">
                <div id="arrow_down">
                    <span></span>
                </div>
                <div id="thrash">
                    <span></span>
                </div>
            </div>
            <canvas id="sketchpad" width="1024" height="520"></canvas>
        </div>
        <div class="lobby">
            <select id="lampSelect">
                <?php
                $query = "SELECT * FROM lamp WHERE free != 0 ;";
                $result = mysql_query($query) or die(mysql_error());
                while ($waardes = mysql_fetch_array($result)) {
                    echo "<option value='".$waardes['id']."'>lamp ".$waardes['id'] . "</option>";
                }
                ?>
            </select>
            <input type="text" id="player">
            <button onclick="playerJoin();" id="button1">start</button>
            <div id="players"></div>
        </div>

        <div class="reken" id="container" style="display:none;">
            <div class="top">
                <div class="upperbar"></div>
                <p class="vraag" id="vraag"> </p>
                <div class="image">
                    <img id="vraagPlaatje" src="" alt="afbeelding"/>
                </div>
            </div>
            <div class="bottom">
                <input type="text" name="antwoord" class="antwoord number"  style="display:none;" id="antwoord" />
                <!-- type="number"  -->
                <div id="multiple"  style="display:none;">
                    <form id="multipleForm">
                        <!-- A -->
                        <label id="labelAnwoord0" class="multipleLabel">
                            <input type="radio" class="radio" name="antwoordMult" value="0" id="antwoord1">
                            <span class="radio"></span>
                            <span id="antwoord0Value" class="value multipleValue"></span>
                        </label><br/>
                        <!-- B -->
                        <label id="labelAnwoord1" class="multipleLabel">
                            <input type="radio" class="radio" name="antwoordMult" value="1" id="antwoord2">
                            <span class="radio"></span>
                            <span id="antwoord1Value" class="value multipleValue"></span>
                        </label><br/>
                        <!-- C -->
                        <label id="labelAnwoord2" class="multipleLabel">
                            <input type="radio" class="radio" name="antwoordMult" value="2" id="antwoord3">
                            <span class="radio"></span>
                            <span id="antwoord2Value" class="value multipleValue"></span>
                        </label><br/>
                        <!-- D -->
                        <label id="labelAnwoord3" class="multipleLabel">
                            <input type="radio" class="radio" name="antwoordMult" value="3" id="antwoord4">
                            <span class="radio"></span>
                            <span id="antwoord3Value" class="value multipleValue"></span>
                        </label>
                    </form>
                </div>

                <div class="button submitAnswer"></div>
                <div class="statusbalk">
                    <ul>
                        <li>
                            <span>1</span>
                        </li>
                        <li>
                            <span>2</span>
                        </li>
                        <li>
                            <span>3</span>
                        </li>
                        <li>
                            <span></span>
                        </li>
                        <li>
                            <span></span>
                        </li>
                        <li>
                            <span></span>
                        </li>
                        <li>
                            <span></span>
                        </li>
                        <li>
                            <span></span>
                        </li>
                        <li>
                            <span></span>
                        </li>
                        <li>
                            <span></span>
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
