<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security check: Ensure user is a Manager (Role 1)
if ($_SESSION['role_id'] != 1) { 
    $_SESSION['error_message'] = "Access denied. Only managers can access this page.";
    header("Location: ../public/login.php");
    exit();
}

// Handle actions via POST/GET
$action = $_GET['action'] ?? '';
$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$status = $_GET['status'] ?? '';

// Process actions
if ($action && $user_id) {
    try {
        switch($action) {
            case 'verify':
                // Check if employer profile exists
                $check = $conn->prepare("SELECT * FROM tbl_employer_profiles WHERE Employer_ID = ?");
                $check->execute([$user_id]);
                
                if ($check->rowCount() > 0) {
                    $stmt = $conn->prepare("UPDATE tbl_employer_profiles SET Verified = 1 WHERE Employer_ID = ?");
                    $stmt->execute([$user_id]);
                    $_SESSION['success_message'] = "Employer verified successfully!";
                } else {
                    // Create employer profile if it doesn't exist
                    $stmt = $conn->prepare("INSERT INTO tbl_employer_profiles (Employer_ID, Verified) VALUES (?, 1)");
                    $stmt->execute([$user_id]);
                    $_SESSION['success_message'] = "Employer profile created and verified!";
                }
                break;
                
            case 'suspend':
                // Add a status field to tbl_users if not exists
                // First check if status column exists
                $checkColumn = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'Status'");
                if ($checkColumn->rowCount() == 0) {
                    $conn->exec("ALTER TABLE tbl_users ADD COLUMN Status ENUM('Active', 'Suspended', 'Inactive') DEFAULT 'Active'");
                }
                
                $newStatus = ($status == 'suspend') ? 'Suspended' : 'Active';
                $stmt = $conn->prepare("UPDATE tbl_users SET Status = ? WHERE User_ID = ?");
                $stmt->execute([$newStatus, $user_id]);
                
                $message = ($newStatus == 'Suspended') ? "User suspended successfully!" : "User activated successfully!";
                $_SESSION['success_message'] = $message;
                break;
                
            case 'delete':
                // Check if user has related records
                $checkApps = $conn->prepare("SELECT COUNT(*) FROM tbl_applications WHERE User_ID = ?");
                $checkApps->execute([$user_id]);
                $appCount = $checkApps->fetchColumn();
                
                $checkJobs = $conn->prepare("SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = ?");
                $checkJobs->execute([$user_id]);
                $jobCount = $checkJobs->fetchColumn();
                
                if ($appCount > 0 || $jobCount > 0) {
                    $_SESSION['error_message'] = "Cannot delete user with existing applications or job postings. Consider suspending instead.";
                } else {
                    $stmt = $conn->prepare("DELETE FROM tbl_users WHERE User_ID = ?");
                    $stmt->execute([$user_id]);
                    $_SESSION['success_message'] = "User deleted successfully!";
                }
                break;
                
            case 'bulk_action':
                $selected_users = $_POST['selected_users'] ?? [];
                $bulk_action = $_POST['bulk_action'] ?? '';
                
                if (!empty($selected_users) && $bulk_action) {
                    $placeholders = implode(',', array_fill(0, count($selected_users), '?'));
                    
                    switch($bulk_action) {
                        case 'verify':
                            $stmt = $conn->prepare("UPDATE tbl_employer_profiles SET Verified = 1 WHERE Employer_ID IN ($placeholders)");
                            $stmt->execute($selected_users);
                            $_SESSION['success_message'] = "Selected employers verified successfully!";
                            break;
                            
                        case 'suspend':
                            $stmt = $conn->prepare("UPDATE tbl_users SET Status = 'Suspended' WHERE User_ID IN ($placeholders)");
                            $stmt->execute($selected_users);
                            $_SESSION['success_message'] = "Selected users suspended successfully!";
                            break;
                            
                        case 'activate':
                            $stmt = $conn->prepare("UPDATE tbl_users SET Status = 'Active' WHERE User_ID IN ($placeholders)");
                            $stmt->execute($selected_users);
                            $_SESSION['success_message'] = "Selected users activated successfully!";
                            break;
                            
                        case 'delete':
                            $_SESSION['error_message'] = "Bulk delete is disabled for safety. Please delete users individually.";
                            break;
                    }
                }
                break;
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error processing action: " . $e->getMessage();
        error_log("Manager action error: " . $e->getMessage());
    }
    
    // Redirect to refresh page and prevent form resubmission
    header("Location: manager_dashboard.php");
    exit();
}

// Get statistics with error handling
try {
    // Check if status column exists
    $statusColumnExists = $conn->query("SHOW COLUMNS FROM tbl_users LIKE 'Status'")->rowCount() > 0;
    
    $totalUsers = $conn->query("SELECT COUNT(*) FROM tbl_users")->fetchColumn();
    $totalJobSeekers = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Role_ID = 3")->fetchColumn();
    $totalEmployers = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Role_ID = 2")->fetchColumn();
    $totalManagers = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Role_ID = 1")->fetchColumn();
    $activeJobs = $conn->query("SELECT COUNT(*) FROM tbl_jobs WHERE Status = 'Active'")->fetchColumn();
    $closedJobs = $conn->query("SELECT COUNT(*) FROM tbl_jobs WHERE Status = 'Closed'")->fetchColumn();
    $totalApplications = $conn->query("SELECT COUNT(*) FROM tbl_applications")->fetchColumn();
    $pendingApplications = $conn->query("SELECT COUNT(*) FROM tbl_applications WHERE Application_Status = 'Pending'")->fetchColumn();
    
    // Get unverified employers
    $unverifiedQuery = "SELECT COUNT(DISTINCT u.User_ID) FROM tbl_users u 
                        LEFT JOIN tbl_employer_profiles ep ON u.User_ID = ep.Employer_ID 
                        WHERE u.Role_ID = 2 AND (ep.Verified IS NULL OR ep.Verified = 0)";
    $unverifiedEmployers = $conn->query($unverifiedQuery)->fetchColumn() ?: 0;
    
    // Recent activity counts
    $recentUsers = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Created_At >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    $recentJobs = $conn->query("SELECT COUNT(*) FROM tbl_jobs WHERE Posted_Date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    $recentApplications = $conn->query("SELECT COUNT(*) FROM tbl_applications WHERE Application_Date >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetchColumn();
    
    // Get recent activities
    $recentActivities = $conn->query("
        (SELECT 'user' as type, User_ID as id, Full_Name as title, 'New user registered' as description, Created_At as date 
         FROM tbl_users ORDER BY Created_At DESC LIMIT 5)
        UNION ALL
        (SELECT 'job' as type, Job_ID as id, Job_Title as title, 'New job posted' as description, Posted_Date as date 
         FROM tbl_jobs ORDER BY Posted_Date DESC LIMIT 5)
        UNION ALL
        (SELECT 'application' as type, Application_ID as id, 'New application' as title, 'Application submitted' as description, Application_Date as date 
         FROM tbl_applications ORDER BY Application_Date DESC LIMIT 5)
        ORDER BY date DESC LIMIT 10
    ")->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    error_log("Manager dashboard error: " . $e->getMessage());
    // Set default values if queries fail
    $totalUsers = $conn->query("SELECT COUNT(*) FROM tbl_users")->fetchColumn() ?: 0;
    $totalJobSeekers = 0;
    $totalEmployers = 0;
    $totalManagers = 0;
    $activeJobs = 0;
    $closedJobs = 0;
    $totalApplications = 0;
    $pendingApplications = 0;
    $unverifiedEmployers = 0;
    $recentUsers = 0;
    $recentJobs = 0;
    $recentApplications = 0;
    $recentActivities = [];
}

include '../includes/header.php'; 
?>

<style>
/* Modern Manager Dashboard Styles - COMPLETE CSS */
:root {
    --primary: #2c3e50;
    --primary-light: #34495e;
    --secondary: #3498db;
    --accent: #e74c3c;
    --success: #27ae60;
    --warning: #f39c12;
    --info: #3498db;
    --dark: #2c3e50;
    --light: #ecf0f1;
    --gray: #7f8c8d;
    --gradient: linear-gradient(135deg, #2c3e50, #3498db);
    --gradient-success: linear-gradient(135deg, #27ae60, #2ecc71);
    --gradient-warning: linear-gradient(135deg, #f39c12, #f1c40f);
    --gradient-danger: linear-gradient(135deg, #e74c3c, #c0392b);
    --gradient-info: linear-gradient(135deg, #3498db, #2980b9);
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.1);
    --shadow-md: 0 5px 20px rgba(0,0,0,0.15);
    --shadow-lg: 0 10px 30px rgba(0,0,0,0.2);
}

* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    color: #333;
}

/* Dashboard Wrapper */
.dashboard-wrapper {
    min-height: calc(100vh - 200px);
    padding: 30px 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background */
.bg-shape {
    position: absolute;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    pointer-events: none;
}

.shape-1 {
    width: 500px;
    height: 500px;
    top: -250px;
    right: -250px;
    animation: float 20s infinite;
}

.shape-2 {
    width: 400px;
    height: 400px;
    bottom: -200px;
    left: -200px;
    animation: float 15s infinite reverse;
}

.shape-3 {
    width: 300px;
    height: 300px;
    top: 40%;
    left: 10%;
    animation: float 18s infinite 2s;
}

/* Dashboard Container */
.dashboard-container {
    max-width: 1400px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
    animation: fadeIn 0.8s ease-out;
}

/* Header Section */
.dashboard-header {
    background: white;
    border-radius: 30px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.welcome-section {
    display: flex;
    align-items: center;
    gap: 20px;
}

.admin-avatar {
    width: 80px;
    height: 80px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2.5rem;
    color: white;
    box-shadow: var(--shadow-md);
}

.welcome-text h1 {
    font-size: 2rem;
    color: var(--dark);
    margin-bottom: 5px;
}

.welcome-text p {
    color: var(--gray);
    display: flex;
    align-items: center;
    gap: 5px;
}

.welcome-text p i {
    color: var(--secondary);
}

.header-actions {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

/* Button Styles */
.btn {
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 600;
    font-size: 1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    border: none;
    cursor: pointer;
}

.btn-primary {
    background: var(--gradient);
    color: white;
    box-shadow: var(--shadow-sm);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.btn-success {
    background: var(--gradient-success);
    color: white;
}

.btn-success:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(39, 174, 96, 0.3);
}

.btn-warning {
    background: var(--gradient-warning);
    color: white;
}

.btn-warning:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(243, 156, 18, 0.3);
}

.btn-danger {
    background: var(--gradient-danger);
    color: white;
}

.btn-danger:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(231, 76, 60, 0.3);
}

.btn-info {
    background: var(--gradient-info);
    color: white;
}

.btn-info:hover {
    transform: translateY(-3px);
    box-shadow: 0 5px 20px rgba(52, 152, 219, 0.3);
}

.btn-outline {
    background: transparent;
    border: 2px solid var(--secondary);
    color: var(--secondary);
}

.btn-outline:hover {
    background: var(--secondary);
    color: white;
    transform: translateY(-3px);
}

.btn-sm {
    padding: 8px 16px;
    font-size: 0.9rem;
}

/* Stats Grid - Main */
.stats-grid-main {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 20px;
    border: 1px solid rgba(255, 255, 255, 0.2);
    position: relative;
    overflow: hidden;
}

.stat-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    width: 4px;
    height: 100%;
    background: var(--primary);
}

.stat-card.blue::before { background: var(--secondary); }
.stat-card.green::before { background: var(--success); }
.stat-card.yellow::before { background: var(--warning); }
.stat-card.red::before { background: var(--accent); }
.stat-card.purple::before { background: #9b59b6; }

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 70px;
    height: 70px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: white;
}

.stat-icon.blue { background: var(--gradient-info); }
.stat-icon.green { background: var(--gradient-success); }
.stat-icon.yellow { background: var(--gradient-warning); }
.stat-icon.red { background: var(--gradient-danger); }
.stat-icon.purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.stat-details h3 {
    font-size: 2rem;
    color: var(--dark);
    margin-bottom: 5px;
}

.stat-details p {
    color: var(--gray);
    font-size: 0.95rem;
    margin-bottom: 5px;
}

.stat-trend {
    font-size: 0.85rem;
    display: flex;
    align-items: center;
    gap: 3px;
}

.trend-up { color: var(--success); }
.trend-down { color: var(--accent); }

/* Stats Grid - Secondary */
.stats-grid-secondary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-mini-card {
    background: white;
    border-radius: 15px;
    padding: 20px;
    box-shadow: var(--shadow-sm);
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s ease;
}

.stat-mini-card:hover {
    transform: translateY(-3px);
    box-shadow: var(--shadow-md);
}

.mini-icon {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
}

.mini-icon.blue { background: var(--gradient-info); }
.mini-icon.green { background: var(--gradient-success); }
.mini-icon.orange { background: var(--gradient-warning); }
.mini-icon.purple { background: linear-gradient(135deg, #9b59b6, #8e44ad); }

.mini-details h4 {
    font-size: 1.5rem;
    color: var(--dark);
    margin: 0;
}

.mini-details p {
    color: var(--gray);
    font-size: 0.85rem;
    margin: 0;
}

/* Main Content Grid */
.main-content-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 25px;
    margin-bottom: 30px;
}

@media (max-width: 992px) {
    .main-content-grid {
        grid-template-columns: 1fr;
    }
}

/* Card Styles */
.card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    transition: all 0.3s ease;
    margin-bottom: 25px;
}

.card:hover {
    box-shadow: var(--shadow-lg);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.card-header h3 {
    color: var(--dark);
    font-size: 1.3rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.card-header h3 i {
    color: var(--secondary);
}

.card-header a {
    color: var(--secondary);
    text-decoration: none;
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.card-header a:hover {
    color: var(--primary);
    transform: translateX(5px);
}

/* Bulk Actions */
.bulk-actions {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.bulk-actions select {
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 0.95rem;
    min-width: 150px;
    background: white;
}

.btn-bulk {
    background: var(--gradient);
    color: white;
    padding: 8px 20px;
    border-radius: 5px;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-bulk:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

.select-all {
    display: flex;
    align-items: center;
    gap: 5px;
    cursor: pointer;
}

.select-all input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--secondary);
}

/* Search Box */
.search-box {
    position: relative;
    margin-bottom: 20px;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 1.2rem;
}

#userSearch {
    width: 100%;
    padding: 15px 15px 15px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

#userSearch:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

/* Filter Section */
.filter-section {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 15px;
    align-items: center;
}

.filter-group {
    display: flex;
    align-items: center;
    gap: 8px;
}

.filter-group label {
    color: var(--gray);
    font-size: 0.9rem;
    font-weight: 600;
}

.filter-select {
    padding: 8px 12px;
    border: 2px solid #e0e0e0;
    border-radius: 5px;
    font-size: 0.9rem;
    background: white;
    cursor: pointer;
    min-width: 120px;
}

/* Table Styles */
.table-responsive {
    overflow-x: auto;
    margin-top: 20px;
}

table {
    width: 100%;
    border-collapse: collapse;
    font-size: 0.95rem;
}

th {
    text-align: left;
    padding: 15px 10px;
    color: var(--gray);
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    border-bottom: 2px solid #f0f0f0;
    background: #f8fafc;
}

td {
    padding: 15px 10px;
    border-bottom: 1px solid #f0f0f0;
    color: var(--dark);
}

tr {
    transition: background 0.3s ease;
}

tr:hover td {
    background: #f8f9fa;
}

/* Role Badges */
.role-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.role-manager {
    background: #e8f0fe;
    color: var(--secondary);
}

.role-employer {
    background: #d4edda;
    color: #155724;
}

.role-seeker {
    background: #fff3cd;
    color: #856404;
}

/* Status Badges */
.status-badge {
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
    display: inline-block;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-verified {
    background: #d1ecf1;
    color: #0c5460;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    border: none;
    cursor: pointer;
    color: white;
}

.btn-action.view {
    background: var(--gradient-info);
}

.btn-action.edit {
    background: var(--gradient-warning);
}

.btn-action.delete {
    background: var(--gradient-danger);
}

.btn-action.verify {
    background: var(--gradient-success);
}

.btn-action.suspend {
    background: #6c757d;
}

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

/* Activity List */
.activity-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.activity-item:hover {
    background: #e8f0fe;
    transform: translateX(5px);
}

.activity-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--secondary);
    font-size: 1.2rem;
}

.activity-details {
    flex: 1;
}

.activity-details p {
    color: var(--dark);
    margin-bottom: 3px;
}

.activity-time {
    color: var(--gray);
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 3px;
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
    margin-top: 20px;
}

.quick-action-item {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 20px;
    text-align: center;
    text-decoration: none;
    transition: all 0.3s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    color: var(--dark);
}

.quick-action-item:hover {
    background: var(--gradient);
    transform: translateY(-5px);
    color: white;
}

.quick-action-item:hover i,
.quick-action-item:hover span {
    color: white;
}

.quick-action-item i {
    font-size: 1.8rem;
    color: var(--secondary);
    transition: all 0.3s ease;
}

.quick-action-item span {
    font-weight: 600;
    transition: all 0.3s ease;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 10px;
    margin-bottom: 25px;
    animation: slideIn 0.5s ease-out;
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
}

.alert-success {
    background: #d4edda;
    color: #155724;
    border-left: 4px solid var(--success);
}

.alert-error {
    background: #f8d7da;
    color: #721c24;
    border-left: 4px solid var(--accent);
}

.alert-info {
    background: #d1ecf1;
    color: #0c5460;
    border-left: 4px solid var(--info);
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 40px 20px;
    background: #f8f9fa;
    border-radius: 10px;
}

.empty-state i {
    font-size: 3rem;
    color: var(--gray);
    margin-bottom: 15px;
    opacity: 0.5;
}

.empty-state p {
    color: var(--gray);
    margin-bottom: 15px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 8px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.page-item {
    list-style: none;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 35px;
    height: 35px;
    background: white;
    color: var(--dark);
    text-decoration: none;
    border-radius: 5px;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.page-link:hover {
    background: var(--secondary);
    color: white;
    border-color: var(--secondary);
}

.page-link.active {
    background: var(--gradient);
    color: white;
    border: none;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes slideIn {
    from { opacity: 0; transform: translateX(-20px); }
    to { opacity: 1; transform: translateX(0); }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(5deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .dashboard-header {
        flex-direction: column;
        text-align: center;
    }
    
    .welcome-section {
        flex-direction: column;
    }
    
    .stats-grid-main,
    .stats-grid-secondary {
        grid-template-columns: 1fr;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .btn-action {
        width: 100%;
        justify-content: center;
    }
    
    .filter-section {
        flex-direction: column;
        align-items: flex-start;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .filter-select {
        width: 100%;
    }
}

/* Modal Styles */
.modal-overlay {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 1000;
    align-items: center;
    justify-content: center;
}

.modal-overlay.active {
    display: flex;
}

.modal-container {
    background: white;
    border-radius: 20px;
    padding: 30px;
    max-width: 500px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease-out;
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.modal-header h3 {
    color: var(--dark);
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    cursor: pointer;
    color: var(--gray);
    transition: color 0.3s ease;
}

.modal-close:hover {
    color: var(--accent);
}

.modal-body {
    margin-bottom: 20px;
}

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

/* Form styles inside modals */
.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--dark);
    font-weight: 600;
    font-size: 0.95rem;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

/* User detail items */
.user-detail-item {
    display: flex;
    margin-bottom: 15px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
}

.user-detail-label {
    width: 120px;
    color: var(--gray);
    font-weight: 600;
}

.user-detail-value {
    flex: 1;
    color: var(--dark);
}

.user-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 10px;
    margin-top: 20px;
}

.user-stat-card {
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 10px;
    text-align: center;
}

.user-stat-card h4 {
    font-size: 1.2rem;
    color: var(--secondary);
    margin-bottom: 5px;
}

.user-stat-card p {
    color: var(--gray);
    font-size: 0.85rem;
}

/* Confirm dialog */
.confirm-dialog {
    text-align: center;
    padding: 20px;
}

.confirm-icon {
    font-size: 4rem;
    color: var(--warning);
    margin-bottom: 20px;
    animation: pulse 1s infinite;
}

.confirm-title {
    font-size: 1.5rem;
    color: var(--dark);
    margin-bottom: 10px;
}

.confirm-message {
    color: var(--gray);
    margin-bottom: 25px;
}

.confirm-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn-confirm {
    padding: 12px 30px;
    border-radius: 8px;
    border: none;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-confirm.danger {
    background: var(--gradient-danger);
    color: white;
}

.btn-confirm.danger:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(231, 76, 60, 0.3);
}

.btn-confirm.secondary {
    background: #e0e0e0;
    color: var(--dark);
}

.btn-confirm.secondary:hover {
    background: #d0d0d0;
    transform: translateY(-2px);
}

/* Add Font Awesome icons inline */
.fas, .far, .fab {
    display: inline-block;
    font-style: normal;
    font-variant: normal;
    text-rendering: auto;
    line-height: 1;
}

/* Container padding */
.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}
</style>

<div class="dashboard-wrapper">
    <!-- Animated Background -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    
    <div class="dashboard-container">
        <!-- Alert Messages -->
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success" id="successAlert">
                <i class="fas fa-check-circle"></i>
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error" id="errorAlert">
                <i class="fas fa-exclamation-circle"></i>
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <!-- Dashboard Header -->
        <div class="dashboard-header">
            <div class="welcome-section">
                <div class="admin-avatar">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="welcome-text">
                    <h1>System Control Panel</h1>
                    <p>
                        <i class="fas fa-shield-alt"></i>
                        Administrative Access • <?php echo date('l, F j, Y'); ?>
                    </p>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="reports.php" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i>
                    Generate Full Audit Report
                </a>
                <a href="system_settings.php" class="btn btn-outline">
                    <i class="fas fa-cog"></i>
                    System Settings
                </a>
                <a href="../modules/auth/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
        
        <!-- Statistics Cards -->
        <div class="stats-grid-main">
            <div class="stat-card blue">
                <div class="stat-icon blue">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($totalUsers); ?></h3>
                    <p>Total Users</p>
                    <span class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +<?php echo $recentUsers; ?> this week
                    </span>
                </div>
            </div>
            
            <div class="stat-card green">
                <div class="stat-icon green">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($activeJobs); ?></h3>
                    <p>Active Jobs</p>
                    <span class="stat-trend trend-up">
                        <i class="fas fa-arrow-up"></i> +<?php echo $recentJobs; ?> new
                    </span>
                </div>
            </div>
            
            <div class="stat-card yellow">
                <div class="stat-icon yellow">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($totalApplications); ?></h3>
                    <p>Applications</p>
                    <span class="stat-trend">
                        <i class="fas fa-clock"></i> <?php echo $pendingApplications; ?> pending
                    </span>
                </div>
            </div>
            
            <div class="stat-card red">
                <div class="stat-icon red">
                    <i class="fas fa-building"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $unverifiedEmployers; ?></h3>
                    <p>Unverified Employers</p>
                    <span class="stat-trend trend-down">
                        <i class="fas fa-exclamation-triangle"></i> Need attention
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Secondary Statistics -->
        <div class="stats-grid-secondary">
            <div class="stat-mini-card">
                <div class="mini-icon blue">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="mini-details">
                    <h4><?php echo number_format($totalJobSeekers); ?></h4>
                    <p>Job Seekers</p>
                </div>
            </div>
            
            <div class="stat-mini-card">
                <div class="mini-icon green">
                    <i class="fas fa-building"></i>
                </div>
                <div class="mini-details">
                    <h4><?php echo number_format($totalEmployers); ?></h4>
                    <p>Employers</p>
                </div>
            </div>
            
            <div class="stat-mini-card">
                <div class="mini-icon purple">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="mini-details">
                    <h4><?php echo number_format($totalManagers); ?></h4>
                    <p>Managers</p>
                </div>
            </div>
            
            <div class="stat-mini-card">
                <div class="mini-icon orange">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="mini-details">
                    <h4><?php echo number_format($pendingApplications); ?></h4>
                    <p>Pending Apps</p>
                </div>
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="main-content-grid">
            <!-- User Management Section -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-users-cog"></i>
                        User Management & Verification
                    </h3>
                    <button class="btn btn-success btn-sm" onclick="openAddUserModal()">
                        <i class="fas fa-plus"></i> Add User
                    </button>
                </div>
                
                <!-- Bulk Actions -->
                <div class="bulk-actions">
                    <div class="select-all">
                        <input type="checkbox" id="selectAllCheckbox" onclick="toggleSelectAll(this)">
                        <label for="selectAllCheckbox">Select All</label>
                    </div>
                    
                    <select id="bulkActionSelect">
                        <option value="">Select Bulk Action</option>
                        <option value="verify">Verify Selected</option>
                        <option value="suspend">Suspend Selected</option>
                        <option value="activate">Activate Selected</option>
                        <option value="delete" disabled>Delete Selected (Disabled)</option>
                    </select>
                    
                    <button class="btn-bulk" onclick="executeBulkAction()">
                        <i class="fas fa-check-double"></i> Apply to Selected
                    </button>
                </div>
                
                <!-- Search Box -->
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="userSearch" placeholder="Search users by name, email, or role...">
                </div>
                
                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-group">
                        <label>Filter by Role:</label>
                        <select id="roleFilter" class="filter-select" onchange="filterUsers()">
                            <option value="all">All Roles</option>
                            <option value="1">Manager</option>
                            <option value="2">Employer</option>
                            <option value="3">Job Seeker</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Status:</label>
                        <select id="statusFilter" class="filter-select" onchange="filterUsers()">
                            <option value="all">All Status</option>
                            <option value="Active">Active</option>
                            <option value="Suspended">Suspended</option>
                        </select>
                    </div>
                    
                    <div class="filter-group">
                        <label>Verification:</label>
                        <select id="verificationFilter" class="filter-select" onchange="filterUsers()">
                            <option value="all">All</option>
                            <option value="verified">Verified</option>
                            <option value="unverified">Unverified</option>
                        </select>
                    </div>
                </div>
                
                <div class="table-responsive">
                    <form id="bulkActionForm" method="POST" action="?action=bulk_action">
                        <input type="hidden" name="bulk_action" id="bulkActionInput">
                        <table id="usersTable">
                            <thead>
                                <tr>
                                    <th width="40px">
                                        <input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)">
                                    </th>
                                    <th>Full Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Verified</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $users = $conn->query("
                                    SELECT u.*, r.Role_Name,
                                           ep.Verified as EmployerVerified,
                                           DATE_FORMAT(u.Created_At, '%M %d, %Y') as JoinDate
                                    FROM tbl_users u 
                                    JOIN tbl_roles r ON u.Role_ID = r.Role_ID 
                                    LEFT JOIN tbl_employer_profiles ep ON u.User_ID = ep.Employer_ID
                                    ORDER BY u.Created_At DESC
                                ");
                                
                                while ($u = $users->fetch(PDO::FETCH_ASSOC)) {
                                    $roleClass = '';
                                    $roleName = $u['Role_Name'] ?? 'Unknown';
                                    $userStatus = $u['Status'] ?? 'Active';
                                    $isVerified = ($u['EmployerVerified'] ?? 0) == 1;
                                    
                                    if ($roleName == 'Manager') {
                                        $roleClass = 'role-manager';
                                    } elseif ($roleName == 'Employer') {
                                        $roleClass = 'role-employer';
                                    } else {
                                        $roleClass = 'role-seeker';
                                    }
                                    
                                    $statusClass = ($userStatus == 'Suspended') ? 'status-inactive' : 'status-active';
                                    
                                    echo "<tr id='user-row-{$u['User_ID']}'>";
                                    echo "<td style='padding:12px; text-align:center;'>
                                            <input type='checkbox' name='selected_users[]' value='{$u['User_ID']}' class='user-checkbox'>
                                          </td>";
                                    echo "<td style='padding:12px;'><strong>" . htmlspecialchars($u['Full_Name']) . "</strong></td>";
                                    echo "<td style='padding:12px;'>" . htmlspecialchars($u['Email']) . "</td>";
                                    echo "<td style='padding:12px;'><span class='role-badge $roleClass'>" . htmlspecialchars($roleName) . "</span></td>";
                                    echo "<td style='padding:12px;'><span class='status-badge $statusClass' id='status-{$u['User_ID']}'>$userStatus</span></td>";
                                    echo "<td style='padding:12px;'>";
                                    if ($roleName == 'Employer') {
                                        if ($isVerified) {
                                            echo "<span class='status-badge status-verified'><i class='fas fa-check-circle'></i> Verified</span>";
                                        } else {
                                            echo "<span class='status-badge status-pending'><i class='fas fa-clock'></i> Pending</span>";
                                        }
                                    } else {
                                        echo "<span class='status-badge' style='background:#e0e0e0; color:#666;'>N/A</span>";
                                    }
                                    echo "</td>";
                                    echo "<td style='padding:12px;'><i class='far fa-calendar-alt' style='color: var(--gray); margin-right:5px;'></i>" . $u['JoinDate'] . "</td>";
                                    echo "<td style='padding:12px;'>
                                            <div class='action-buttons'>
                                                <button onclick='viewUser({$u['User_ID']})' class='btn-action view' title='View Details'><i class='fas fa-eye'></i></button>
                                                <button onclick='editUser({$u['User_ID']})' class='btn-action edit' title='Edit User'><i class='fas fa-edit'></i></button>";
                                                
                                    if ($roleName == 'Employer' && !$isVerified) {
                                        echo "<button onclick='verifyUser({$u['User_ID']})' class='btn-action verify' title='Verify Employer'><i class='fas fa-check-circle'></i></button>";
                                    }
                                    
                                    if ($userStatus == 'Suspended') {
                                        echo "<button onclick='activateUser({$u['User_ID']})' class='btn-action verify' title='Activate User'><i class='fas fa-play'></i></button>";
                                    } else {
                                        echo "<button onclick='suspendUser({$u['User_ID']})' class='btn-action suspend' title='Suspend User'><i class='fas fa-ban'></i></button>";
                                    }
                                    
                                    echo "<button onclick='deleteUser({$u['User_ID']})' class='btn-action delete' title='Delete User'><i class='fas fa-trash'></i></button>
                                            </div>
                                          </td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
            
            <!-- Recent Activity & Quick Actions -->
            <div>
                <!-- Recent Activity Card -->
                <div class="card">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-history"></i>
                            Recent Activity
                        </h3>
                        <a href="activity_log.php">View All <i class="fas fa-arrow-right"></i></a>
                    </div>
                    
                    <div class="activity-list">
                        <?php 
                        $displayCount = 0;
                        foreach ($recentActivities as $activity): 
                            if ($displayCount >= 7) break;
                            $displayCount++;
                            
                            $icon = '';
                            $color = '';
                            switch($activity['type']) {
                                case 'user':
                                    $icon = 'fas fa-user-plus';
                                    $color = 'var(--secondary)';
                                    break;
                                case 'job':
                                    $icon = 'fas fa-briefcase';
                                    $color = 'var(--success)';
                                    break;
                                case 'application':
                                    $icon = 'fas fa-file-alt';
                                    $color = 'var(--warning)';
                                    break;
                            }
                            
                            $timeAgo = time() - strtotime($activity['date']);
                            if ($timeAgo < 60) {
                                $timeText = 'just now';
                            } elseif ($timeAgo < 3600) {
                                $minutes = floor($timeAgo/60);
                                $timeText = $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
                            } elseif ($timeAgo < 86400) {
                                $hours = floor($timeAgo/3600);
                                $timeText = $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
                            } else {
                                $days = floor($timeAgo/86400);
                                $timeText = $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
                            }
                        ?>
                        <div class="activity-item">
                            <div class="activity-icon" style="color: <?php echo $color; ?>;">
                                <i class="<?php echo $icon; ?>"></i>
                            </div>
                            <div class="activity-details">
                                <p><strong><?php echo htmlspecialchars($activity['title']); ?></strong> - <?php echo $activity['description']; ?></p>
                                <span class="activity-time"><i class="far fa-clock"></i> <?php echo $timeText; ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <?php if (empty($recentActivities)): ?>
                            <div class="empty-state">
                                <i class="fas fa-history"></i>
                                <p>No recent activity</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Quick Actions Card -->
                <div class="card" style="margin-top: 25px;">
                    <div class="card-header">
                        <h3>
                            <i class="fas fa-bolt"></i>
                            Quick Actions
                        </h3>
                    </div>
                    
                    <div class="quick-actions">
                        <a href="javascript:void(0)" onclick="openAddUserModal()" class="quick-action-item">
                            <i class="fas fa-user-plus"></i>
                            <span>Add User</span>
                        </a>
                        
                        <a href="?action=verify_all" class="quick-action-item" onclick="return confirm('Verify all pending employers?')">
                            <i class="fas fa-check-double"></i>
                            <span>Verify All</span>
                        </a>
                        
                        <a href="reports.php" class="quick-action-item">
                            <i class="fas fa-chart-bar"></i>
                            <span>Generate Report</span>
                        </a>
                        
                        <a href="system_settings.php" class="quick-action-item">
                            <i class="fas fa-cog"></i>
                            <span>Settings</span>
                        </a>
                        
                        <a href="backup.php" class="quick-action-item">
                            <i class="fas fa-database"></i>
                            <span>Backup Database</span>
                        </a>
                        
                        <a href="audit_logs.php" class="quick-action-item">
                            <i class="fas fa-history"></i>
                            <span>Audit Logs</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- View User Modal -->
<div class="modal-overlay" id="viewUserModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-user-circle"></i> User Details</h3>
            <button class="modal-close" onclick="closeModal('viewUserModal')">&times;</button>
        </div>
        <div class="modal-body" id="viewUserContent">
            <!-- Content will be loaded dynamically -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('viewUserModal')">Close</button>
            <button class="btn btn-primary" onclick="editUserFromView()">Edit User</button>
        </div>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Edit User</h3>
            <button class="modal-close" onclick="closeModal('editUserModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="editUserForm" method="POST" action="edit_user.php">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" id="edit_email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role_id" id="edit_role" class="form-control" required>
                        <option value="1">Manager</option>
                        <option value="2">Employer</option>
                        <option value="3">Job Seeker</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="status" id="edit_status" class="form-control">
                        <option value="Active">Active</option>
                        <option value="Suspended">Suspended</option>
                        <option value="Inactive">Inactive</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label class="form-label">New Password (leave blank to keep current)</label>
                    <input type="password" name="password" class="form-control" placeholder="Enter new password">
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('editUserModal')">Cancel</button>
            <button class="btn btn-success" onclick="submitEditUser()">Save Changes</button>
        </div>
    </div>
</div>

<!-- Confirm Action Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal-container" style="max-width: 400px;">
        <div class="confirm-dialog">
            <div class="confirm-icon" id="confirmIcon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="confirm-title" id="confirmTitle">Confirm Action</h3>
            <p class="confirm-message" id="confirmMessage">Are you sure you want to perform this action?</p>
            <div class="confirm-actions">
                <button class="btn-confirm secondary" onclick="closeModal('confirmModal')">Cancel</button>
                <button class="btn-confirm danger" id="confirmButton" onclick="executeConfirmedAction()">Confirm</button>
            </div>
        </div>
    </div>
</div>

<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Add New User</h3>
            <button class="modal-close" onclick="closeModal('addUserModal')">&times;</button>
        </div>
        <div class="modal-body">
            <form id="addUserForm" method="POST" action="add_user.php">
                <div class="form-group">
                    <label class="form-label">Full Name</label>
                    <input type="text" name="full_name" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Role</label>
                    <select name="role_id" class="form-control" required>
                        <option value="">Select Role</option>
                        <option value="1">Manager</option>
                        <option value="2">Employer</option>
                        <option value="3">Job Seeker</option>
                    </select>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closeModal('addUserModal')">Cancel</button>
            <button class="btn btn-success" onclick="submitAddUser()">Add User</button>
        </div>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
// State management
let currentAction = '';
let currentUserId = 0;
let selectedUsers = [];

// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

// View User
async function viewUser(userId) {
    try {
        const response = await fetch(`get_user_details.php?id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            const content = document.getElementById('viewUserContent');
            content.innerHTML = `
                <div class="user-detail-item">
                    <span class="user-detail-label">Full Name:</span>
                    <span class="user-detail-value">${data.user.Full_Name}</span>
                </div>
                <div class="user-detail-item">
                    <span class="user-detail-label">Email:</span>
                    <span class="user-detail-value">${data.user.Email}</span>
                </div>
                <div class="user-detail-item">
                    <span class="user-detail-label">Role:</span>
                    <span class="user-detail-value">${data.user.Role_Name}</span>
                </div>
                <div class="user-detail-item">
                    <span class="user-detail-label">Status:</span>
                    <span class="user-detail-value"><span class="status-badge ${data.user.Status === 'Active' ? 'status-active' : 'status-inactive'}">${data.user.Status || 'Active'}</span></span>
                </div>
                <div class="user-detail-item">
                    <span class="user-detail-label">Joined:</span>
                    <span class="user-detail-value">${data.user.Created_At}</span>
                </div>
                <div class="user-stats">
                    <div class="user-stat-card">
                        <h4>${data.stats.applications || 0}</h4>
                        <p>Applications</p>
                    </div>
                    <div class="user-stat-card">
                        <h4>${data.stats.jobs || 0}</h4>
                        <p>Jobs Posted</p>
                    </div>
                    <div class="user-stat-card">
                        <h4>${data.stats.saved || 0}</h4>
                        <p>Saved Jobs</p>
                    </div>
                </div>
            `;
            openModal('viewUserModal');
            currentUserId = userId;
        } else {
            alert('Failed to load user details');
        }
    } catch (error) {
        alert('Network error occurred');
    }
}

// Edit User
async function editUser(userId) {
    try {
        const response = await fetch(`get_user_details.php?id=${userId}`);
        const data = await response.json();
        
        if (data.success) {
            document.getElementById('edit_user_id').value = data.user.User_ID;
            document.getElementById('edit_full_name').value = data.user.Full_Name;
            document.getElementById('edit_email').value = data.user.Email;
            document.getElementById('edit_role').value = data.user.Role_ID;
            document.getElementById('edit_status').value = data.user.Status || 'Active';
            openModal('editUserModal');
            currentUserId = userId;
        }
    } catch (error) {
        alert('Failed to load user data');
    }
}

function submitEditUser() {
    document.getElementById('editUserForm').submit();
}

function editUserFromView() {
    closeModal('viewUserModal');
    editUser(currentUserId);
}

// Verify User
function verifyUser(userId) {
    currentAction = 'verify';
    currentUserId = userId;
    document.getElementById('confirmTitle').innerText = 'Verify Employer';
    document.getElementById('confirmMessage').innerText = 'Are you sure you want to verify this employer? They will be able to post jobs.';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-check-circle" style="color: var(--success);"></i>';
    document.getElementById('confirmButton').className = 'btn-confirm success';
    document.getElementById('confirmButton').style.background = 'var(--gradient-success)';
    openModal('confirmModal');
}

// Suspend User
function suspendUser(userId) {
    currentAction = 'suspend';
    currentUserId = userId;
    document.getElementById('confirmTitle').innerText = 'Suspend User';
    document.getElementById('confirmMessage').innerText = 'Are you sure you want to suspend this user? They will not be able to access the system.';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-ban" style="color: var(--warning);"></i>';
    document.getElementById('confirmButton').className = 'btn-confirm danger';
    openModal('confirmModal');
}

// Activate User
function activateUser(userId) {
    currentAction = 'activate';
    currentUserId = userId;
    document.getElementById('confirmTitle').innerText = 'Activate User';
    document.getElementById('confirmMessage').innerText = 'Are you sure you want to activate this user? They will regain access to the system.';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-play" style="color: var(--success);"></i>';
    document.getElementById('confirmButton').className = 'btn-confirm success';
    document.getElementById('confirmButton').style.background = 'var(--gradient-success)';
    openModal('confirmModal');
}

// Delete User
function deleteUser(userId) {
    currentAction = 'delete';
    currentUserId = userId;
    document.getElementById('confirmTitle').innerText = 'Delete User';
    document.getElementById('confirmMessage').innerText = 'Are you sure you want to delete this user? This action cannot be undone!';
    document.getElementById('confirmIcon').innerHTML = '<i class="fas fa-trash" style="color: var(--accent);"></i>';
    document.getElementById('confirmButton').className = 'btn-confirm danger';
    openModal('confirmModal');
}

// Execute confirmed action
function executeConfirmedAction() {
    let url = '';
    switch(currentAction) {
        case 'verify':
            url = `?action=verify&user_id=${currentUserId}`;
            break;
        case 'suspend':
            url = `?action=suspend&user_id=${currentUserId}&status=suspend`;
            break;
        case 'activate':
            url = `?action=suspend&user_id=${currentUserId}&status=activate`;
            break;
        case 'delete':
            url = `?action=delete&user_id=${currentUserId}`;
            break;
    }
    if (url) {
        window.location.href = url;
    }
    closeModal('confirmModal');
}

// Bulk Actions
function toggleSelectAll(checkbox) {
    const checkboxes = document.querySelectorAll('.user-checkbox');
    checkboxes.forEach(cb => cb.checked = checkbox.checked);
    updateSelectedUsers();
}

function updateSelectedUsers() {
    selectedUsers = [];
    document.querySelectorAll('.user-checkbox:checked').forEach(cb => {
        selectedUsers.push(cb.value);
    });
}

function executeBulkAction() {
    const action = document.getElementById('bulkActionSelect').value;
    const checkboxes = document.querySelectorAll('.user-checkbox:checked');
    
    if (checkboxes.length === 0) {
        alert('Please select at least one user');
        return;
    }
    
    if (!action) {
        alert('Please select a bulk action');
        return;
    }
    
    document.getElementById('bulkActionInput').value = action;
    document.getElementById('bulkActionForm').submit();
}

// Add User
function openAddUserModal() {
    openModal('addUserModal');
}

function submitAddUser() {
    document.getElementById('addUserForm').submit();
}

// Search and filter
function filterUsers() {
    const searchInput = document.getElementById('userSearch');
    const roleFilter = document.getElementById('roleFilter');
    const statusFilter = document.getElementById('statusFilter');
    const verificationFilter = document.getElementById('verificationFilter');
    const table = document.getElementById('usersTable');
    const rows = table.getElementsByTagName('tr');
    
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : '';
    const roleValue = roleFilter ? roleFilter.value : 'all';
    const statusValue = statusFilter ? statusFilter.value : 'all';
    const verificationValue = verificationFilter ? verificationFilter.value : 'all';
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        if (!row) continue;
        
        const name = row.cells[1]?.textContent.toLowerCase() || '';
        const email = row.cells[2]?.textContent.toLowerCase() || '';
        const role = row.cells[3]?.textContent || '';
        const status = row.cells[4]?.textContent || '';
        const verified = row.cells[5]?.textContent || '';
        
        let matchesSearch = searchTerm === '' || name.includes(searchTerm) || email.includes(searchTerm);
        let matchesRole = roleValue === 'all' || 
                         (roleValue === '1' && role.includes('Manager')) ||
                         (roleValue === '2' && role.includes('Employer')) ||
                         (roleValue === '3' && role.includes('Job Seeker'));
        let matchesStatus = statusValue === 'all' || status.includes(statusValue);
        let matchesVerification = verificationValue === 'all' ||
                                 (verificationValue === 'verified' && verified.includes('Verified')) ||
                                 (verificationValue === 'unverified' && verified.includes('Pending'));
        
        if (matchesSearch && matchesRole && matchesStatus && matchesVerification) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    }
}

// Auto-hide alerts
setTimeout(() => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        if (alert) {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity = '0';
            setTimeout(() => {
                if (alert.parentNode) {
                    alert.remove();
                }
            }, 500);
        }
    });
}, 5000);

// Attach event listeners
document.getElementById('userSearch')?.addEventListener('keyup', filterUsers);
document.getElementById('roleFilter')?.addEventListener('change', filterUsers);
document.getElementById('statusFilter')?.addEventListener('change', filterUsers);
document.getElementById('verificationFilter')?.addEventListener('change', filterUsers);

// Close modals when clicking outside
window.addEventListener('click', function(event) {
    document.querySelectorAll('.modal-overlay').forEach(modal => {
        if (event.target === modal) {
            modal.classList.remove('active');
        }
    });
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        document.querySelectorAll('.modal-overlay').forEach(modal => {
            modal.classList.remove('active');
        });
    }
    
    if (e.ctrlKey && e.key === 'a' && !e.target.matches('input, textarea')) {
        e.preventDefault();
        openAddUserModal();
    }
    
    if (e.key === '/' && !e.ctrlKey && !e.altKey && !e.metaKey) {
        e.preventDefault();
        document.getElementById('userSearch')?.focus();
    }
});
</script>

<?php include '../includes/footer.php'; ?>