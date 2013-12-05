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
        <script src="js/tussenscherm.js"></script>
        <!-- non-retina iPad pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-72x72.PNG" sizes="72x72">
        <!-- non-retina iPad iOS 7 -->
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-76x76.PNG" sizes="76x76">
        <!-- retina iPad pre iOS 7 -->
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-144x144.PNG" sizes="144x144">
        <!-- retina iPad iOS 7 -->
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-152x152.png" sizes="152x152">
        <?php
        $exec = exec("hostname"); //the "hostname" is a valid command in both windows and linux
        $hostname = trim($exec); //remove any spaces before and after
        $ip = gethostbyname($hostname); //resolves the hostname using local hosts resolver or DNS

        include "bestanden/config.php";
        ?>

        <script type="text/javascript">

            //VARS =====================

            var nextButtonPressed = false;
            var loginButtonPressed = false;
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
            var correctAnswer = "";
            var currentQuestion = 1;
            var maxQuestion = 0;
            var questionLabels = ['labelQ1', 'labelQ2', 'labelQ3', 'labelQ4', 'labelQ5', 'labelQ6', 'labelQ7', 'labelQ8', 'labelQ9'];
            //=================================== touchevent voor de submit
            $(document).ready(function() {

                $(".overlay").hide();
                $(".submitAnswer").on("touchend", function() {
                    timerToZero();
                });
                $(".submitAnswer").on("touchmove", function() {
                    timerToZero();
                });
                $("#nextButton").on("touchend", function() {
                    nextTouch();
                });
                $("#nextButton").on("touchmove", function() {
                    nextTouch();
                });
                $("#button1").on("touchend", function() {
                    loginTouch();
                });
                $("#button1").on("touchmove", function() {
                    loginTouch();
                });
            });
            function loginTouch() {
                if (!loginButtonPressed) {
                    loginButtonPressed = true;
                    var user = $('#player').val();
                    var pass = $('#password').val();
                    if (playerAllow(user, pass)) {
                        playerJoin();
                    } else {
                        loginButtonPressed = false;
                    }
                }
            }

            function nextTouch() {
                if (!nextButtonPressed) {
                    nextButtonPressed = true;
                    wachtenWeergeven();
                    gotoNextQuestion();
                }
            }

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
                        $(".overlay").hide(300);
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
                console.log("vraag: " + currentQuestion);
                $("#container .button-container").show();
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
                $("#players").show();
                $("#players").append("<span>" + player + "</span>");
            }

            //=================================== je naam opsturen en naar de server sturen dat je wilt starten

            function start(naam) {
                websocket.send("start_" + naam);
                $(".title h1").html('Welkom');
                $(".loginform").hide();
                $(".button-container").hide();
            }

            //=================================== vraag laten zien op het scherm

            function stelVraag(vraag)
            {
                vraagGesteld = true;
                $('#vraag').text(vraag);
                startTimer(timeForQ);
            }


            //=================================== tussenscherm showen

            function showNext() {
                nextButtonPressed = false;
                $(".overlay").fadeIn(300);
            }



            //=================================== kijken of alles goed is of dat er iets fout was

            function checkAnswer(trueOrFalse)
            {
                console.log("vraag opnieuw = " + vraagOpnieuw);
                trueOrFalse = $.trim(trueOrFalse.toString());
                if (trueOrFalse === "true" || vraagOpnieuw === true)
                {
                    saveKlad();
                    currentQuestion++;
                    questionCounter(currentQuestion);
                    canvasReset(); // sketchpad leegmaken
                    $("#icon").css('background', 'url(../images/icons/individu.png)');
                    console.log("nieuw vraag opvragen");
                    vraagOpnieuw = false;
                    if (trueOrFalse === "false") {
                        showRightAnswer();
                    } else if (trueOrFalse === "true") {
                        emptyRightAnswer();
                    }
                    showNext();
                } else if (trueOrFalse === "false" && vraagOpnieuw === false)
                {
                    $("#icon").css('background', 'url(../images/icons/groep.png)');
                    console.log("opnieuw proberen");
                    vraagOpnieuw = true;
                    websocket.send("tryagain_");
                }
            }

            function emptyRightAnswer() {
                $("#uitleg").empty();
                wachtOpVerder();
            }

            function showRightAnswer() {
                console.log("juiste antwoord weergeven");
                $("#uitleg").html("Het juiste antwoord is:<br/>" + correctAnswer);
                uitlegWeergeven();
            }



            //==================================== vraag / antwoord / type etc opslaan
            function setQuestion(json) {
                var obj = JSON.parse(json);
                vak = obj['subject'];
                type = obj['type'];
                vraag = obj['questionText'];
                imgUrl = obj['image'].replace('\/', '/');
                maxQuestion = 10;
                questionCounter(currentQuestion);
                var correct = 0;
                if (type === 'multiple') {
                    correct = parseInt(obj['correctAnswer']);
                    var antwoorden = obj['multipleChoiceAnswers'];
                    antwoord1 = antwoorden[0];
                    antwoord2 = antwoorden[1];
                    antwoord3 = antwoorden[2];
                    antwoord4 = antwoorden[3];
                }
                else
                {
                    antwoord1 = obj['multipleChoiceAnswers'][0];
                }

                switch (correct)
                {
                    case 0:
                        correctAnswer = antwoord1;
                        break;
                    case 1:
                        correctAnswer = antwoord2;
                        break;
                    case 2:
                        correctAnswer = antwoord3;
                        break;
                    case 3:
                        correctAnswer = antwoord4;
                        break;
                }
            }

            //=================================== timer op 0 zetten om de vraag meteen op te sturen

            function timerToZero() {
                $("#container .button-container").hide();
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
            $(document).ready(function() {
                $(".submitAnswer").on("touchend", function() {
                    timerToZero();
                });
                $(".submitAnswer").on("touchmove", function() {
                    timerToZero();
                });
            });
            //=================================== touchevent voor de submit
            $(document).ready(function() {
                $(".submitAnswer").on("touchend", function() {
                    timerToZero();
                });
                $(".submitAnswer").on("touchmove", function() {
                    timerToZero();
                });
            });
            function playerJoin() {
                showLaadText()
                lamp = $("#lampSelect").val();
                naam = $("#player").val();
                start(naam);
                $("#player").attr("disabled", "disabled");
                $("#lampSelect").attr("disabled", "disabled");
            }

            function gotoNextQuestion() {
                websocket.send("newquestion_");
            }



            function questionCounter(currentQ) {
                if (currentQ <= maxQuestion) {
                    var teller = 1;
                    for (var i = -4; i <= 4; i++) {
                        if (currentQ + i > 0) {
                            if (currentQ + i > maxQuestion) {
                                $("#labelQ" + teller).empty();
                            } else {
                                $("#labelQ" + teller).text(currentQ + i);
                            }
                        }
                        teller++;
                    }
                }
            }
            function saveKlad() {
                var canvas = document.getElementById("sketchpad");
                var img = canvas.toDataURL("image/png");
                $.post("saveKlad.php", {imgUrl: img});
                console.log('image url send');
            }


            function playerAllow(user, pass) {
                if (user === "") {
                    $('#alertName').css('visibility', 'visible');
                } else {
                    $('#alertName').css('visibility', 'hidden');
                }
                if (pass === "") {
                    $('#alertPass').css('visibility', 'visible');
                } else {
                    $('#alertPass').css('visibility', 'hidden');
                }
                if (user !== "" && pass !== "") {
                    //ajax
                    return true;
                    $.ajax({
                        url: "login.php",
                        type: "POST",
                        data: {
                            username: user,
                            password: pass
                        },
                        success: function(response)
                        {
                            if (response === 'true') {
                                return true;
                            }
                            else {
                                return false;
                            }
                        }
                    });


                } else {
                    return false;
                }

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
            <div class="upperbar"></div>
            <div class="title">
                <h1>Inloggen</h1>
            </div>
            <div class="loginform">
                <select id="lampSelect">
                    <?php
                    $query = "SELECT * FROM lamp WHERE free != 0 ;";
                    $result = mysql_query($query) or die(mysql_error());
                    while ($waardes = mysql_fetch_array($result)) {
                        echo "<option value='" . $waardes['id'] . "'>lamp " . $waardes['id'] . "</option>";
                    }
                    ?>
                </select>
                <span>Voer je naam in:</span>
                <h5 id="alertName">er is geen naam ingevuld!</h5>
                <input type="text" id="player">
                <span>Wachtwoord:</span>
                <h5 id="alertPass">er is geen wachtwoord ingevuld!</h5>
                <input type="password" id="password">
            </div>
            <div id="players" style="display:none;">
                <h2>Wie spelen er mee?</h2>
            </div>
            <div class="button-container">
                <span class="button-text">Bevestigen</span>
                <div id="button1"></div>
                <span class="button-shadow"></span>
            </div>
            <div class="bottom-bar">
                <div class="bottom-logo"></div>
            </div>
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
                <div class="button-container">
                    <span class="button-text">Bevestigen</span>
                    <div class="button submitAnswer"></div>
                    <span class="button-shadow"></span>
                </div> 
                <div class="statusbalk">
                    <div class="icon">
                        <span></span>
                    </div>
                    <ul>
                        <li>
                            <span id="labelQ1"></span>
                        </li>
                        <li>
                            <span id="labelQ2"></span>
                        </li>
                        <li>
                            <span id="labelQ3"></span>
                        </li>
                        <li>
                            <span id="labelQ4"></span>
                        </li>
                        <li>
                            <span id="labelQ5" class="active"></span>
                        </li>
                        <li>
                            <span id="labelQ6"></span>
                        </li>
                        <li>
                            <span id="labelQ7"></span>
                        </li>
                        <li>
                            <span id="labelQ8"></span>
                        </li>
                        <li>
                            <span id="labelQ9"></span>
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
        <div class="overlay">
            <p id="uitleg"></p>
            <div id="vraagnummer" style="display:none;">
                <h1>Vraag <span>1</span></h1>
            </div>
            <div id="categorie" style="display:none;">
                <h2></h2>
            </div>
            <div class="button-container">
                <span class="button-text">Volgende</span>
                <div id="nextButton" class="nextButton"></div>
                <span class="button-shadow"></span>
            </div> 
            <div id="laden" class="laden" style="display:none;">
                <h3></h3>
            </div>
        </div>
    </body>
</html>
