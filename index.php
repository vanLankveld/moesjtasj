<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <title>Quora</title>
        <link href="css/input.css" rel="stylesheet" type="text/css" />
        <meta name="viewport" content="user-scalable=1.0,initial-scale=1.0,minimum-scale=1.0,maximum-scale=1.0">
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent" />
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="format-detection" content="telephone=no">
        <script src="js/jquery.js"></script>
        <script src="js/input.js"></script>
        <script src="js/tussenscherm.js"></script>
        <script src="js/showHideElements.js"></script>
        <script src="js/touchEvents.js"></script>
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-72x72.PNG" sizes="72x72">
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-76x76.PNG" sizes="76x76">
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-144x144.PNG" sizes="144x144">
        <link rel="apple-touch-icon" href="images/appicon/apple-touch-icon-152x152.png" sizes="152x152">
        <?php
        $exec = exec("hostname");
        $hostname = trim($exec);
        $ip = gethostbyname($hostname);
        include "bestanden/config.php";
        ?>
        <script type="text/javascript">
            //VARS ====================
            
            var vraagTijd;
            var nextButtonPressed = false;
            var loginButtonPressed = false;
            var time;
            var timeLeft = 0;
            var timerFunction;
            var timerStart = false;
            var vraagGesteld = false;
            var timeForQ;
            var vraag;
            var vak;
            var type;
            var soort;
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
                createTouchEvents();
            });

            function loginTouch() {
                if (!loginButtonPressed) {
                    loginButtonPressed = true;
                    console.log("Login button pressed.");
                    var user = $('#player').val();
                    var pass = $('#password').val();
                    if (!playerAllow(user, pass)) {
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
                    vraagTijd = length;
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
                var openQuestionFieldType = "text";
                if (soort.toLowerCase() === "rekenen") {
                    openQuestionFieldType = "number";
                }
                
                $("#antwoord").attr("type", openQuestionFieldType);
                
                tekstResize();
            }
            
            
            //=================================== speler is joined de lobby

            function playerJoined(player) {
                console.log(player + " doet mee");
                $("#players").show();
                $("#players").append("<span>" + player + "</span>");
            }


            //=================================== je naam opsturen en naar de server sturen dat je wilt starten

            function start(naam) {
                var lampId =  $("#lampSelect").val();
                websocket.send("start_" + naam + "_" + lampId);
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


            //==================================== vraag / antwoord / type etc opslaan
            function setQuestion(json) {
                var obj = JSON.parse(json);
                vak = obj['subject'];
                type = obj['type'];
                vraag = obj['questionText'];
                imgUrl = obj['image'].replace('\/', '/');
                maxQuestion = 10;
                soort = obj['subject'];
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
                timeLeft = vraagTijd - time;
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
                    antwoord = ($("#labelAntwoord" + (labelNmmr)).text());
                }
                sendAnswer();
            }


            //=================================== antwoord versturen

            function sendAnswer() {
                //antwoord opsturen
                var time = 0;
                if (antwoord === "") {
                    antwoord = "$$$$@@@@$$$$";
                }
                antwoord = $.trim(antwoord);
                console.log('antwoord dat opgestuurd wordt = ' + antwoord);
                websocket.send("answer_" + antwoord + "_" + timeLeft);
            }

            
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
                    console.log('ajax ophalen');
                    $.post("login.php", {username: user, password: pass}
                    , function(response)
                    {
                        response = response.trim();
                        if (response !== 'notSet') {
                            console.log("login true");
                            playerJoin();
                        }
                    }
                    );
                }
                console.log('login false');
                return false;
            }

        </script>
    </head>
    <body>
        <?php include 'userInterface.html'; ?>
    </body>
</html>
