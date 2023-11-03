<?php

/* Check errors */
if ($_FILES['userfile']['error'] != 0) {
    exit('Error uploading file.');
}

/* Check file type */
if (mime_content_type($_FILES['userfile']['type']) != 'text/csv') {
    exit('File is not a CSV.');
}

/* Check file size */
if ($_FILES['userfile']['size'] == 0 || $_FILES['userfile']['size'] > 2**30) {
    exit('File size is too large.');
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
        echo 'Failed to connect to database - ' . $e->getMessage() . ', retrying in ' . 2**$retry . ' seconds...\r\n';
        sleep(2**$retry);
    }
}

if (!$dbh) {
    exit('Failed to connect to database.');
}

/* Validate user */
$username = $_GET['username'];
$password = $_GET['password'];
$VIN = $_GET['VIN'];

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

/* Open CSV */
$csv = fopen($_FILES['userfile']['tmp_name'], 'r');
if (!$csv) {
    exit('Failed to open CSV file.');
}

/* Parse CSV */
if (!ini_set('auto_detect_line_endings', true)) {
    exit('Failed to auto detect line endings.');
}

$queries = [];
$header = fgetcsv($csv);
for ($row = fgetcsv($csv); $row; $row = fgetcsv($csv)) {
    $num_cols = count($row);
    $q = array_fill(0, 5, null);
    for ($col = 0; $col < $num_cols; $col++) {
        if (preg_match('/timestamp/i', $header[$col]])) {
            $q[0] = $row[$col];
        } elseif (preg_match('/rpm/i', $header[$col]])) {
            $q[1] = $row[$col];
        } elseif (preg_match('/speed/i', $header[$col]])) {
            $q[2] = $row[$col];
        } elseif (preg_match('/engine_load/i', $header[$col]])) {
            $q[3] = $row[$col];
        } elseif (preg_match('/coolant_temp/i', $header[$col]])) {
            $q[4] = $row[$col];
        }
    }
    
    $queries[] = $q;
}

if (!$queries) {
    exit('No data in CSV.');
}

/* Try to upload trip */
try {
    $start_time = $queries[$array_key_first($queries)][0];
    $end_time = $queries[$array_key_last($queries)][0];
    
    /* Begin transaction */
    $dbh->beginTransaction();
    $dbh->exec('INSERT INTO Trips (VIN, start_time, end_time) VALUES (' . $VIN . ', ' . $start_time . ', ' . $end_time . ')');
    $trip_id = null;
    foreach ($dbh->query('SELECT trip_id FROM Trips WHERE VIN=' . $VIN . ' AND start_time=' . $start_time . ' AND end_time=' . $end_time) as $row) {
        if ($trip_id === null) {
            $trip_id = $row['trip_id'];
        } else {
            $dbh->rollBack();
            exit('Unexpected failure: trip_id not unique.');
        }
    }
    
    if ($trip_id === null) {
        $dbh->rollBack();
        exit('Unexpected failure: trip_id is null.');
    }
    
    foreach ($queries as $query) {
        $dbh->exec('INSERT INTO Trip_Data (trip_id, time, rpm, speed, engine_load, coolant_temp) VALUES (' $trip_id . ', ' . $query[0] . ', ' . $query[1] . ', ' . $query[2] . ', ' . $query[3] . ', ' . $query[4] . ')');
    }
    $dbh->commit();

} catch (Exception $e) {
    $dbh->rollBack();
    exit('Failed to upload data: ' . $e->getMessage());
}

echo 'Upload success!\r\n';

?>
