<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: logout_user.php');
}
include('get_cars.php');
?>

<html lang='en'>
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Car Statistics Monitoring System</title>
</head>
<body>
    <h1>Add trip</h1>
    <form id='trip_form' method='post' action='/cse30246/owd/upload_trip.php' enctype='multipart/form-data'>
        <label for='car'>Car:</label>
        <select id='car' name='VIN' required>
            <?php
            echo $_SESSION['cars'];
            ?>
        </select>
        
        <br/><br/>
        
        <label for='file'>File:</label>
        <input type='file' id='file' name='file' accept='text/csv' required/>
        
        <br/><br/>
        <input type='submit' value='Upload trip'/>
    </form>
    <br/>
    <button onclick='location.href="/cse30246/owd/home.php"' type='button'>
        Back
    </button>
</body>
</html>
