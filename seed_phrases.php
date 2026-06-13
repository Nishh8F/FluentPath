<?php
require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // First, let's clear the existing phrases table just in case they ran it multiple times
    $conn->exec("TRUNCATE TABLE phrases");
    
    // Read the SQL dump
    $sql = file_get_contents(__DIR__ . '/phrases_backup.sql');
    
    // Execute the SQL dump
    $conn->exec($sql);
    
    echo "<h1>Phrases Seeded Successfully!</h1>";
    echo "<p>All the lesson data has been successfully imported into your Azure database.</p>";
    echo "<a href='/'>Go back to the app</a>";
    
} catch(Exception $e) {
    echo "<h1>Error Seeding Phrases</h1>";
    echo "<p>Connection failed: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>
