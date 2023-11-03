<?php

$phpFileUploadErrors = array(
    0 => 'There is no error, the file uploaded with success',
    1 => 'The uploaded file exceeds the upload_max_filesize directive in php.ini',
    2 => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form',
    3 => 'The uploaded file was only partially uploaded',
    4 => 'No file was uploaded',
    6 => 'Missing a temporary folder',
    7 => 'Failed to write file to disk.',
    8 => 'A PHP extension stopped the file upload.',
);

/* Check errors */
if ($_FILES['file']['error'] != 0) {
    exit('Error uploading file: ' . $phpFileUploadErrors[$_FILES['file']['error']]);
}

/* Check file size */
if ($_FILES['file']['size'] == 0 || $_FILES['file']['size'] > 2**30) {
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
$VIN = $_POST['VIN'];

/* Try to look up user */
try {
    /* Begin transaction */
    $exists = false;
    foreach ($dbh->query('SELECT Users.username FROM Users, Cars WHERE Users.username=Cars.username AND Users.username=\'' . $username . '\' AND password=\'' . $password . '\' AND VIN=\'' . $VIN . '\'') as $row) {
        if (!$exists) {
            $exists = true;
        } else {
            exit('Unexpected error: username, password, and VIN combination not unique.');
        }
    }
    
    if (!$exists) {
        exit('Invalid username, password, and VIN combination.');
    }

} catch (Exception $e) {
    exit('Failed to log in: ' . $e->getMessage());
}

/* Open CSV */
$csv = fopen($_FILES['file']['tmp_name'], 'r');
if (!$csv) {
    exit('Failed to open CSV file.');
}

/* Parse CSV */
if (ini_set('auto_detect_line_endings', true) === false) {
    exit('Failed to auto detect line endings.');
}

$queries = [];
$header = fgetcsv($csv);
for ($row = fgetcsv($csv); $row; $row = fgetcsv($csv)) {
    $num_cols = count($row);
    $q = array_fill(0, 5, null);
    for ($col = 0; $col < $num_cols; $col++) {
        if (preg_match('/timestamp/i', $header[$col])) {
            $q[0] = $row[$col] ? $row[$col] : 'null';
        } elseif (preg_match('/rpm/i', $header[$col])) {
            $q[1] = $row[$col] ? $row[$col] : 'null';
        } elseif (preg_match('/speed/i', $header[$col])) {
            $q[2] = $row[$col] ? $row[$col] : 'null';
        } elseif (preg_match('/engine_load/i', $header[$col])) {
            $q[3] = $row[$col] ? $row[$col] : 'null';
        } elseif (preg_match('/coolant_temp/i', $header[$col])) {
            $q[4] = $row[$col] ? $row[$col] : 'null';
        }
    }
    
    $queries[] = $q;
}

if (!fclose($csv)) {
    exit('Unexpected failure: failed to close csv file.');
}

if (!$queries) {
    exit('No data in CSV.');
}

/* Try to upload trip */
try {
    $start_time = $queries[key(reset($queries))][0];
    $end_time = $queries[key(end($queries))][0];
    
    /* Begin transaction */
    $dbh->beginTransaction();
    $dbh->exec('INSERT INTO Trips (VIN) VALUES (\'' . $VIN . '\')');
    $trip_id = null;
    foreach ($dbh->query('SELECT trip_id FROM Trips WHERE VIN=\'' . $VIN . '\'') as $row) {
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
    
    reset($queries);
    foreach ($queries as $query) {
        $q = 'INSERT INTO Trip_Data (trip_id, time, rpm, speed, engine_load, coolant_temp) VALUES (' . $trip_id . ', FROM_UNIXTIME(' . substr($query[0], 0, -3) . '.' . substr($query[0], -3) . '), ' . $query[1] . ', ' . $query[2] . ', ' . $query[3] . ', ' . $query[4] . ')';
        
        $dbh->exec($q);
    }
    $dbh->commit();

} catch (Exception $e) {
    $dbh->rollBack();
    echo $q;
    exit('Failed to upload data: ' . $e->getMessage());
}

echo 'Upload success!';

?>
