<?php
require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, let's clear the existing phrases table just in case they ran it multiple times
    $conn->exec("TRUNCATE TABLE phrases");
    
    // Read the entire SQL dump
    $sql = file_get_contents(__DIR__ . '/phrases_backup.sql');
    
    // Extract the multi-line INSERT INTO statement using regex (starts with INSERT INTO, ends with ;)
    $inserted = false;
    if (preg_match('/INSERT INTO `phrases` VALUES .*?;/is', $sql, $matches)) {
        $conn->exec($matches[0]);
        $inserted = true;
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
