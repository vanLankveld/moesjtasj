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
                    //vraag stellen (op het scherm zetten) als er nog geen vraag gesteld is
                    if (vraagGesteld == false)
                    {
                        //container op het scherm tonen en het spelers join hokje weghalen
                        $(".container").attr('display', 'block');
                        $(".container").show();
                        $("#players").hide();
                        //vraag stellen
                        stelVraag(vraag);
                        //als het type multiple choice is de radio buttons laten zien
                        if (type === 'multiple') {
                            $("#multiple").show();
                            $("#labelAnwoord0").append(antwoord1);
                            $("#labelAnwoord1").append(antwoord2);
                            $("#labelAnwoord2").append(antwoord3);
                            $("#labelAnwoord3").append(antwoord4);
                        //als het 
                        } else if (type === 'enkel') {
                            $("#antwoord").show();
                        }
                    //als er al wel een vraag gesteld is
                    } else
                    //antwoord checken op multipelchoice of enkelen vraag
                    if (vraagGesteld == true)
                    {
                        var antwoord;
                        //wanneer het een enkele vraag is
                        if (type === "enkel") {
                            antwoord = $("#antwoord").val();
                            $("#antwoord").attr('disabled', 'disabled');
                        }
                        //wanneer het een multiple choice vraag is 
                        else if (type === "multiple") {
                            var labelNmmr = ($('input[name=antwoordMult]:checked', '#multipleForm').val());
                            antwoord = ($("#labelAnwoord" + (labelNmmr)).text());
                            $("#antwoord1").attr('disabled', 'disabled');
                            $("#antwoord2").attr('disabled', 'disabled');
                            $("#antwoord3").attr('disabled', 'disabled');
                            $("#antwoord4").attr('disabled', 'disabled');
                        }
                        //antwoord opsturen
                        if (antwoord === "") {
                            antwoord = "$$$$@@@@$$$$";
                        }
                        antwoord = $.trim(antwoord);
                        console.log('antwoord dat opgestuurd wordt = ' + antwoord);
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
                    vraagOpnieuw = true;
                    console.log('fout');
                }
            }

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

            function timerToZero() {
                time = 0;
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



                        <!--
                        
                                                A <input type="radio" class="radio" name="antwoordMult" value="0" id="antwoord1"><label id="labelAnwoord0" for="antwoord0"></label><br/>
                                                B <input type="radio" class="radio" name="antwoordMult" value="1" id="antwoord2"><label id="labelAnwoord1" for="antwoord1"></label><br/>
                                                C <input type="radio" class="radio" name="antwoordMult" value="2" id="antwoord3"><label id="labelAnwoord2" for="antwoord2"></label><br/>
                                                D <input type="radio" class="radio" name="antwoordMult" value="3" id="antwoord4"><label id="labelAnwoord3" for="antwoord3"></label><br/>
                        
                        -->
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
