<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
if (!isset($_SESSION['username'])) {
    header('Location: logout_user.php');
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
$username = $_SESSION['username'];
$password = $_SESSION['password'];

/* Try to get cars */
$cars = '';
try {
    foreach ($dbh->query('SELECT VIN, make, model, year FROM Users, Cars WHERE Users.username=Cars.username AND Users.username=\'' . $username . '\' AND Users.password=\'' . $password . '\'') as $row) {
        $cars .= '<option value=\'' . $row['VIN'] . '\'>' . $row['year'] . ' ' . $row['make'] . ' ' . $row['model'] . ' - ' . $row['VIN'] . '</option>';
    }

} catch (Exception $e) {
    exit('Failed to get cars: ' . $e->getMessage());
}

$_SESSION['cars'] = $cars;

?>
