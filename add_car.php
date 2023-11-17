<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: logout_user.php');
}
?>

<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Car Statistics Monitoring System</title>
</head>
<body>
    <h1>Add car</h1>
    <form method='post' action='/cse30246/owd/upload_car.php'>
        <label for='VIN'>VIN:</label>
        <input type='text' id='VIN' name='VIN' required minlength='17' maxlength='17'/>
        
        <br/><br/>
        
        <label for='make'>Make:</label>
        <input type='text' id='make' name='make' required maxlength='255'/>
        
        <br/><br/>
        
        <label for='model'>Model:</label>
        <input type='text' id='model' name='model' required maxlength='255'/>
        
        <br/><br/>
        
        <label for='year'>Year:</label>
        <input type='text' id='year' name='year' required pattern='(?:190[1-9])|(?:19[1-9][0-9])|(?:20[0-9]{2})|(?:21[0-4][0-9])|(?:215[0-5])'/>
        
        <br/><br/>
        <input type='submit' value='Add car'/>
    </form>
    <br/><br/>
    <button onclick='location.href="/cse30246/owd/home.php"' type='button'>
        Back
    </button>
</body>
</html>
