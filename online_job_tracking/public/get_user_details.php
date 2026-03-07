<?php
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security check: Only managers can access this
if ($_SESSION['role_id'] != 1) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit();
}

try {
    // Get user details
    $stmt = $conn->prepare("
        SELECT u.*, r.Role_Name 
        FROM tbl_users u 
        JOIN tbl_roles r ON u.Role_ID = r.Role_ID 
        WHERE u.User_ID = ?
    ");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'User not found']);
        exit();
    }
    
    // Get user statistics
    $apps = $conn->prepare("SELECT COUNT(*) FROM tbl_applications WHERE User_ID = ?");
    $apps->execute([$user_id]);
    $applications = $apps->fetchColumn();
    
    $jobs = $conn->prepare("SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = ?");
    $jobs->execute([$user_id]);
    $jobCount = $jobs->fetchColumn();
    
    $saved = $conn->prepare("SELECT COUNT(*) FROM tbl_saved_jobs WHERE User_ID = ?");
    $saved->execute([$user_id]);
    $savedCount = $saved->fetchColumn();
    
    echo json_encode([
        'success' => true,
        'user' => $user,
        'stats' => [
            'applications' => $applications,
            'jobs' => $jobCount,
            'saved' => $savedCount
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
?>