<html>
<body>
<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit('Invalid request method.');
}
/*
if (!isset($_POST['submit'])) {
    exit('No data.');
}*/

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
        echo 'Failed to connect to database - ' . $e->getMessage() . ', retrying in ' . 2**$retry . ' seconds...\r\n';
        sleep(2**$retry);
    }
}

if (!$dbh) {
    exit('Failed to connect to database.');
}

/* Validate user */
$username = $_POST['username'];
$password = $_POST['password'];
$VIN = $_POST['VIN'];

/* Try to look up user */
try {
    /* Begin transaction */
    $name = null;
    foreach ($dbh->query('SELECT name FROM Users, Cars WHERE Users.username=' . $username . ' AND password=' . $password . ' AND VIN=' . $VIN) as $row) {
        if ($name === null) {
            $name = $row['name'];
        } else {
            exit('Unexpected error: username, password, and VIN combination not unique.');
        }
    }
    
    if ($name === null) {
        exit('Invalid username, password, and VIN combination.');
    }

} catch (Exception $e) {
    exit('Failed to log in: ' . $e->getMessage());
}

/* Try to upload car */
try {
    $make = $_POST['make'];
    $model = $_POST['model'];
    $year = $_POST['year'];
    
    $dbh->exec('INSERT INTO Cars (username, VIN, make, model, year) VALUES (' . $username . ', ' . $VIN . ', ' . $make . ', ' . $model . ', ' . $year . ')');

} catch (Exception $e) {
    exit('Failed to add car: ' . $e->getMessage());
}

echo 'Car added successfully!\r\n';

?>
</body>
</html>
