<?php 
    //require 'php/sentanal.php';
    //include 'php/historyquery.php';
?>

<?php
function sentanal($keyword, $amount) {

    /*Here we declare the relevant consumer & access keys for the twitter api. */
    include_once "twitteroauth/twitteroauth.php"; 
    require_once 'php/alchemyapi.php';
    $alchemyapi = new AlchemyAPI();
    $consumer = "gxoEc96Hnwn6O6xKU6CI4dNpB";
    $consumersecret ="COuVZRJTfjTHso6OnLJs7IZfI9vXlBFn9QeTxfmHpccZ0F04DB";
    $accesstoken ="20649653-vSHPvUgr85WPyhLhlqtZ1l801xGmS2mJMiAEben2h";
    $accesstokensecret ="3nNYdvV6XAOPA5P8xJ4knEFgfH6z5JF9QX3DYMcB1CMjA";

    /*Below we pass those keys to twitterOauth*/

    $twitter = new twitteroauth($consumer,$consumersecret,$accesstoken,$accesstokensecret);

    /* Declare variables that will store the count of sentiments.*/
    $count_positive = 0;
    $count_neutral = 0;
    $count_negative = 0;

    /*Below we make a get request to the twitter api, passing in our keyword and amount. The data is returned in json form and stored the varibale "tweets"*/
        if ( isset($keyword)){
            $tweets = $twitter->get('https://api.twitter.com/1.1/search/tweets.json?q=%22%20'.$keyword.'%20%22&lang=en&result_type=recent&count='.$amount.'');
            /* we loop through each tweet*/
            foreach($tweets as $tweet){
                foreach ($tweet as $t){
                    /*if the text field is empty, we do not send it for analysis, instead we break*/
                    if( empty($t->text)) {
                        break;
                    }
                    else {
                        /*We pass the text field to alchemyapi for sentiment analysis which is returned in response variable*/
                        $response = $alchemyapi->sentiment('text', $t->text, null);
                        /*we print the user profile pic and the text*/
                        echo ' <div class="tweetprint"><div class="tweetprintcontent"><img src="'.$t->user->profile_image_url.'" />  ' .$t->text,'<br> <b>Sentiment:</b> ', $response['docSentiment']['type'], '</div></div><br>';

                        if ($response['status'] == 'OK') {

                            /*Database connection*/
                            $mysqli = new mysqli("127.0.0.1", "testtwit", "iluvtwitter", "twitsent");
                            if ($mysqli->connect_errno) {
                                echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
                            }
                            /*remove special characters from keyword. */
                                $keyphrase =  mysqli_real_escape_string($mysqli, $_POST['keyword']);
                                $finalsent = $response['docSentiment']['type']; 

                            /*if sentiment pos/neg, then store score, otherwise just store keyword + sentiment*/
                            if (array_key_exists('score', $response['docSentiment'])) {
                                $finalscore = $response['docSentiment']['score'];
                                mysqli_query($mysqli,"INSERT INTO analhist (keyword, sentiment, score) VALUES ('$keyphrase', '$finalsent', '$finalscore')");
                            }
                            else{
                                mysqli_query($mysqli,"INSERT INTO analhist (keyword, sentiment) VALUES ('$keyphrase', '$finalsent')");
                                }
                                    mysqli_close($mysqli);
                            }

                        /*Using the below functions, we iterate throught the rsponse object. We then loop through the new iterator variable and 
                        count each sentiment. */
                        $iterator = new RecursiveIteratorIterator(new RecursiveArrayIterator($response)); 

                        foreach ($iterator as $key => $value) {
                            if ($key == 'type' && $value == 'positive') {
                                $count_positive++;
                            }
                        }

                            foreach ($iterator as $key => $value) {
                            if ($key == 'type' && $value == 'neutral') {
                                $count_neutral++;
                            }
                        }

                        foreach ($iterator as $key => $value) {
                            if ($key == 'type' && $value == 'negative') {
                                $count_negative++;
                            }
                        }
                }
            }
            }
        }
        /*We return the counts of each sentiment */
        return array ($count_positive, $count_neutral, $count_negative);
    }

    function historyquery($keyphrase){
        $mysqli = new mysqli("127.0.0.1", "testtwit", "iluvtwitter", "twitsent");
        if ($mysqli->connect_errno) {
            echo "Failed to connect to MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
        }

        /*Execute Db query to count occurrence of positive sentiment associated with keyword */
        $db_pos = mysqli_query($mysqli, "SELECT sentiment, COUNT(*) FROM analhist WHERE sentiment='positive' AND keyword='$keyphrase'");
        /* fetch the result of above query into an array */
        $pos_result_array = mysqli_fetch_row($db_pos);
        /* Access the second index in that array to get our summed value from COUNT query */
        $pos_result = $pos_result_array[1];

        /*Repeat above query & fetching for negative & neutral sentiments */
        $db_neut = mysqli_query($mysqli, "SELECT sentiment, COUNT(*) FROM analhist WHERE sentiment='neutral' AND keyword='$keyphrase'");
        $neut_result_array = mysqli_fetch_row($db_neut);
        $neut_result = $neut_result_array[1];

        $db_neg = mysqli_query($mysqli, "SELECT sentiment, COUNT(*) FROM analhist WHERE sentiment='negative' AND keyword='$keyphrase'");
        $neg_result_array = mysqli_fetch_row($db_neg);
        $neg_result = $neg_result_array[1];

        /*close db connection */
        mysqli_close($mysqli);

        return array ($pos_result, $neut_result, $neg_result);
    }
?>
<html lang="en">
<head>
    <meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="description" content="Sentiment Analysis tool for Tweets - designed by Matt Flynn. UCD Computer Science Conversion 13204846">

    <title>Twitter Sentiment Analysis - Matt Flynn &ndash; </title>
    <script src="Chart.js/Chart.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.1/jquery.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    
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
                <!-- Create form for submitting keyword. This keyword will be searched for related tweets and those tweets will be analysed.-->
            <form class="pure-form" action="index.php#resulthead" method="post" id="tweetquery">
                <label for="Analyse">
                <input type="text" name="keyword" id="keyword" class="pure-input-rounded" style="height: 3em" placeholder="Search..." required>
            </div>
                <select name="amount">
                    <option value ="10" selected>10</opifton>
                    <option value ="25">25</option>
                    <option value ="50">50</option>
                </select>
                </label>
        </p>
        <p>
                <button class="formbutton-custom" type="submit" value="Submit" onclick="Javascript: revealDivs();"><h2>Analyse</h2></button>
            </form>
        </p>
    </div>

    <script>
        function revealDivs(){
           document.getElementById( 'resgroup' ).style.display = 'block';
           document.getElementById( 'graphgroup' ).style.display = 'block';
        } 
    </script>
</div>

<!-- Display fetched tweets -->

<div class="content-wrapper" >
    <div class="content" id="resgroup" style="display: none">
    </br><br>
    <div id="resulthead"><h2 style="display: none">testbreak</h2><br><br></div>
        <h2  class="content-head is-center" >TWEET SEARCH RESULTS</h2>
        <div class="pure-g">

            <div class="l-box pure-u-1 pure-u-md-1-2 pure-u-lg-1-4">

                <h3 style="text-align: center" class="content-subhead">
                    <?php if(isset($_POST['keyword'])){ 
                        $caps = strtoupper($_POST['keyword']);
                        echo $caps; } ?>
                </h3>
                <p>
                     <?php 

                        /* throw form data into new variable to avoid undeclared variable error in php */      
                        if(isset($_POST['keyword'])){$keyphrase = $_POST['keyword']; }
                        if(isset($_POST['amount'])){$useramount = $_POST['amount']; }

                        /*Below we send our keyphrase and requested amount to the sentiment analysis function (sentanal). 
                        This function returns 3 variables which we put into an array using the list function. */
                        list ($count_positive, $count_neutral, $count_negative) = @sentanal($keyphrase, $useramount);

                        /*Now run mysql query to establish historical data related to keyword. 
                        The history query function returns the count of all pos, neg + neutral sentiments related to the keyword in the database.*/
                        if(isset($_POST['keyword'])){
                        list ($pos_hist, $neut_hist, $neg_hist) = historyquery($keyphrase);
                        }
                        echo '<br>';   
                    ?>

                </p>
            </div>

        </div>
    </div>

    <div class="ribbon l-box-lrg pure-g" id="graphgroup" style="display: none">
        <div class="l-box-lrg is-center pure-u-1 pure-u-md-1-2 pure-u-lg-2-5">
        </div>
        <div class="pure-u-1 pure-u-md-1-2 pure-u-lg-3-5">

            <h2 class="content-head content-head-ribbon" style="text-align: center">Sentiment analysis results for <?php if(isset($_POST['keyword'])){ echo $_POST['keyword']; } ?> </h2>
            <!-- below we create our divs and canvas that will hold the visual charts. -->
            <p>
                 <div id="cavans-holder" style="width: 40%; margin-left: 27.5%; padding: 2.5%">
                    <canvas id="chart-area" width="35em" height="35em"></canvas>
                    <p>
                </div>
            <h2 class="content-head content-head-ribbon" style="text-align: center">Total past queries for <?php if(isset($_POST['keyword'])){ echo $_POST['keyword']; } ?> </h2>
                <div style="width: 40%; margin-left: 30%; padding: 2.5%">
                    <p>
                    <canvas id="canvas2" height="35em" width="35em"></canvas>
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

            /*Access total sentiment history of keyword from database. */

            var pos_countdb = <?php echo json_encode($pos_hist); ?>;
            var neut_countdb = <?php echo json_encode($neut_hist); ?>;
            var neg_countdb = <?php echo json_encode($neg_hist); ?>;
            
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
                        highlightFill: "rgba(255,90,94, 0.8)",
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
            };
        </script>
            </p>
        </div>
    </div>

    <!--This section of provides links to the tools and sources that this site is based on. 
    Also provides links to the user profile and comparator analysers. -->

    <div class="content">
        <h2 class="content-head is-center">Powered by AlchemyAPI and TwitterOAuth by Abraham Williams</h2>

        <div style="text-align: center;">
                <p>
                    <p>
                <a href="comparison.php"><button style ="font-size: 200%" class="pure-button">Compare Terms</button></a>
                </p> <br>
                <p>
                <a href="profileanal.php"><button style ="font-size: 205%; padding-right:2.5em" class="pure-button">Analyse User</button></a>
                </p>
        </div>

        <div class="pure-g">

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