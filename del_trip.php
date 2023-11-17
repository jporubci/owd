<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: logout_user.php');
}
include('get_trips.php');
?>

<html lang='en'>
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Car Statistics Monitoring System</title>
</head>
<body>
    <h1>Delete trip</h1>
    <form id='trip_form' method='post' action='/cse30246/owd/remove_trip.php'>
        <label for='trip'>Trip:</label>
        <select id='trip' name='trip_id' required>
            <?php
            echo $_SESSION['trips'];
            ?>
        </select>
        
        <br/><br/>
        <input type='submit' value='Delete trip'/>
    </form>
    <br/>
    <button onclick='location.href="/cse30246/owd/home.php"' type='button'>
        Back
    </button>
</body>
</html>
