<?php
require_once __DIR__ . '/config.php';

// First connect without dbname to create the database if it doesn't exist
try {
    $conn = new PDO("mysql:host=$db_host;charset=utf8mb4", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->exec("CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    echo "Database `$db_name` created or already exists.<br>\n";
} catch (PDOException $e) {
    echo "Failed to create database: " . $e->getMessage() . "\n";
    exit(1);
}

// Now get connection using config.php's function
try {
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create users table
    $sqlUsers = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        name VARCHAR(100) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        birthday DATE DEFAULT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        lessons_done INT(11) DEFAULT 0,
        total_xp INT(11) DEFAULT 0,
        daily_xp INT(11) DEFAULT 0,
        current_streak INT(11) DEFAULT 0,
        last_activity_date DATE DEFAULT NULL,
        last_reward_date DATE DEFAULT NULL,
        coins INT DEFAULT 100,
        auth_token VARCHAR(64) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlUsers);
    echo "users table created or already exists.<br>\n";

    // Create login_attempts table
    $conn->exec("CREATE TABLE IF NOT EXISTS login_attempts (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        attempt_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX(ip_address),
        INDEX(attempt_time)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    echo "login_attempts table created or already exists.<br>\n";

    // Create languages table
    $sqlLanguages = "CREATE TABLE IF NOT EXISTS languages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        sub_title VARCHAR(100) DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlLanguages);
    echo "languages table created or already exists.<br>\n";

    // Seed languages
    $languages = [
        ['code' => 'kelantan', 'name' => 'Bahasa Melayu', 'sub_title' => 'Kelantan (Kecek Kelate)'],
        ['code' => 'terengganu', 'name' => 'Bahasa Melayu', 'sub_title' => 'Terengganu (Ganu)'],
        ['code' => 'perak', 'name' => 'Bahasa Melayu', 'sub_title' => 'Perak (Loghat Perak)'],
        ['code' => 'selangor', 'name' => 'Bahasa Melayu', 'sub_title' => 'Selangor/KL (Standard Urban)'],
        ['code' => 'utara', 'name' => 'Bahasa Melayu', 'sub_title' => 'Utara (Loghat Utara)'],
        ['code' => 'english', 'name' => 'English', 'sub_title' => 'Global Communication'],
        ['code' => 'russian', 'name' => 'Russian', 'sub_title' => 'Русский язык'],
    ];

    foreach ($languages as $lang) {
        $stmt = $conn->prepare("SELECT COUNT(*) FROM languages WHERE code = ?");
        $stmt->execute([$lang['code']]);
        if ($stmt->fetchColumn() == 0) {
            $insert = $conn->prepare("INSERT INTO languages (code, name, sub_title) VALUES (?, ?, ?)");
            $insert->execute([$lang['code'], $lang['name'], $lang['sub_title']]);
            echo "Seeded language: {$lang['code']}<br>\n";
        }
    }

    // Create phrases table
    $sqlPhrases = "CREATE TABLE IF NOT EXISTS phrases (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        language_id INT(11) NOT NULL,
        phrase VARCHAR(255) NOT NULL,
        correct_answer VARCHAR(255) NOT NULL,
        wrong_1 VARCHAR(255) NOT NULL,
        wrong_2 VARCHAR(255) NOT NULL,
        wrong_3 VARCHAR(255) NOT NULL,
        FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlPhrases);
    echo "phrases table created or already exists.<br>\n";

    // Create user_progress table
    $sqlProgress = "CREATE TABLE IF NOT EXISTS user_progress (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        language_id INT(11) NOT NULL,
        progress_percent INT(11) DEFAULT 0,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE,
        UNIQUE KEY user_lang (user_id, language_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlProgress);
    echo "user_progress table created or already exists.<br>\n";

    // Create user_milestones table
    $sqlMilestones = "CREATE TABLE IF NOT EXISTS user_milestones (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        title VARCHAR(100) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        icon VARCHAR(50) DEFAULT 'fa-check',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlMilestones);
    echo "user_milestones table created or already exists.<br>\n";

    // Create user_badges table
    $sqlBadges = "CREATE TABLE IF NOT EXISTS user_badges (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        user_id INT(11) NOT NULL,
        title VARCHAR(100) NOT NULL,
        description VARCHAR(255) DEFAULT '',
        icon VARCHAR(50) DEFAULT 'fa-award',
        earned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlBadges);
    echo "user_badges table created or already exists.<br>\n";

    // Seed phrases table from phrases_backup.sql if it exists
    $backupFile = __DIR__ . '/phrases_backup.sql';
    if (file_exists($backupFile)) {
        $conn->exec("TRUNCATE TABLE phrases");
        $sql = file_get_contents($backupFile);
        
        // Strip comments and split queries
        $sql = preg_replace('/--.*$/m', '', $sql);
        $sql = preg_replace('/\/\*.*?\*\//s', '', $sql);
        $queries = array_filter(array_map('trim', explode(';', $sql)));
        
        $inserted = false;
        foreach ($queries as $query) {
            if (!empty($query)) {
                $conn->exec($query);
                $inserted = true;
            }
        }
        if ($inserted) {
            echo "Phrases seeded successfully from backup file.<br>\n";
        }
    } else {
        echo "Warning: phrases_backup.sql not found. Phrases table not seeded.<br>\n";
    }

    echo "<strong>All tables set up and database seeded successfully!</strong><br>\n";

} catch (PDOException $e) {
    echo "Database setup failed: " . $e->getMessage() . "\n";
}
