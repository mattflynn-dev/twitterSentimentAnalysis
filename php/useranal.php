<?php
function useranalsearch($keyword, $amount) {

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
            $tweets = $twitter->get('https://api.twitter.com/1.1/statuses/user_timeline.json?screen_name='.$keyword.'&lang=en&count='.$amount.'');
            /* we loop through each tweet*/
            foreach($tweets as $tweet){
                     /*if the text field is empty, we do not send it for analysis, instead we break*/
                    if( empty($tweet->text)) {
                        break;
                    }
                    else{
                        /*We pass the text field to alchemyapi for sentiment analysis which is returned in response variable*/
                        $response = $alchemyapi->sentiment('text', $tweet->text, null);
                        /*we print the user profile pic and the text*/
                        echo ' <div class="tweetprint"><div class="tweetprintcontent"><img src="'.$tweet->user->profile_image_url.'" /> ' .$tweet->text,'<br> <b>Sentiment:</b> ', $response['docSentiment']['type'], '</div></div><br>';
                        if ($response['status'] == 'OK') {
                            if (array_key_exists('score', $response['docSentiment'])) {
                            }
                        }
                        else{
                            echo 'Error in the sentiment analysis call: ', $response['statusInfo'];
                            }
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
        return array ($count_positive, $count_neutral, $count_negative);
    }
?>