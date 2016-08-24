<?php 
    require 'php/sentanal.php';
    include 'php/historyquery.php';
?>
<html lang="en">
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Sentiment Analysis tool for Tweets - designed by Matt Flynn.">

    <title>Twitter Sentiment Analysis - Matt Flynn &ndash; </title>
    <script src="Chart.js/Chart.js"></script>


<link rel="stylesheet" href="css/pure.css">

    <link rel="stylesheet" href="http://yui.yahooapis.com/pure//grids-responsive.css">

        <link rel="stylesheet" href="css/layouts/twitsentproject.css">


<link rel="stylesheet" href="css/font-awesome.css">



</head>
<body>


<!-- Create top menu bar -->

<div class="header">
    <div class="home-menu pure-menu pure-menu-open pure-menu-horizontal pure-menu-fixed">
        <a class="pure-menu-heading" href="">Sentiment Analysis for Twitter</a>

        <ul>
            <li class="pure-menu-selected"><a href="index.php">HOME</a></li>
            <li><a href="comparison.php">COMPARE TERMS</a></li>
            <li><a href="useranal.php">USER-ANALYSIS</a></li>
        </ul>
    </div>
</div>

<div class="splash-container">
    <div class="splash">
        <h1 class="splash-head">Sentiment Analysis for Twitter</h1>
        <p class="splash-subhead">
            <div id="specialformdiv">
                <!-- Create form for submitting keyword. This keyword will be searched for related tweets and those tweets will be analysed.-->
            <form class="pure-form" action="comparison.php#resulthead" method="post">
                <label for="Analyse">
                <input type="text" name="keyword" class="pure-input-rounded" style="height: 3em" placeholder="Search..." required>
                <input type="text" name="keywordalt"class="pure-input-rounded" style="height: 3em" placeholder="And Compare..." required>
            </div>
                <select name="amount">
                    <option value ="10" selected>10</opifton>
                    <option value ="20">20</option>
                </select>
                </label>
        </p>
        <p>
                <button class="formbutton-custom" type="submit" value="Submit"><h2>Compare</h2></button>
            </form>
        </p>
    </div>
</div>

<!-- Display fetched tweets -->
<div class="content-wrapper">
    <div class="content">
            </br><br>
    <div id="resulthead"><h2 style="display: none">testbreak</h2><br><br></div>
        <h2 class="content-head is-center">TWEET SEARCH RESULTS</h2>

        <div class="pure-g">
            <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                <h3 style="text-align: center; font-size: 135%" class="content-subhead">
                    <?php
                    if(isset($_POST['keyword'])){ 
                        $caps = strtoupper($_POST['keyword']);
                        echo $caps; } ?>
                </h3>
                <p>
                    <?php 
                        /*declare variables to be returned from function. 
                        These variables will count the results of the sentiment analysis */

                        /* throw form data into new variable to avoid undeclared variable error in php */      
                        if(isset($_POST['keyword'])){$keyphrase = $_POST['keyword']; }
                        if(isset($_POST['keywordalt'])){$keyphrasealt = $_POST['keywordalt']; }
                        if(isset($_POST['amount'])){$useramount = $_POST['amount']; }

                        /*Below we send our keyphrase and comparison and requested amount to the sentiment analysis function (sentanal). 
                        This function returns 3 variables which we put into an array using the list function. */
                        list ($count_positive, $count_neutral, $count_negative) = @sentanal($keyphrase, $useramount);
                    ?>
                <h3 style="text-align: center; font-size: 135%" class="content-subhead">
                    <?php
                    if(isset($_POST['keywordalt'])){ 
                        $caps = strtoupper($_POST['keywordalt']);
                        echo $caps; } ?>
                </h3>
                    <?php
                        list ($count_positive2, $count_neutral2, $count_negative2) = @sentanal($keyphrasealt, $useramount);

                        /*Now run mysql query to establish historical data related to keyword. 
                        The history query function returns the count of all pos, neg + neutral sentiments related to the keyword in the database.*/
                        if(isset($_POST['keyword'])){
                            list ($pos_hist, $neut_hist, $neg_hist) = historyquery($keyphrase);
                        }
                        if(isset($_POST['keywordalt'])){
                            list ($pos_hist2, $neut_hist2, $neg_hist2) = historyquery($keyphrasealt);
                        }
                        echo '<br>';   
                    ?>

                </p>
            </div>

        </div>
    </div>

    <div class="ribbon l-box-lrg pure-g">
        <div class="l-box-lrg is-center pure-u-1 pure-u-md-1-2 pure-u-lg-2-5">
        </div>
        <div class="pure-u-1 pure-u-md-1-2 pure-u-lg-3-5">

            <h2 class="content-head content-head-ribbon" style="text-align: center">Sentiment analysis results for <?php if(isset($_POST['keyword'])){ echo $_POST['keyword']; } ?> </h2>
            <!-- below we create our divs and canvas that will hold the visual charts. -->
            <p>
                 <div id="canvas-holder" style="width: 35%; margin-left: 30%; padding: 2%">
                    <canvas id="chart-area" width="17.5em" height="17.5em"/>
                    <p>
                </div>

            <h2 class="content-head content-head-ribbon" style="text-align: center">Sentiment analysis results for <?php if(isset($_POST['keywordalt'])){ echo $_POST['keywordalt']; } ?> </h2>
            <p>
                 <div id="canvas-holder" style="width: 35%; margin-left: 30%; padding: 2%">
                    <canvas id="chart-area2" width="17.5em" height="17.5em"/>
                    <p>
                </div>

            <h2 class="content-head content-head-ribbon" style="text-align: center">Total past queries for <?php if(isset($_POST['keyword'])){ echo $_POST['keyword']; } ?> </h2>
                <div style="width: 35%; margin-left: 32.5%;">
                    <p>
                    <canvas id="canvas2" height="17.5em" width="17.5em"></canvas>
                </div>

            <h2 class="content-head content-head-ribbon" style="text-align: center">Total past queries for <?php if(isset($_POST['keywordalt'])){ echo $_POST['keywordalt']; } ?> </h2>
                <div style="width: 35%; margin-left: 32.5%;">
                    <p>
                    <canvas id="canvas3" height="17.5em" width="17.5em"></canvas>
                </div>
                


        <script>

            /*Below we create javascript variable from the ones returned to us in the php functions*/
            var pos_count = <?php echo json_encode($count_positive); ?>;
            var neut_count = <?php echo json_encode($count_neutral); ?>;
            var neg_count = <?php echo json_encode($count_negative); ?>;
            var pos_count2 = <?php echo json_encode($count_positive2); ?>;
            var neut_count2 = <?php echo json_encode($count_neutral2); ?>;
            var neg_count2 = <?php echo json_encode($count_negative2); ?>;

            /* assign data for chartjs */
            var doughnutData = [ 
                {
                value: neg_count,
                color:"#F7464A",
                highlight: "#FF5A5E",
                label: "negative"
                },
                {
                value: pos_count,
                color: "#4BEB6B",
                highlight: "#7DF195",
                label: "Positive"
                },
                {
                value: neut_count,
                color: "#FDB45C",
                highlight: "#FFC870",
                label: "Neutral"
                }
                ];

            var doughnutDataALT = [
                {
                value: neg_count2,
                color:"#FF1975", 
                highlight: "#FF4791", 
                label: "negative"
                },
                {
                value: pos_count2,
                color: "#00E6B8",
                highlight: "#47EDCC",
                label: "Positive"
                },
                {
                value: neut_count2,
                color: "#FF9933",
                highlight: "#E69C53",
                label: "Neutral"
                }
                ];

            /*Access total sentiment history of keyword from database. */

            var pos_countdb = <?php echo json_encode($pos_hist); ?>;
            var neut_countdb = <?php echo json_encode($neut_hist); ?>;
            var neg_countdb = <?php echo json_encode($neg_hist); ?>;
            var pos_countdb2 = <?php echo json_encode($pos_hist2); ?>;
            var neut_countdb2 = <?php echo json_encode($neut_hist2); ?>;
            var neg_countdb2 = <?php echo json_encode($neg_hist2); ?>;
            
            /*Define barchart data */

            var barChartData = {
                labels : ["Sentiment"],
                datasets : [
                    {
                        label: "Positive",
                        fillColor : "rgba(75,235,107, 0.7)",
                        strokeColor : "rgba(207,207,166, 0.8)",
                        highlightFill: "rgba(112,217,132, 0.8)",
                        highlightStroke: "rgba(186,186,149, 0.9)",
                        data : [pos_countdb]
                    },
                    {
                        label: "Neutral",
                        fillColor : "rgba(253,180,92, 0.7)",
                        strokeColor : "rgba(207,207,166, 0.8)",
                        highlightFill: "rgba(255,200,112, 0.8)",
                        highlightStroke: "rgba(186,186,149, 0.9)",
                        data : [neut_countdb]
                    },
                    {
                        label: "Negative",
                        fillColor : "rgba(247,70,74, 0.7)",
                        strokeColor : "rgba(207,207,166, 0.8)",
                        highlightFill: "rgba(189,90,93, 0.8)",
                        highlightStroke: "rgba(186,186,149, 0.9)",
                        data : [neg_countdb]
                    }
                ]
            }; 

            var barChartDataALT = {
                labels : ["Sentiment"],
                datasets : [
                    {
                        label: "Positive",
                        fillColor : "rgba(0,230,184, 0.7)",
                        strokeColor : "rgba(207,207,166, 0.8)",
                        highlightFill: "rgba(71,237,204, 0.8)",
                        highlightStroke: "rgba(186,186,149, 0.9)",
                        data : [pos_countdb]
                    },
                    {
                        label: "Neutral",
                        fillColor : "rgba(177,126,64, 0.7)",
                        strokeColor : "rgba(207,207,166, 0.8)",
                        highlightFill: "rgba(255,200,112, 0.8)",
                        highlightStroke: "rgba(186,186,149, 0.9)",
                        data : [neut_countdb]
                    },
                    {
                        label: "Negative",
                        fillColor : "rgba(255,25,117, 0.7)",
                        strokeColor : "rgba(207,207,166, 0.8)",
                        highlightFill: "rgba(255,71,145, 0.8)",
                        highlightStroke: "rgba(186,186,149, 0.9)",
                        data : [neg_countdb]
                    }
                ]
            };
            /*below we create two charts (a donut and a bar chart) using Chartjs*/
            window.onload = function(){
                var ctx = document.getElementById("chart-area").getContext("2d");
                window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {
                    responsive : true
                });

                var ctx2 = document.getElementById("canvas2").getContext("2d");
                window.myBar = new Chart(ctx2).Bar(barChartData, {
                    responsive : true
                });
                var ctx = document.getElementById("chart-area2").getContext("2d");
                window.myDoughnut = new Chart(ctx).Doughnut(doughnutDataALT, {
                    responsive : true
                });

                var ctx2 = document.getElementById("canvas3").getContext("2d");
                window.myBar = new Chart(ctx2).Bar(barChartDataALT, {
                    responsive : true
                });
            };
        </script>
            </p>
        </div>
    </div>

        <!--This section of provides links to the tools and sources that this site is based on. 
    Also provides links to the user profile and keyword analysers. -->

    <div class="content">
        <h2 class="content-head is-center">Powered by AlchemyAPI and TwitterOAuth by Abraham Williams</h2>

        <div class="pure-g">
            <div class="l-box-lrg pure-u-1 pure-u-md-2-5" style="padding-left: 5em; margin-left: 30%; margin-right: 30%">
                <p>
                <a href="index.php"><button style="font-size: 200%" class="pure-button">Single Keyword</button></a>
                </p> <br>
                <p>
                <a href="profileanal.php"><button style="font-size: 210%; padding-right: 2.5em" class="pure-button">Analyse User</button></a>
                </p>

            </div>

            <div class="l-box-lrg pure-u-1 pure-u-md-3-5" style="text-align: center; margin-right:2.5em">
                <a href="http://www.alchemyapi.com/"><h3>AlchemyAPI</h3></a>
                <p>
                    AlchemyAPI combines expertise in machine learning
                     with a massively scalable processing infrastructure <br> to produce some of
                    the most advanced text and image analytics available.
                </p>

                <a href="https://github.com/abraham/twitteroauth"><h3>TwitterOAuth</h3></a>
                <p>
                    PHP library for working with Twitter's OAuth API developed by Abraham Williams.
                </p>
            </div>
        </div>

    </div>

    <div class="footer l-box is-center">
        Created by Matt Flynn 2014 -- <a href="http://purecss.io">PureCSS</a> courtesy of YUI Team.
    </div>

</div>






</body>
</html>