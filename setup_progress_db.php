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
    echo "user_progress table created successfully.<br>\n";

    // Add new columns to users table
    $alterUsersSql = "
        ALTER TABLE users 
        ADD COLUMN IF NOT EXISTS lessons_done INT(11) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS total_xp INT(11) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS daily_xp INT(11) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS current_streak INT(11) DEFAULT 0,
        ADD COLUMN IF NOT EXISTS last_activity_date DATE DEFAULT NULL
    ";
    try {
        $conn->exec($alterUsersSql);
        echo "users table altered successfully (columns added).<br>\n";
    } catch (PDOException $e) {
        // Some older MySQL versions don't support IF NOT EXISTS in ALTER TABLE, 
        // so we catch the error if column already exists
        echo "Note: users columns might already exist.<br>\n";
    }

    $sqlMilestones = "CREATE TABLE IF NOT EXISTS user_milestones (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        title VARCHAR(100) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        icon VARCHAR(50) DEFAULT 'fa-check',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sqlMilestones);
    echo "user_milestones table created successfully.<br>\n";

    $sqlBadges = "CREATE TABLE IF NOT EXISTS user_badges (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        title VARCHAR(100) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        icon VARCHAR(50) DEFAULT 'fa-award',
        earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    )";
    $conn->exec($sqlBadges);
    echo "user_badges table created successfully.<br>\n";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
