<?php
require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, let's clear the existing phrases table just in case they ran it multiple times
    $conn->exec("TRUNCATE TABLE phrases");
    
    // Read the SQL dump line by line
    $lines = file(__DIR__ . '/phrases_backup.sql');
    
    // Execute only the INSERT INTO statement to avoid PDO comment/syntax parsing errors
    $inserted = false;
    foreach ($lines as $line) {
        if (strpos(trim($line), 'INSERT INTO') === 0) {
            $conn->exec($line);
            $inserted = true;
        }
    }
    
    if ($inserted) {
        echo "<h1>Phrases Seeded Successfully!</h1>";
        echo "<p>All the lesson data has been successfully imported into your Azure database.</p>";
        echo "<a href='/'>Go back to the app</a>";
    } else {
        echo "<h1>Warning</h1>";
        echo "<p>Could not find the INSERT INTO statement in the backup file.</p>";
    }
    
} catch(Exception $e) {
    echo "<h1>Error Seeding Phrases</h1>";
    echo "<p>Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
