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
    <link rel="stylesheet" type='text/css' href="css/style.css">
</head>
<body>
    <h1>Log in</h1>
    <form method='post' action='/cse30246/owd/login_user.php'>
        <label for='username'>Username:</label>
        <input type='text' id='username' name='username' required maxlength='255'/>
        <br/><br/>
        <label for='password'>Password:</label>
        <input type='password' id='password' name='password' required minlength='8' maxlength='255'/>
        <br/><br/>
        <input type='submit' name='' value='Log in'/>
    </form>
</body>
</html>
