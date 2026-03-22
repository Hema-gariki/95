<html>
    <body>
        <h2>Upload CSV file</h2>
        <form method = "POST" enctype="multipart/form-data" action "csv_processor.php">
            <input type = "file" name = "csv" required>
            <input type ="text" name = "survey_name" required>
            <br><br>
            <input type = "submit" value = "Generate Questionnarie">
        </form>
    </body>
</html>   
