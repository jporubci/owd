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

/* Try to remove trip */
try {
    $trip_id = $_POST['trip_id'];
    
    /* Begin transaction */
    $dbh->beginTransaction();
    $dbh->exec('DELETE FROM Trip_Data WHERE trip_id=' . $trip_id);
    $dbh->exec('DELETE FROM Trips WHERE trip_id=' . $trip_id);
    $dbh->commit();

} catch (Exception $e) {
    $dbh->rollBack();
    exit('Failed to delete trip: ' . $e->getMessage());
}

header('Location: home.php');

?>
