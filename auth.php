<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

session_start();

require_once __DIR__ . '/config.php';

try {
    $conn = getDBConnection();

    $action = $_GET['action'] ?? '';

    // Helper function to get progress
    function getUserProgress($conn, $userId) {
        $stmt = $conn->prepare("SELECT l.code AS lang_code, up.progress_percent FROM user_progress up JOIN languages l ON up.language_id = l.id WHERE up.user_id = ?");
        $stmt->execute([$userId]);
        $progress = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $progress[$row['lang_code']] = (int)$row['progress_percent'];
        }
        return $progress;
    }

    function getUserBadges($conn, $userId) {
        $stmt = $conn->prepare("SELECT title, description, icon, earned_at FROM user_badges WHERE user_id = ? ORDER BY earned_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    function getUserMilestones($conn, $userId) {
        $stmt = $conn->prepare("SELECT title, description, icon, created_at FROM user_milestones WHERE user_id = ? ORDER BY created_at DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
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

        $stmt = $conn->prepare("SELECT id, username, password, name, bio, birthday, profile_picture, lessons_done, total_xp, daily_xp, current_streak, last_activity_date FROM users WHERE username = ?");
        $stmt->execute([$user]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($userData && password_verify($pass, $userData['password'])) {
            $_SESSION['user_id'] = $userData['id'];
            $_SESSION['username'] = $userData['username'];
            
            // Remove password from response
            unset($userData['password']);
            
            $userData['progress'] = getUserProgress($conn, $userData['id']);
            $userData['badges'] = getUserBadges($conn, $userData['id']);
            $userData['milestones'] = getUserMilestones($conn, $userData['id']);
            
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
            $stmt = $conn->prepare("SELECT id, username, name, bio, birthday, profile_picture, lessons_done, total_xp, daily_xp, current_streak, last_activity_date FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $userData = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($userData) {
                $userData['progress'] = getUserProgress($conn, $userData['id']);
                $userData['badges'] = getUserBadges($conn, $userData['id']);
                $userData['milestones'] = getUserMilestones($conn, $userData['id']);
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
        
        // Fetch language_id for the langCode
        $langStmt = $conn->prepare("SELECT id FROM languages WHERE code = ?");
        $langStmt->execute([$langCode]);
        $languageId = $langStmt->fetchColumn();
        
        if ($languageId === false) {
            echo json_encode(["error" => "Invalid language code."]);
            exit;
        }
        
        // Fetch current progress
        $stmt = $conn->prepare("SELECT progress_percent FROM user_progress WHERE user_id = ? AND language_id = ?");
        $stmt->execute([$userId, $languageId]);
        $currentProgress = $stmt->fetchColumn();

        if ($currentProgress !== false) {
            $newProgress = min(100, (int)$currentProgress + $increment);
            $updateStmt = $conn->prepare("UPDATE user_progress SET progress_percent = ? WHERE user_id = ? AND language_id = ?");
            $updateStmt->execute([$newProgress, $userId, $languageId]);
        } else {
            $newProgress = min(100, $increment);
            $insertStmt = $conn->prepare("INSERT INTO user_progress (user_id, language_id, progress_percent) VALUES (?, ?, ?)");
            $insertStmt->execute([$userId, $languageId, $newProgress]);
        }

        // Stats tracking
        $stmt = $conn->prepare("SELECT lessons_done, total_xp, daily_xp, current_streak, last_activity_date FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $stats = $stmt->fetch(PDO::FETCH_ASSOC);

        $today = date('Y-m-d');
        $yesterday = date('Y-m-d', strtotime('-1 day'));

        $lessons_done = (int)$stats['lessons_done'] + 1;
        $total_xp = (int)$stats['total_xp'] + $increment;
        $daily_xp = (int)$stats['daily_xp'];
        $current_streak = (int)$stats['current_streak'];
        $last_activity = $stats['last_activity_date'];

        if ($last_activity === $today) {
            $daily_xp += $increment;
        } else if ($last_activity === $yesterday) {
            $daily_xp = $increment;
            $current_streak += 1;
        } else {
            $daily_xp = $increment;
            $current_streak = 1;
        }

        $updateUser = $conn->prepare("UPDATE users SET lessons_done = ?, total_xp = ?, daily_xp = ?, current_streak = ?, last_activity_date = ? WHERE id = ?");
        $updateUser->execute([$lessons_done, $total_xp, $daily_xp, $current_streak, $today, $userId]);

        // Auto-award Milestones and Badges
        if ($lessons_done === 1) {
            $stmt = $conn->prepare("INSERT INTO user_milestones (user_id, title, description, icon) VALUES (?, 'First Lesson Completed!', 'You finished your very first lesson.', 'fa-check')");
            $stmt->execute([$userId]);
        }

        if ($current_streak === 3) {
            $stmt = $conn->prepare("SELECT id FROM user_badges WHERE user_id = ? AND title = 'Habit Builder'");
            $stmt->execute([$userId]);
            if (!$stmt->fetch()) {
                $bStmt = $conn->prepare("INSERT INTO user_badges (user_id, title, description, icon) VALUES (?, 'Habit Builder', '3 Days Streak', 'fa-leaf')");
                $bStmt->execute([$userId]);
                
                $mStmt = $conn->prepare("INSERT INTO user_milestones (user_id, title, description, icon) VALUES (?, 'Earned Habit Builder Badge', 'Achieved a 3-day learning streak.', 'fa-leaf')");
                $mStmt->execute([$userId]);
            }
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

        $stmt = $conn->prepare("SELECT id, username, name, bio, birthday, profile_picture, lessons_done, total_xp, daily_xp, current_streak, last_activity_date FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $updatedUser['progress'] = getUserProgress($conn, $userId);
        $updatedUser['badges'] = getUserBadges($conn, $userId);
        $updatedUser['milestones'] = getUserMilestones($conn, $userId);

        echo json_encode(["success" => true, "user" => $updatedUser]);
        exit;
    }

    if ($action === 'award_easter_egg') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(["error" => "Not logged in."]);
            exit;
        }

        $userId = $_SESSION['user_id'];
        
        // Check if badge already exists
        $stmt = $conn->prepare("SELECT id FROM user_badges WHERE user_id = ? AND title = 'Milk Shake'");
        $stmt->execute([$userId]);
        
        if (!$stmt->fetch()) {
            $bStmt = $conn->prepare("INSERT INTO user_badges (user_id, title, description, icon) VALUES (?, 'Milk Shake', 'You shook the app!', 'fa-blender')");
            $bStmt->execute([$userId]);
            
            $mStmt = $conn->prepare("INSERT INTO user_milestones (user_id, title, description, icon) VALUES (?, 'Found an Easter Egg', 'Earned the Milk Shake badge.', 'fa-blender')");
            $mStmt->execute([$userId]);
        }

        // Return updated user stats
        $stmt = $conn->prepare("SELECT id, username, name, bio, birthday, profile_picture, lessons_done, total_xp, daily_xp, current_streak, last_activity_date FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $updatedUser = $stmt->fetch(PDO::FETCH_ASSOC);
        $updatedUser['progress'] = getUserProgress($conn, $userId);
        $updatedUser['badges'] = getUserBadges($conn, $userId);
        $updatedUser['milestones'] = getUserMilestones($conn, $userId);

        echo json_encode(["success" => true, "user" => $updatedUser]);
        exit;
    }

    if ($action === 'get_leaderboard') {
        // Fetch top 50 users ranked by total_xp
        $stmt = $conn->prepare("
            SELECT id, username, name, profile_picture, total_xp, lessons_done, current_streak
            FROM users
            ORDER BY total_xp DESC, lessons_done DESC
            LIMIT 50
        ");
        $stmt->execute();
        $topUsers = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $currentUserRank = null;
        $currentUserData = null;

        if (isset($_SESSION['user_id'])) {
            $userId = $_SESSION['user_id'];

            // Count how many users rank above the current user
            $rankStmt = $conn->prepare("
                SELECT COUNT(*) + 1 AS user_rank
                FROM users
                WHERE total_xp > (SELECT total_xp FROM users WHERE id = ?)
            ");
            $rankStmt->execute([$userId]);
            $currentUserRank = (int)$rankStmt->fetchColumn();

            // Check if current user is already in the top list
            $inTop = false;
            foreach ($topUsers as $u) {
                if ((int)$u['id'] === $userId) {
                    $inTop = true;
                    break;
                }
            }

            // Fetch current user's own data for rank banner
            $selfStmt = $conn->prepare("SELECT id, username, name, profile_picture, total_xp, lessons_done, current_streak FROM users WHERE id = ?");
            $selfStmt->execute([$userId]);
            $currentUserData = $selfStmt->fetch(PDO::FETCH_ASSOC);
            $currentUserData['rank'] = $currentUserRank;
        }

        echo json_encode([
            "success" => true,
            "top_users" => $topUsers,
            "current_user_rank" => $currentUserRank,
            "current_user_data" => $currentUserData
        ]);
        exit;
    }

    echo json_encode(["error" => "Invalid action."]);

} catch(PDOException $e) {
    echo json_encode(["error" => "Connection failed: " . $e->getMessage()]);
}
?>
