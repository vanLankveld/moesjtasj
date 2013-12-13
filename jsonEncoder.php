<html>
    <head>
        <link class="include" rel="stylesheet" type="text/css" href="./js/graph/jquery.jqplot.min.css" />
        <script src="./js/jquery.js"></script>
        <script src="./js/graph/jquery.jqplot.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.canvasTextRenderer.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.canvasAxisLabelRenderer.min.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.categoryAxisRenderer.js"></script>
        <script class="include" type="text/javascript" src="./js/graph/plugins/jqplot.canvasAxisTickRenderer.js"></script>



        <script>
            $(document).ready(function() {
                var id = 1;
                $.getJSON("json/getJson.php?spelerId=" + id, function(data) {
                    console.log(data[id]["achternaam"]);
                    //console.log(data[id]);
                });


                var guus = [["vraag1", 2], ["vraag2", 4], ["vraag3", 8]];
                var mike = [["vraag1", 5], ["vraag2", 2], ["vraag3", 9]];

                var plot1 = $.jqplot('chart1', [guus, mike], {
                    title: 'Tijd in seconden per vraag',
                    axesDefaults: {
                        tickRenderer: $.jqplot.CanvasAxisTickRenderer,
                        tickOptions: {
                            angle: 0,
                            fontSize: '10pt'
                        }
                    },
                    axes: {
                        xaxis: {
                            renderer: $.jqplot.CategoryAxisRenderer
                        }
                    },
                    legend: {
                        show: true,
                        location: "e"
                    },
                    series: [
                        {label: "guus"},
                        {label: "mike"}
                    ]
                });
            });


        </script>
    </head>
    <body>
        <div id="chart1" style="width: 500px;"></div>
    </body>
</html>