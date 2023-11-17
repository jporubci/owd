<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['username'])) {
    header('Location: home.php');
}
?>

<html lang='en'>
<head>
    <meta charset='UTF-8'/>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'/>
    <title>Car Statistics Monitoring System</title>
</head>
<body>
    <h1>On-Web Diagnostics</h1>
    <button onclick='location.href="/cse30246/owd/login.php"' type='button'>
        Log in
    </button>
    <br/><br/>
    <button onclick='location.href="/cse30246/owd/register.php"' type='button'>
        Register
    </button>
</body>
</html>
