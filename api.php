<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();
} catch(PDOException $exception) {
    echo json_encode(["error" => "Connection error: " . $exception->getMessage()]);
    exit;
}

// Get the language code from the app and sanitize it
$lang = isset($_GET['lang']) ? htmlspecialchars(strip_tags(strtolower(trim($_GET['lang'])))) : '';

if (!$lang) {
    echo json_encode(["error" => "No language specified."]);
    exit;
}

// Fetch 10 random phrases for the selected language
$query = "SELECT * FROM phrases WHERE lang_code = :lang_code ORDER BY RAND() LIMIT 10";
$stmt = $conn->prepare($query);
$stmt->bindParam(':lang_code', $lang);
$stmt->execute();

$lessons = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Group all options together
    $options = [
        $row['correct_answer'],
        $row['wrong_1'],
        $row['wrong_2'],
        $row['wrong_3']
    ];
    
    // Shuffle the options so the correct answer is in a random position
    shuffle($options);
    
    // Find the new index (0-3) of the correct answer after shuffling
    $correctIndex = array_search($row['correct_answer'], $options);

    // Build the final array for React
    $lessons[] = [
        "id" => $row['id'],
        "phrase" => $row['phrase'],
        "options" => $options,
        "correctIndex" => $correctIndex
    ];
}

// Return the data to the app
echo json_encode($lessons);
?>