<?php
require_once __DIR__ . '/config.php';

// Helper function to check if a column exists in a table
function columnExists($conn, $table, $column) {
    try {
        $stmt = $conn->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        return $stmt->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}

try {
    $conn = getDBConnection();
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Create languages table if it doesn't exist
    $sqlLanguages = "CREATE TABLE IF NOT EXISTS languages (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        code VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        sub_title VARCHAR(100) DEFAULT ''
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
    $conn->exec($sqlLanguages);
    echo "languages table created or already exists.<br>\n";

    // 2. Seed languages table with the 7 supported languages/dialects
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

    // 3. Create or migrate phrases table
    // Check if phrases table exists
    $phrasesTableExists = false;
    try {
        $conn->query("SELECT 1 FROM phrases LIMIT 1");
        $phrasesTableExists = true;
    } catch (Exception $e) {
        $phrasesTableExists = false;
    }

    if (!$phrasesTableExists) {
        // Fresh setup: create phrases table with language_id
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
        echo "phrases table created successfully.<br>\n";
    } else {
        // phrases table exists. Let's check if it needs migration (has lang_code)
        if (columnExists($conn, 'phrases', 'lang_code')) {
            echo "Migrating phrases table...<br>\n";
            // 1. Add language_id column if it doesn't exist
            if (!columnExists($conn, 'phrases', 'language_id')) {
                $conn->exec("ALTER TABLE phrases ADD COLUMN language_id INT(11) AFTER id");
                echo "Added language_id column to phrases.<br>\n";
            }
            // 2. Map existing data from lang_code to language_id
            $conn->exec("UPDATE phrases p JOIN languages l ON p.lang_code = l.code SET p.language_id = l.id");
            echo "Mapped lang_code to language_id in phrases.<br>\n";
            // 3. Make language_id NOT NULL
            $conn->exec("ALTER TABLE phrases MODIFY COLUMN language_id INT(11) NOT NULL");
            // 4. Drop old lang_code column
            $conn->exec("ALTER TABLE phrases DROP COLUMN lang_code");
            echo "Dropped lang_code from phrases.<br>\n";
            // 5. Add foreign key constraint
            $conn->exec("ALTER TABLE phrases ADD CONSTRAINT fk_phrases_language FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE");
            echo "Added foreign key constraint to phrases table.<br>\n";
        } else {
            echo "phrases table already normalized.<br>\n";
        }
    }

    // 4. Create or migrate user_progress table
    $progressTableExists = false;
    try {
        $conn->query("SELECT 1 FROM user_progress LIMIT 1");
        $progressTableExists = true;
    } catch (Exception $e) {
        $progressTableExists = false;
    }

    if (!$progressTableExists) {
        // Fresh setup
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
        echo "user_progress table created successfully.<br>\n";
    } else {
        // user_progress exists. Check if it needs migration
        if (columnExists($conn, 'user_progress', 'lang_code')) {
            echo "Migrating user_progress table...<br>\n";
            // 1. Add language_id column
            if (!columnExists($conn, 'user_progress', 'language_id')) {
                $conn->exec("ALTER TABLE user_progress ADD COLUMN language_id INT(11) AFTER user_id");
                echo "Added language_id column to user_progress.<br>\n";
            }
            // 2. Map existing data
            $conn->exec("UPDATE user_progress up JOIN languages l ON up.lang_code = l.code SET up.language_id = l.id");
            echo "Mapped lang_code to language_id in user_progress.<br>\n";
            
            // 3. Add temporary index to user_id to satisfy foreign key requirement while dropping user_lang
            $conn->exec("ALTER TABLE user_progress ADD INDEX temp_user_idx (user_id)");
            
            // 4. Drop old index user_lang
            try {
                $conn->exec("ALTER TABLE user_progress DROP INDEX user_lang");
                echo "Dropped old unique index user_lang.<br>\n";
            } catch (Exception $e) {
                // If it fails (maybe key name was different or already dropped), ignore
            }
            // 5. Drop lang_code column
            $conn->exec("ALTER TABLE user_progress DROP COLUMN lang_code");
            echo "Dropped lang_code from user_progress.<br>\n";
            // 6. Make language_id NOT NULL
            $conn->exec("ALTER TABLE user_progress MODIFY COLUMN language_id INT(11) NOT NULL");
            // 7. Create new unique index
            $conn->exec("ALTER TABLE user_progress ADD UNIQUE KEY user_lang (user_id, language_id)");
            echo "Created unique index on (user_id, language_id).<br>\n";
            // 8. Drop temporary index
            $conn->exec("ALTER TABLE user_progress DROP INDEX temp_user_idx");
            // 9. Add foreign key constraints
            $conn->exec("ALTER TABLE user_progress ADD CONSTRAINT fk_progress_language FOREIGN KEY (language_id) REFERENCES languages(id) ON DELETE CASCADE");
            echo "Added foreign key constraint to user_progress.<br>\n";
        } else {
            echo "user_progress table already normalized.<br>\n";
        }
    }

    // 5. Alter users table
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
        echo "Note: users columns might already exist.<br>\n";
    }

    // 6. Create user_milestones table
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
    echo "user_milestones table created successfully.<br>\n";

    // 7. Create user_badges table
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
    echo "user_badges table created successfully.<br>\n";

    echo "<strong>All tables set up and migrated successfully!</strong><br>\n";

} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}
?>
