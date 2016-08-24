<?php 
    require 'php/useranal.php';
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
            <li><a href="profileanal.php">USER-ANALYSIS</a></li>
        </ul>
    </div>
</div>

<div class="splash-container">
    <div class="splash">
        <h1 class="splash-head">Sentiment Analysis for Twitter</h1>
        <p class="splash-subhead">
            <div id="specialformdiv">
                <!-- Create form for submitting username. This keyword will be searched for their tweets and those tweets will be analysed.-->
            <form class="pure-form" action="profileanal.php#resulthead" method="post">
                <label for="Analyse">
                <input type="text" name="username" class="pure-input-rounded" style="height: 3em" placeholder="Search..." required>
            </div>
                <select name="amount">
                    <option value ="10" selected>10</opifton>
                    <option value ="25">25</option>
                    <option value ="50">50</option>
                </select>
                </label>
        </p>
        <p>
                <button class="formbutton-custom" type="submit" value="Submit"><h2>Analyse</h2></button>
            </form>
        </p>
    </div>
</div>

<!-- Display fetched tweets -->
<div class="content-wrapper">
    <div class="content">
        <br><br>
        <div id="resulthead"><h2 style="display: none">testbreak</h2><br><br></div>
        <h2 class="content-head is-center">USER TIMELINE SEARCH RESULTS</h2>

        <div class="pure-g">
            <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                <h3 style="text-align: center" class="content-subhead">
                    <?php if(isset($_POST['username'])){ 
                        $caps = strtoupper($_POST['username']);
                        echo '@', $caps; } ?>
                </h3>
                <p>
                     <?php 

                        /* throw form data into new variable to avoid undeclared variable error in php */      
                        if(isset($_POST['username'])){$userpro = $_POST['username']; }
                        if(isset($_POST['amount'])){$useramount = $_POST['amount']; }

                        /*Below we send our user name and requested amount to the sentiment analysis function (sentanal). 
                        This function returns 3 variables which we put into an array using the list function. */
                        list ($count_positive, $count_neutral, $count_negative) = @useranalsearch($userpro, $useramount);
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

            <h2 class="content-head content-head-ribbon" style="text-align: center">Sentiment analysis results for @<?php if(isset($_POST['username'])){ echo $_POST['username']; } ?> </h2>
            <!-- below we create our divs and canvas that will hold the visual charts. -->
            <p>
                 <div id="canvas-holder" style="width: 40%; margin-left: 27.5%; padding: 2.5%">
                    <canvas id="chart-area" width="25em" height="25em"></canvas>
                    <p>
                </div>

        <script>

            /*Below we create javascript variable from the ones returned to us in the php functions*/
            var pos_count = <?php echo json_encode($count_positive); ?>;
            var neut_count = <?php echo json_encode($count_neutral); ?>;
            var neg_count = <?php echo json_encode($count_negative); ?>;

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
            
            /*below we create a donutchart using Chartjs*/
            window.onload = function(){
                var ctx = document.getElementById("chart-area").getContext("2d");
                window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {
                    responsive : true
                });
            };
        </script>
            </p>
        </div>
    </div>

    <div class="content">
        <h2 class="content-head is-center">Powered by AlchemyAPI and TwitterOAuth by Abraham Williams</h2>

        <div class="pure-g">
            <div class="l-box-lrg pure-u-1 pure-u-md-2-5" style="padding-left: 5em; margin-left: 30%; margin-right: 30%">
                <p>
                <a href="comparison.php"><button style="font-size: 200%" class="pure-button">Compare Terms</button></a>
                </p> <br>
                <p>
                <a href="index.php"><button style="font-size: 200%" class="pure-button">Keyword Search</button></a>
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