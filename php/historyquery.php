<?php
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