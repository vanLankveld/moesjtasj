<html>
    <head>
        <link class="include" rel="stylesheet" type="text/css" href="./js/graph/jquery.jqplot.min.css" />
        <link href="css/graph.css" rel="stylesheet" type="text/css" />
        <script src="./js/jquery.js"></script>
        <script src="./js/graph/jquery.jqplot.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.canvasTextRenderer.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.categoryAxisRenderer.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.canvasAxisTickRenderer.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.barRenderer.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.pointLabels.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.highlighter.min.js"></script>


        <script>


            $(document).ready(function() {
                var gameId = ($(".gameId option:selected").val());
                changeGraph(gameId);
                $(".gameId").change(function() {
                    var gameId = ($(".gameId option:selected").val());
                    changeGraph(gameId);
                });
            });
            function changeGraph(gameId) {
                $(".container").empty();
                getPlayersFromGame(gameId);
            }

            function getPlayersFromGame(gameId) {
                $.getJSON("json/getJson.php?gameId=" + gameId, function(data) {
                    data.forEach(function(data) {
                        getPlayerInfo(data['playerId'], gameId);
                    });
                });
            }

            function getPlayerInfo(playerId, gameId) {
                var firstQuestionGraph = [];
                var secondQuestionGraph = [];
                var vragenArray = [];
                $.getJSON("json/getJson.php?spelerId=" + playerId, function(data) {
                    for (key in data[playerId]['games'][gameId]['vragen']) {
                        var tijdVraag1 = data[playerId]['games'][gameId]['vragen'][key]['tijdEerstePoging'];
                        var tijdVraag2 = data[playerId]['games'][gameId]['vragen'][key]['tijdTweedePoging'];
                        firstQuestionGraph.push([key, tijdVraag1]);
                        secondQuestionGraph.push([key, tijdVraag2]);
                        vragenArray[key] = data[playerId]['games'][gameId]['vragen'][key]['vraag'];
                    }
                    drawGraph(firstQuestionGraph, secondQuestionGraph, playerId, data[playerId]['naam'], vragenArray);
                });
            }

            function drawGraph(firstQuestionGraph, secondQuestionGraph, id, naam, vragenArray) {
                $(".container").append("<div id='chart" + id + "' class='chart'></div><div class='chartVraag' id='chartVraag" + id + "'></div>");
                var plot1 = $.jqplot("chart" + id, [firstQuestionGraph, secondQuestionGraph], {
                    title: 'Tijd per vraag - ' + naam,
                    seriesColors: ['#f59833', '#000'],
                    series: [
                        {
                            label: 'Eerste poging',
                            renderer: $.jqplot.BarRenderer,
                            pointLabels: {show: true, location: 'n', edgeTolerance: -15},
                            rendererOptions: {
                                smooth: true,
                                animation: {
                                    show: true
                                }
                            }
                        },
                        {
                            label: 'Tweede poging',
                            renderer: $.jqplot.BarRenderer,
                            pointLabels: {show: true, location: 'n', edgeTolerance: -15},
                            rendererOptions: {
                                smooth: true,
                                animation: {
                                    show: true
                                }
                            }
                        }
                    ],
                    legend: {
                        show: true,
                        placement: 'outsideGrid'

                    },
                    axesDefaults: {
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        tickOptions: {
                            fontSize: '10pt'
                        }
                    },
                    axes: {
                        xaxis: {
                            renderer: $.jqplot.CategoryAxisRenderer,
                            label: 'Vraag'
                        },
                        yaxis: {
                            min: 0,
                            max: 120,
                            autoscale: true,
                            tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                            labelRenderer: $.jqplot.CanvasAxisLabelRenderer,
                            tickOptions: {
                                labelPosition: 'start'
                            },
                            label: 'Tijd'
                        }
                    }, highlighter: {
                        sizeAdjust: 10,
                        tooltipLocation: 'n',
                        tooltipAxes: 'y',
                        tooltipFormatString: '<b><i><span style="color:red;">hello</span></i></b> %.2f',
                        useAxesFormatters: true
                    }
                });
                $('#chart' + id).bind('jqplotDataClick',
                        function(ev, seriesIndex, pointIndex, data) {
                            $('#chartVraag'+id).html(vragenArray[firstQuestionGraph[pointIndex][0]]);
                        }
                );
            }

        </script>
    </head>
    <body>
        <form>
            <select class='gameId'>
                <?php
                include "bestanden/config.php";
                $query = "
                      SELECT gameId 
                      FROM gamestats
                      GROUP BY gameId
                      ORDER BY gameId DESC
                      ;";
                $result = mysql_query($query) or die(mysql_error());
                while ($waardes = mysql_fetch_array($result)) {
                    echo "<option value = '" . $waardes['gameId'] . "'>game " . $waardes['gameId'] . "</option>";
                }
                ?>
            </select>
        </form>
        <div class="container"></div>
        <div id="info1"></div>
    </body>
</html>