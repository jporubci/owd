<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (isset($_SESSION['username'])) {
    header('Location: home.php');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request method.');
}

/* Connect to MySQL DB */
$options = [
    PDO::ATTR_CASE                      => PDO::CASE_NATURAL,
    PDO::ATTR_ERRMODE                   => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_ORACLE_NULLS              => PDO::NULL_EMPTY_STRING,
    PDO::ATTR_STRINGIFY_FETCHES         => false,
    PDO::ATTR_AUTOCOMMIT                => true,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY  => true,
];
$dbh = null;
for ($retry = 0; $retry < 3; $retry++) {
    try {
        $dbh = new PDO('mysql:dbname=jporubci;host=localhost', 'jporubci', 'goirish', $options);
    } catch (PDOException $e) {
        echo 'Failed to connect to database - ' . $e->getMessage() . ', retrying in ' . 2**$retry . ' seconds...';
        sleep(2**$retry);
    }
}

if (!$dbh) {
    exit('Failed to connect to database.');
}

/* Validate user */
$username = $_POST['username'];
$password = $_POST['password'];

/* Try to look up user */
try {
    /* Begin transaction */
    $a = false;
    foreach ($dbh->query('SELECT username FROM Users WHERE username=\'' . $username . '\' AND password=\'' . $password . '\'') as $row) {
        $a = true;
    }
    
    if (!$a) {
        exit('Invalid username and password combination.');
    }
    
} catch (Exception $e) {
    exit('Failed to log in: ' . $e->getMessage());
}

/* Start session */
$_SESSION['name'] = $name;
$_SESSION['password'] = $password;
$_SESSION['username'] = $username;

header('Location: home.php');

?>
