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
?>