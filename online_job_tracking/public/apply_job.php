<?php
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security: Only Job Seekers can access this page
if ($_SESSION['role_id'] != 3) {
    $_SESSION['error_message'] = "Access denied. Only job seekers can apply for jobs.";
    header("Location: ../public/login.php");
    exit();
}

$job_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['apply'])) {
    $job_id = (int)$_POST['job_id'];
    $cover_letter = trim($_POST['cover_letter'] ?? '');
    $expected_salary = !empty($_POST['expected_salary']) ? (float)$_POST['expected_salary'] : null;
    $available_start_date = !empty($_POST['available_start_date']) ? $_POST['available_start_date'] : null;
    
    // Handle resume upload
    $resume_path = null;
    if (isset($_FILES['resume']) && $_FILES['resume']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        $file_type = $_FILES['resume']['type'];
        
        if (in_array($file_type, $allowed_types) && $_FILES['resume']['size'] <= 5 * 1024 * 1024) {
            $upload_dir = '../uploads/resumes/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['resume']['name'], PATHINFO_EXTENSION);
            $resume_path = 'uploads/resumes/' . $user_id . '_' . time() . '.' . $file_extension;
            $full_path = '../' . $resume_path;
            
            move_uploaded_file($_FILES['resume']['tmp_name'], $full_path);
        }
    }
    
    try {
        // Check if already applied
        $check = $conn->prepare("SELECT * FROM tbl_applications WHERE User_ID = ? AND Job_ID = ?");
        $check->execute([$user_id, $job_id]);
        
        if ($check->rowCount() > 0) {
            $_SESSION['error_message'] = "You have already applied for this job.";
        } else {
            $sql = "INSERT INTO tbl_applications (User_ID, Job_ID, Cover_Letter, Expected_Salary, Available_Start_Date, Resume_Path, Application_Status) 
                    VALUES (?, ?, ?, ?, ?, ?, 'Pending')";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $job_id, $cover_letter, $expected_salary, $available_start_date, $resume_path]);
            
            $_SESSION['success_message'] = "Application submitted successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error submitting application: " . $e->getMessage();
        error_log("Application error: " . $e->getMessage());
    }
    
    header("Location: jobseeker_dashboard.php");
    exit();
}
?>