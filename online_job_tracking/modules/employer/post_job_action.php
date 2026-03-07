<?php
session_start();
require_once '../../config/db_connect.php';

// Security check: Only employers can post jobs
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    $_SESSION['error_message'] = "Unauthorized access. Only employers can post jobs.";
    header("Location: ../../view/auth/login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $job_title = trim($_POST['title'] ?? '');
    $job_category = $_POST['category'] ?? '';
    $job_type = $_POST['job_type'] ?? 'Full Time';
    $location = trim($_POST['location'] ?? '');
    $salary_min = !empty($_POST['salary_min']) ? (float)$_POST['salary_min'] : null;
    $salary_max = !empty($_POST['salary_max']) ? (float)$_POST['salary_max'] : null;
    $experience = $_POST['experience'] ?? '';
    $deadline = !empty($_POST['deadline']) ? $_POST['deadline'] : null;
    $description = trim($_POST['description'] ?? '');
    $requirements = trim($_POST['requirements'] ?? '');
    $benefits = trim($_POST['benefits'] ?? '');
    $status = isset($_POST['status']) ? 'Active' : 'Draft';
    
    // Validate required fields
    $errors = [];
    if (empty($job_title)) $errors[] = "Job title is required";
    if (empty($job_category)) $errors[] = "Job category is required";
    if (empty($description)) $errors[] = "Job description is required";
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: ../../view/employer/post_job.php");
        exit();
    }
    
    try {
        // Insert job into database
        $sql = "INSERT INTO tbl_jobs (
            Job_Title, Job_Description, Requirements, Benefits, Job_Category, 
            Job_Type, Location, Salary_Min, Salary_Max, Experience_Level,
            Application_Deadline, Employer_ID, Status, Posted_Date
        ) VALUES (
            :title, :description, :requirements, :benefits, :category,
            :job_type, :location, :salary_min, :salary_max, :experience,
            :deadline, :employer_id, :status, NOW()
        )";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':title' => $job_title,
            ':description' => $description,
            ':requirements' => $requirements,
            ':benefits' => $benefits,
            ':category' => $job_category,
            ':job_type' => $job_type,
            ':location' => $location,
            ':salary_min' => $salary_min,
            ':salary_max' => $salary_max,
            ':experience' => $experience,
            ':deadline' => $deadline,
            ':employer_id' => $employer_id,
            ':status' => $status
        ]);
        
        $job_id = $conn->lastInsertId();
        
        // Get employer company name for notification
        $emp = $conn->prepare("SELECT Company_Name FROM tbl_employer_profiles WHERE Employer_ID = ?");
        $emp->execute([$employer_id]);
        $company = $emp->fetch(PDO::FETCH_ASSOC);
        $company_name = $company['Company_Name'] ?? 'A company';
        
        // Send notifications to all job seekers
        $notify_sql = "INSERT INTO tbl_notifications (User_ID, Notification_Type, Title, Message, Link, Created_Date)
                       SELECT User_ID, 'job', :title, :message, :link, NOW()
                       FROM tbl_users WHERE Role_ID = 3"; // Role_ID 3 = Job Seekers
        
        $notify_msg = "New job posted: " . $job_title . " at " . $company_name;
        $notify_link = "../../view/job_seeker/apply_job.php?id=" . $job_id;
        
        $notify_stmt = $conn->prepare($notify_sql);
        $notify_stmt->execute([
            ':title' => "New Job Opportunity: " . $job_title,
            ':message' => $notify_msg,
            ':link' => $notify_link
        ]);
        
        // Also send email notifications (optional - you can implement later)
        // You can add email sending functionality here
        
        $_SESSION['success_message'] = "Job posted successfully! Notifications sent to all job seekers.";
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error posting job: " . $e->getMessage();
        error_log("Post job error: " . $e->getMessage());
    }
    
    header("Location: ../../view/employer/employer_dashboard.php");
    exit();
    
} else {
    // Not a POST request
    header("Location: ../../view/employer/post_job.php");
    exit();
}
?>