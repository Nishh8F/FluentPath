<?php
$host = "localhost;port=3307";
$username = "root";
$password = "";

try {
    $conn = new PDO("mysql:host=$host;dbname=fluentpath", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "CREATE TABLE IF NOT EXISTS user_progress (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        lang_code VARCHAR(50) NOT NULL,
        progress_percent INT(11) DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY user_lang (user_id, lang_code)
    )";
    $conn->exec($sql);
    echo "user_progress table created successfully.\n";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
