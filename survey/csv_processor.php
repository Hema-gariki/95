<?php 

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST[survey_name])){
    $survey_name = $post['survey_name'];
    echo "<h2>Survey Name:" . $survey_name. "</h2>";

     


    }
?>  
