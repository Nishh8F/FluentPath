<?php
require_once __DIR__ . '/config.php';
try {
    $conn = getDBConnection();
    // Add auth_token column if it doesn't exist
    $conn->exec("ALTER TABLE users ADD COLUMN auth_token VARCHAR(64) UNIQUE");
    echo "Success: auth_token column added to live database.\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
