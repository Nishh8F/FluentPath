<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();

$host = "localhost;port=3307";
$username = "root";
$password = "";
$database = "fluentpath";

try {
    $conn = new PDO("mysql:host=$host;dbname=$database", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $action = $_GET['action'] ?? '';

    // Helper function to get progress
    function getUserProgress($conn, $userId) {
        $stmt = $conn->prepare("SELECT lang_code, progress_percent FROM user_progress WHERE user_id = ?");
        $stmt->execute([$userId]);
        $progress = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $progress[$row['lang_code']] = (int)$row['progress_percent'];
        }
        return $progress;
    }

    if ($action === 'register') {
        $user = isset($_POST['username']) ? htmlspecialchars(strip_tags(trim($_POST['username']))) : '';
        $name = isset($_POST['name']) ? htmlspecialchars(strip_tags(trim($_POST['name']))) : null;
        $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($user) || empty($pass)) {
            echo json_encode(["error" => "Username and password are required."]);
            exit;
        }

        if (strpos($user, ' ') !== false) {
            echo json_encode(["error" => "Username cannot contain spaces."]);
            exit;
        }

        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
        $stmt->execute([$user]);
        if ($stmt->fetchColumn() > 0) {
            echo json_encode(["error" => "Username is already taken."]);
            exit;
        }

        $hashedPassword = password_hash($pass, PASSWORD_DEFAULT);
        $insertStmt = $conn->prepare("INSERT INTO users (username, password, name) VALUES (?, ?, ?)");
        $insertStmt->execute([$user, $hashedPassword, $name]);

        $_SESSION['user_id'] = $conn->lastInsertId();
        $_SESSION['username'] = $user;

        echo json_encode([
            "success" => true, 
            "user" => [
                "id" => $_SESSION['user_id'], 
                "username" => $user,
                "name" => $name,
                "bio" => null,
                "birthday" => null,
                "profile_picture" => null,
                "progress" => []
            ]
        ]);
        exit;
    }

    if ($action === 'login') {
        $user = isset($_POST['username']) ? htmlspecialchars(strip_tags(trim($_POST['username']))) : '';
        $pass = isset($_POST['password']) ? trim($_POST['password']) : '';

        if (empty($user) || empty($pass)) {
            echo json_encode(["error" => "Username and password are required."]);
            exit;
        }

        if (strpos($user, ' ') !== false) {
            echo json_encode(["error" => "Username cannot contain spaces."]);
            exit;
        }

        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && password_verify($pass, $userData['password'])) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            
            // Remove password from response
            unset($userData['password']);
            
            $userData['progress'] = getUserProgress($conn, $userData['id']);
            
            echo json_encode(["success" => true, "user" => $userData]);
        } else {
            echo json_encode(["error" => "Invalid username or password."]);
        }
        exit;
    }

    if ($action === 'logout') {
        session_destroy();
        echo json_encode(["success" => true]);
        exit;
    }

    if ($action === 'check') {
        if (isset($_SESSION['user_id'])) {
            $stmt = $conn->prepare("SELECT id, username, name, bio, birthday, profile_picture FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData) {
                $userData['progress'] = getUserProgress($conn, $userData['id']);
                echo json_encode(["success" => true, "user" => $userData]);
            } else {
                echo json_encode(["success" => false, "message" => "User not found."]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Not logged in."]);
        }
        exit;
    }

    if ($action === 'save_progress') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["error" => "Not authenticated."]);
            exit;
        }

        $userId = $_SESSION['user_id'];
        // Use either GET or POST for lang parameter
        $langCode = isset($_POST['lang']) ? $_POST['lang'] : (isset($_GET['lang']) ? $_GET['lang'] : '');
        $increment = isset($_POST['increment']) ? (int)$_POST['increment'] : (isset($_GET['increment']) ? (int)$_GET['increment'] : 10);
        
        if (empty($langCode)) {
            echo json_encode(["error" => "Language code is required."]);
            exit;
        }
        
        // Fetch current progress
        $stmt = $conn->prepare("SELECT progress_percent FROM user_progress WHERE user_id = ? AND lang_code = ?");
        $stmt->execute([$userId, $langCode]);
        $currentProgress = $stmt->fetchColumn();

        if ($currentProgress !== false) {
            $newProgress = min(100, (int)$currentProgress + $increment);
            $updateStmt = $conn->prepare("UPDATE user_progress SET progress_percent = ? WHERE user_id = ? AND lang_code = ?");
            $updateStmt->execute([$newProgress, $userId, $langCode]);
        } else {
            $newProgress = min(100, $increment);
            $insertStmt = $conn->prepare("INSERT INTO user_progress (user_id, lang_code, progress_percent) VALUES (?, ?, ?)");
            $insertStmt->execute([$userId, $langCode, $newProgress]);
        }

        echo json_encode(["success" => true, "progress" => getUserProgress($conn, $userId)]);
        exit;
    }

    if ($action === 'update_profile') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["error" => "Not authenticated."]);
            exit;
        }

        $userId = $_SESSION['user_id'];
        $newUsername = isset($_POST['username']) ? htmlspecialchars(strip_tags(trim($_POST['username']))) : '';
        $name = isset($_POST['name']) ? htmlspecialchars(strip_tags(trim($_POST['name']))) : null;
        $bio = isset($_POST['bio']) ? htmlspecialchars(strip_tags(trim($_POST['bio']))) : null;
        $birthday = isset($_POST['birthday']) ? trim($_POST['birthday']) : null;

        if (empty($newUsername)) {
            echo json_encode(["error" => "Username cannot be empty."]);
            exit;
        }

        $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
        $stmt->execute([$newUsername, $userId]);
        if ($stmt->fetch()) {
            echo json_encode(["error" => "Username is already taken."]);
            exit;
        }

        $profilePicturePath = null;
        $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $currentPic = $stmt->fetchColumn();
        $profilePicturePath = $currentPic;

        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = strtolower(pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION));
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (in_array($fileExtension, $allowedExtensions)) {
                $newFileName = 'profile_' . $userId . '_' . time() . '.' . $fileExtension;
                $targetFile = $uploadDir . $newFileName;
                
                if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $targetFile)) {
                    $profilePicturePath = $targetFile;
                    if ($currentPic && file_exists($currentPic) && $currentPic !== $targetFile) {
                        unlink($currentPic);
                    }
                } else {
                    echo json_encode(["error" => "Failed to upload image."]);
                    exit;
                }
            } else {
                echo json_encode(["error" => "Invalid image format. Only JPG, PNG, and GIF are allowed."]);
                exit;
            }
        }

        $updateStmt = $conn->prepare("UPDATE users SET username = ?, name = ?, bio = ?, birthday = ?, profile_picture = ? WHERE id = ?");
        $updateStmt->execute([$newUsername, $name, $bio, $birthday, $profilePicturePath, $userId]);

        $_SESSION['username'] = $newUsername;

        $stmt = $conn->prepare("SELECT id, username, name, bio, birthday, profile_picture FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $updatedUser['progress'] = getUserProgress($conn, $userId);

        echo json_encode(["success" => true, "user" => $updatedUser]);
        exit;
    }

    echo json_encode(["error" => "Invalid action."]);

} catch(PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
