<?php

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
$name = $_POST['name'];

/* Try to look up user */
try {
    /* Begin transaction */
    $a = false;
    foreach ($dbh->query('SELECT username FROM Users WHERE username=\'' . $username . '\'') as $row) {
        $a = true;
    }
    
    if ($a) {
        exit('User already exists.');
    }
    
} catch (Exception $e) {
    exit('Failed to log in: ' . $e->getMessage());
}

/* Try to upload car */
try {
    $dbh->exec('INSERT INTO Users (username, password, name) VALUES (\'' . $username . '\', \'' . $password . '\', \'' . $name . '\')');

} catch (Exception $e) {
    exit('Failed to add car: ' . $e->getMessage());
}

echo 'Registered succesfully!';

?>
