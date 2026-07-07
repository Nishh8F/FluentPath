<?php
require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();
    echo "<h3>Connected to Database Successfully</h3>";

    // 1. Check if the topic column exists, if not, add it.
    $checkColumn = $conn->query("SHOW COLUMNS FROM phrases LIKE 'topic'");
    if ($checkColumn->rowCount() === 0) {
        $conn->exec("ALTER TABLE phrases ADD COLUMN topic VARCHAR(50) DEFAULT 'General'");
        echo "<p>Added 'topic' column to the 'phrases' table.</p>";
    } else {
        echo "<p>'topic' column already exists.</p>";
    }

    // 2. Fetch all phrases
    $stmt = $conn->query("SELECT id, correct_answer FROM phrases");
    $phrases = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $updateStmt = $conn->prepare("UPDATE phrases SET topic = :topic WHERE id = :id");
    
    $foodKeywords = ['eat', 'eating', 'hungry', 'sweet', 'bitter', 'sour', 'spicy', 'food', 'drink', 'feast', 'cook', 'coconut', 'water', 'spoon', 'spoilt', 'taste', 'delicious'];
    $travelKeywords = ['go', 'going', 'where', 'walk', 'bridge', 'bicycle', 'car', 'travel', 'journey', 'come', 'coming', 'climb', 'chase', 'run', 'fast', 'slow'];
    $greetingKeywords = ['how are', 'what are', 'hello', 'good', 'morning', 'night', 'doing', 'things', 'you', 'me', 'i', 'we', 'they', 'he', 'she', 'why', 'tell', 'yes', 'no', 'boy', 'man', 'girl', 'woman', 'brother', 'sister', 'child', 'children'];
    
    $counts = ['Greetings' => 0, 'Food' => 0, 'Travel' => 0, 'General' => 0];

    foreach ($phrases as $phrase) {
        $meaning = $phrase['correct_answer'];
        $topic = 'General';
        
        // Check Food
        foreach ($foodKeywords as $kw) {
            if (preg_match("/\b" . preg_quote(trim($kw), '/') . "\b/i", $meaning)) {
                $topic = 'Food';
                break;
            }
        }
        
        // Check Travel
        if ($topic === 'General') {
            foreach ($travelKeywords as $kw) {
                if (preg_match("/\b" . preg_quote(trim($kw), '/') . "\b/i", $meaning)) {
                    $topic = 'Travel';
                    break;
                }
            }
        }
        
        // Check Greetings
        if ($topic === 'General') {
            foreach ($greetingKeywords as $kw) {
                if (preg_match("/\b" . preg_quote(trim($kw), '/') . "\b/i", $meaning)) {
                    $topic = 'Greetings';
                    break;
                }
            }
        }
        
        // Update DB
        $updateStmt->execute([':topic' => $topic, ':id' => $phrase['id']]);
        $counts[$topic]++;
    }
    
    echo "<h3>Topics Successfully Synced on Azure!</h3>";
    echo "<ul>";
    foreach($counts as $category => $count) {
        echo "<li><strong>$category:</strong> $count phrases</li>";
    }
    echo "</ul>";
    
    echo "<p><em>You can now safely delete this file from your server.</em></p>";
    
} catch(PDOException $e) {
    echo "<h3>Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
}
