<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: logout_user.php');
}
?>

<html lang='en'>
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Car Statistics Monitoring System</title>
</head>
<body>
    <h1>Home</h1>
    <button onclick='location.href="/cse30246/owd/add_trip.php"' type='button'>
        Add trip
    </button>
    <br/><br/>
    <button onclick='location.href="/cse30246/owd/add_car.php"' type='button'>
        Add car
    </button>
    <br/><br/>
    <button onclick='location.href="/cse30246/owd/del_trip.php"' type='button'>
        Delete trip
    </button>
    <br/><br/>
    <button onclick='location.href="/cse30246/owd/del_car.php"' type='button'>
        Delete car
    </button>
    <br/><br/>
    <button onclick='location.href="/cse30246/owd/logout_user.php"' type='button'>
        Log out
    </button>
</body>
</html>
