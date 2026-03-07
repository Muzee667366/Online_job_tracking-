<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security: Ensure user is an Employer (Role 2)
if ($_SESSION['role_id'] != 2) {
    $_SESSION['error_message'] = "Access denied. Only employers can access this page.";
    header("Location: ../public/login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $company_name = trim($_POST['company_name']);
    $company_website = trim($_POST['company_website']);
    $industry = trim($_POST['industry']);
    $company_size = $_POST['company_size'] ?? '';
    $founded_year = $_POST['founded_year'] ? (int)$_POST['founded_year'] : null;
    $company_description = trim($_POST['company_description']);
    $contact_person = trim($_POST['contact_person']);
    $contact_phone = trim($_POST['contact_phone']);
    $contact_email = trim($_POST['contact_email']);
    $address = trim($_POST['address']);
    
    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['company_logo']['type'];
        
        if (in_array($file_type, $allowed_types) && $_FILES['company_logo']['size'] <= 2 * 1024 * 1024) {
            $upload_dir = '../uploads/company_logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['company_logo']['name'], PATHINFO_EXTENSION);
            $logo_path = 'uploads/company_logos/' . $employer_id . '_' . time() . '.' . $file_extension;
            $full_path = '../' . $logo_path;
            
            if (move_uploaded_file($_FILES['company_logo']['tmp_name'], $full_path)) {
                // Success
            } else {
                $logo_path = null;
            }
        }
    }
    
    try {
        // Check if profile exists
        $check = $conn->prepare("SELECT * FROM tbl_employer_profiles WHERE Employer_ID = ?");
        $check->execute([$employer_id]);
        
        if ($check->rowCount() > 0) {
            // Update existing profile
            if ($logo_path) {
                $stmt = $conn->prepare("UPDATE tbl_employer_profiles SET 
                    Company_Name = ?, Company_Website = ?, Industry = ?, Company_Size = ?, 
                    Founded_Year = ?, Company_Description = ?, Contact_Person = ?, 
                    Contact_Phone = ?, Contact_Email = ?, Address = ?, Company_Logo = ? 
                    WHERE Employer_ID = ?");
                $stmt->execute([$company_name, $company_website, $industry, $company_size, 
                               $founded_year, $company_description, $contact_person, 
                               $contact_phone, $contact_email, $address, $logo_path, $employer_id]);
            } else {
                $stmt = $conn->prepare("UPDATE tbl_employer_profiles SET 
                    Company_Name = ?, Company_Website = ?, Industry = ?, Company_Size = ?, 
                    Founded_Year = ?, Company_Description = ?, Contact_Person = ?, 
                    Contact_Phone = ?, Contact_Email = ?, Address = ? 
                    WHERE Employer_ID = ?");
                $stmt->execute([$company_name, $company_website, $industry, $company_size, 
                               $founded_year, $company_description, $contact_person, 
                               $contact_phone, $contact_email, $address, $employer_id]);
            }
            $_SESSION['success_message'] = "Company profile updated successfully!";
        } else {
            // Insert new profile
            $stmt = $conn->prepare("INSERT INTO tbl_employer_profiles 
                (Employer_ID, Company_Name, Company_Website, Industry, Company_Size, 
                 Founded_Year, Company_Description, Contact_Person, Contact_Phone, 
                 Contact_Email, Address, Company_Logo, Verified) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->execute([$employer_id, $company_name, $company_website, $industry, 
                           $company_size, $founded_year, $company_description, 
                           $contact_person, $contact_phone, $contact_email, $address, $logo_path]);
            $_SESSION['success_message'] = "Company profile created successfully!";
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error saving profile: " . $e->getMessage();
        error_log("Employer profile error: " . $e->getMessage());
    }
    
    header("Location: employer_dashboard.php");
    exit();
}

// Get employer statistics with error handling
try {
    // Check if tables exist and have correct structure
    $tables_exist = true;
    
    // Total jobs posted
    $totalJobs = $conn->prepare("SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = ?");
    $totalJobs->execute([$employer_id]);
    $totalJobsCount = $totalJobs->fetchColumn();
    
    // Active jobs
    $activeJobs = $conn->prepare("SELECT COUNT(*) FROM tbl_jobs WHERE Employer_ID = ? AND Status = 'Active'");
    $activeJobs->execute([$employer_id]);
    $activeJobsCount = $activeJobs->fetchColumn();
    
    // Total applications received
    $totalApps = $conn->prepare("
        SELECT COUNT(*) FROM tbl_applications a 
        INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
        WHERE j.Employer_ID = ?
    ");
    $totalApps->execute([$employer_id]);
    $totalAppsCount = $totalApps->fetchColumn();
    
    // Pending applications
    $pendingApps = $conn->prepare("
        SELECT COUNT(*) FROM tbl_applications a 
        INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
        WHERE j.Employer_ID = ? AND a.Application_Status = 'Pending'
    ");
    $pendingApps->execute([$employer_id]);
    $pendingAppsCount = $pendingApps->fetchColumn();
    
    // Reviewed applications
    $reviewedApps = $conn->prepare("
        SELECT COUNT(*) FROM tbl_applications a 
        INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
        WHERE j.Employer_ID = ? AND a.Application_Status = 'Reviewed'
    ");
    $reviewedApps->execute([$employer_id]);
    $reviewedAppsCount = $reviewedApps->fetchColumn();
    
    // Accepted applications
    $acceptedApps = $conn->prepare("
        SELECT COUNT(*) FROM tbl_applications a 
        INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
        WHERE j.Employer_ID = ? AND a.Application_Status = 'Accepted'
    ");
    $acceptedApps->execute([$employer_id]);
    $acceptedAppsCount = $acceptedApps->fetchColumn();
    
    // Get recent jobs
    $recentJobs = $conn->prepare("
        SELECT j.*, 
               (SELECT COUNT(*) FROM tbl_applications WHERE Job_ID = j.Job_ID) as app_count 
        FROM tbl_jobs j 
        WHERE Employer_ID = ? 
        ORDER BY j.Posted_Date DESC 
        LIMIT 5
    ");
    $recentJobs->execute([$employer_id]);
    $recentJobsList = $recentJobs->fetchAll(PDO::FETCH_ASSOC);
    
    // Get recent applications
    $recentApps = $conn->prepare("
        SELECT a.*, j.Job_Title, u.Full_Name, u.Email 
        FROM tbl_applications a 
        INNER JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
        INNER JOIN tbl_users u ON a.User_ID = u.User_ID 
        WHERE j.Employer_ID = ? 
        ORDER BY a.Application_Date DESC 
        LIMIT 5
    ");
    $recentApps->execute([$employer_id]);
    $recentApplications = $recentApps->fetchAll(PDO::FETCH_ASSOC);
    
    // Get company profile
    $profile = $conn->prepare("SELECT * FROM tbl_employer_profiles WHERE Employer_ID = ?");
    $profile->execute([$employer_id]);
    $companyProfile = $profile->fetch(PDO::FETCH_ASSOC);
    
    // Calculate profile completion
    $profileCompletion = 0;
    $totalFields = 10;
    $filledFields = 0;
    
    if ($companyProfile) {
        if (!empty($companyProfile['Company_Name'])) $filledFields++;
        if (!empty($companyProfile['Industry'])) $filledFields++;
        if (!empty($companyProfile['Company_Size'])) $filledFields++;
        if (!empty($companyProfile['Company_Description'])) $filledFields++;
        if (!empty($companyProfile['Contact_Person'])) $filledFields++;
        if (!empty($companyProfile['Contact_Phone'])) $filledFields++;
        if (!empty($companyProfile['Contact_Email'])) $filledFields++;
        if (!empty($companyProfile['Address'])) $filledFields++;
        if (!empty($companyProfile['Company_Website'])) $filledFields++;
        if (!empty($companyProfile['Company_Logo'])) $filledFields++;
        
        $profileCompletion = round(($filledFields / $totalFields) * 100);
    }
    
} catch (PDOException $e) {
    error_log("Employer dashboard error: " . $e->getMessage());
    // Set default values if queries fail
    $totalJobsCount = 0;
    $activeJobsCount = 0;
    $totalAppsCount = 0;
    $pendingAppsCount = 0;
    $reviewedAppsCount = 0;
    $acceptedAppsCount = 0;
    $recentJobsList = [];
    $recentApplications = [];
    $companyProfile = null;
    $profileCompletion = 0;
    
    // Show error message to admin only (you can remove this in production)
    if ($_SESSION['role_id'] == 1) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
    }
}

include '../includes/header.php'; 
?>

<style>
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

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
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
    width: 400px;
    height: 400px;
    top: -200px;
    right: -200px;
    animation: float 20s infinite;
}

.shape-2 {
    width: 300px;
    height: 300px;
    bottom: -150px;
    left: -150px;
    animation: float 15s infinite reverse;
}

.shape-3 {
    width: 200px;
    height: 200px;
    top: 30%;
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

.company-avatar {
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
    overflow: hidden;
    cursor: pointer;
    transition: transform 0.3s ease;
}

.company-avatar:hover {
    transform: scale(1.1);
}

.company-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
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

.verification-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    margin-left: 10px;
}

.verified {
    background: #d4edda;
    color: #155724;
}

.unverified {
    background: #fff3cd;
    color: #856404;
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

/* Stats Grid */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
    gap: 15px;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.stat-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.stat-icon {
    width: 60px;
    height: 60px;
    border-radius: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8rem;
    color: white;
}

.stat-icon.blue { background: var(--gradient); }
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
    margin-bottom: 3px;
}

.stat-details small {
    color: var(--secondary);
    font-size: 0.85rem;
}

/* Profile Completion Bar */
.profile-completion {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: var(--shadow-sm);
}

.completion-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 10px;
}

.completion-header h4 {
    color: var(--dark);
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    gap: 8px;
}

.completion-header span {
    color: var(--secondary);
    font-weight: 700;
    font-size: 1.2rem;
}

.progress-bar {
    width: 100%;
    height: 10px;
    background: #f0f0f0;
    border-radius: 5px;
    overflow: hidden;
}

.progress-fill {
    height: 100%;
    background: var(--gradient-success);
    border-radius: 5px;
    transition: width 0.3s ease;
}

/* Company Profile View Card */
.profile-view-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    position: relative;
    overflow: hidden;
}

.profile-view-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 6px;
    background: var(--gradient);
}

.profile-view-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    flex-wrap: wrap;
    gap: 20px;
}

.profile-view-title {
    display: flex;
    align-items: center;
    gap: 15px;
}

.profile-view-title h2 {
    color: var(--dark);
    font-size: 1.8rem;
}

.profile-view-actions {
    display: flex;
    gap: 10px;
}

.company-banner {
    display: flex;
    gap: 30px;
    align-items: center;
    flex-wrap: wrap;
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 2px solid #f0f0f0;
}

.company-logo-large {
    width: 120px;
    height: 120px;
    background: var(--gradient);
    border-radius: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3.5rem;
    color: white;
    overflow: hidden;
    box-shadow: var(--shadow-md);
}

.company-logo-large img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.company-name-large h3 {
    font-size: 2.2rem;
    color: var(--dark);
    margin-bottom: 10px;
}

.company-name-large p {
    color: var(--gray);
    display: flex;
    align-items: center;
    gap: 5px;
}

.company-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 30px;
}

.info-section {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
}

.info-section h4 {
    color: var(--dark);
    font-size: 1.2rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
    border-bottom: 2px solid #e0e0e0;
    padding-bottom: 10px;
}

.info-section h4 i {
    color: var(--secondary);
}

.info-row {
    display: flex;
    margin-bottom: 12px;
}

.info-label {
    width: 120px;
    color: var(--gray);
    font-weight: 600;
}

.info-value {
    flex: 1;
    color: var(--dark);
}

.info-value a {
    color: var(--secondary);
    text-decoration: none;
}

.info-value a:hover {
    text-decoration: underline;
}

.company-description {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 25px;
    margin-top: 20px;
}

.company-description h4 {
    color: var(--dark);
    font-size: 1.2rem;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.company-description p {
    color: var(--gray);
    line-height: 1.8;
    white-space: pre-line;
}

.stats-row {
    display: flex;
    gap: 20px;
    margin-top: 20px;
    flex-wrap: wrap;
}

.stat-badge {
    background: white;
    border-radius: 30px;
    padding: 8px 20px;
    display: flex;
    align-items: center;
    gap: 8px;
    box-shadow: var(--shadow-sm);
}

.stat-badge i {
    color: var(--secondary);
}

.stat-badge span {
    color: var(--dark);
    font-weight: 600;
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

/* Table Styles */
.table-responsive {
    overflow-x: auto;
}

table {
    width: 100%;
    border-collapse: collapse;
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
}

td {
    padding: 15px 10px;
    border-bottom: 1px solid #f0f0f0;
    color: var(--dark);
}

tr:hover td {
    background: #f8f9fa;
}

/* Status Badges */
.status-badge {
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-closed {
    background: #f8d7da;
    color: #721c24;
}

.status-reviewed {
    background: #d1ecf1;
    color: #0c5460;
}

.status-accepted {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.btn-action {
    padding: 6px 12px;
    border-radius: 5px;
    font-size: 0.85rem;
    font-weight: 600;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    border: none;
    cursor: pointer;
    color: white;
}

.btn-action.view { background: var(--gradient-info); }
.btn-action.edit { background: var(--gradient-warning); }
.btn-action.delete { background: var(--gradient-danger); }
.btn-action.applicants { background: var(--gradient-success); }

.btn-action:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-sm);
}

/* Applications List */
.applications-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.application-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.application-item:hover {
    background: #e8f0fe;
    transform: translateX(5px);
}

.applicant-info h4 {
    color: var(--dark);
    margin-bottom: 5px;
    font-size: 1rem;
}

.applicant-info p {
    color: var(--gray);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.applicant-meta {
    text-align: right;
}

.applicant-meta .date {
    color: var(--gray);
    font-size: 0.85rem;
    margin-bottom: 5px;
}

/* Company Profile Form */
.profile-form {
    margin-top: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

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

.form-group label i {
    color: var(--secondary);
    margin-right: 8px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.form-control:hover {
    border-color: var(--secondary);
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

textarea.form-control {
    resize: vertical;
    min-height: 100px;
}

/* File Upload */
.file-upload {
    position: relative;
    margin-top: 5px;
}

.file-upload-label {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border: 2px dashed var(--secondary);
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload-label:hover {
    background: #e8f0fe;
    border-color: var(--primary);
}

.file-upload-label i {
    font-size: 2rem;
    color: var(--secondary);
}

.file-upload-label span {
    color: var(--gray);
}

.file-upload-label span strong {
    color: var(--secondary);
}

.file-upload input[type="file"] {
    position: absolute;
    opacity: 0;
    width: 100%;
    height: 100%;
    top: 0;
    left: 0;
    cursor: pointer;
}

.file-info {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    font-size: 0.9rem;
    color: var(--gray);
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Current Logo Display */
.current-logo {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 15px;
}

.logo-preview {
    width: 60px;
    height: 60px;
    border-radius: 10px;
    overflow: hidden;
    background: white;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid #e0e0e0;
}

.logo-preview img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.logo-preview i {
    font-size: 2rem;
    color: var(--gray);
}

/* Quick Actions */
.quick-actions {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
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
    max-width: 600px;
    width: 90%;
    max-height: 80vh;
    overflow-y: auto;
    animation: slideUp 0.3s ease-out;
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(50px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

.modal-footer {
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .company-banner {
        flex-direction: column;
        text-align: center;
    }
    
    .profile-view-header {
        flex-direction: column;
    }
    
    .info-row {
        flex-direction: column;
        gap: 5px;
    }
    
    .info-label {
        width: auto;
    }
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
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php 
                echo $_SESSION['success_message'];
                unset($_SESSION['success_message']);
                ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
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
                <div class="company-avatar" onclick="scrollToProfile()">
                    <?php if ($companyProfile && !empty($companyProfile['Company_Logo'])): ?>
                        <img src="../<?php echo htmlspecialchars($companyProfile['Company_Logo']); ?>" alt="Company Logo">
                    <?php else: ?>
                        <i class="fas fa-building"></i>
                    <?php endif; ?>
                </div>
                <div class="welcome-text">
                    <h1>
                        <?php echo htmlspecialchars($companyProfile['Company_Name'] ?? $_SESSION['full_name']); ?>
                        <?php if ($companyProfile && isset($companyProfile['Verified'])): ?>
                            <?php if ($companyProfile['Verified']): ?>
                                <span class="verification-badge verified">
                                    <i class="fas fa-check-circle"></i> Verified
                                </span>
                            <?php else: ?>
                                <span class="verification-badge unverified">
                                    <i class="fas fa-clock"></i> Pending Verification
                                </span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </h1>
                    <p>
                        <i class="fas fa-user-tie"></i>
                        Employer Dashboard • <?php echo date('l, F j, Y'); ?>
                    </p>
                </div>
            </div>
            
            <div class="header-actions">
                <button class="btn btn-primary" onclick="openProfileModal()">
                    <i class="fas fa-edit"></i>
                    <?php echo $companyProfile ? 'Edit Profile' : 'Setup Profile'; ?>
                </button>
                <a href="post_job.php" class="btn btn-success">
                    <i class="fas fa-plus-circle"></i>
                    Post New Job
                </a>
                <a href="../modules/auth/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
        
        <!-- Profile Completion Bar (shown only if profile incomplete) -->
        <?php if (!$companyProfile || $profileCompletion < 100): ?>
        <div class="profile-completion">
            <div class="completion-header">
                <h4>
                    <i class="fas fa-chart-line"></i>
                    Company Profile Completion
                </h4>
                <span><?php echo $profileCompletion; ?>%</span>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $profileCompletion; ?>%;"></div>
            </div>
            <p style="color: var(--gray); margin-top: 10px; font-size: 0.9rem;">
                <i class="fas fa-info-circle"></i>
                Complete your company profile to get verified and attract more applicants
            </p>
        </div>
        <?php endif; ?>
        
        <!-- Company Profile View Section -->
        <div id="company-profile-view" class="profile-view-card">
            <div class="profile-view-header">
                <div class="profile-view-title">
                    <i class="fas fa-building" style="font-size: 2rem; color: var(--secondary);"></i>
                    <h2>Company Profile</h2>
                </div>
                <div class="profile-view-actions">
                    <button class="btn btn-sm btn-outline" onclick="openProfileModal()">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button class="btn btn-sm btn-primary" onclick="window.print()">
                        <i class="fas fa-print"></i> Print
                    </button>
                </div>
            </div>
            
            <?php if ($companyProfile): ?>
            <!-- Company Banner -->
            <div class="company-banner">
                <div class="company-logo-large">
                    <?php if (!empty($companyProfile['Company_Logo'])): ?>
                        <img src="../<?php echo htmlspecialchars($companyProfile['Company_Logo']); ?>" alt="Company Logo">
                    <?php else: ?>
                        <i class="fas fa-building"></i>
                    <?php endif; ?>
                </div>
                <div class="company-name-large">
                    <h3><?php echo htmlspecialchars($companyProfile['Company_Name']); ?></h3>
                    <p>
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($companyProfile['Industry'] ?? 'Industry not specified'); ?>
                        <?php if ($companyProfile['Verified']): ?>
                            <span class="verification-badge verified" style="margin-left: 10px;">
                                <i class="fas fa-check-circle"></i> Verified Company
                            </span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <!-- Company Info Grid -->
            <div class="company-info-grid">
                <!-- Basic Information -->
                <div class="info-section">
                    <h4><i class="fas fa-info-circle"></i> Basic Information</h4>
                    
                    <?php if (!empty($companyProfile['Company_Website'])): ?>
                    <div class="info-row">
                        <span class="info-label">Website:</span>
                        <span class="info-value">
                            <a href="<?php echo htmlspecialchars($companyProfile['Company_Website']); ?>" target="_blank">
                                <?php echo htmlspecialchars($companyProfile['Company_Website']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyProfile['Industry'])): ?>
                    <div class="info-row">
                        <span class="info-label">Industry:</span>
                        <span class="info-value"><?php echo htmlspecialchars($companyProfile['Industry']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyProfile['Company_Size'])): ?>
                    <div class="info-row">
                        <span class="info-label">Company Size:</span>
                        <span class="info-value"><?php echo htmlspecialchars($companyProfile['Company_Size']); ?> employees</span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyProfile['Founded_Year'])): ?>
                    <div class="info-row">
                        <span class="info-label">Founded:</span>
                        <span class="info-value"><?php echo htmlspecialchars($companyProfile['Founded_Year']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyProfile['Address'])): ?>
                    <div class="info-row">
                        <span class="info-label">Location:</span>
                        <span class="info-value"><?php echo htmlspecialchars($companyProfile['Address']); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <!-- Contact Information -->
                <div class="info-section">
                    <h4><i class="fas fa-address-card"></i> Contact Information</h4>
                    
                    <?php if (!empty($companyProfile['Contact_Person'])): ?>
                    <div class="info-row">
                        <span class="info-label">Contact Person:</span>
                        <span class="info-value"><?php echo htmlspecialchars($companyProfile['Contact_Person']); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyProfile['Contact_Phone'])): ?>
                    <div class="info-row">
                        <span class="info-label">Phone:</span>
                        <span class="info-value">
                            <a href="tel:<?php echo htmlspecialchars($companyProfile['Contact_Phone']); ?>">
                                <?php echo htmlspecialchars($companyProfile['Contact_Phone']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($companyProfile['Contact_Email'])): ?>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">
                            <a href="mailto:<?php echo htmlspecialchars($companyProfile['Contact_Email']); ?>">
                                <?php echo htmlspecialchars($companyProfile['Contact_Email']); ?>
                            </a>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Company Description -->
            <?php if (!empty($companyProfile['Company_Description'])): ?>
            <div class="company-description">
                <h4><i class="fas fa-align-left"></i> About Us</h4>
                <p><?php echo nl2br(htmlspecialchars($companyProfile['Company_Description'])); ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Stats Row -->
            <div class="stats-row">
                <div class="stat-badge">
                    <i class="fas fa-briefcase"></i>
                    <span><?php echo $totalJobsCount; ?> Jobs Posted</span>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-users"></i>
                    <span><?php echo $totalAppsCount; ?> Applications Received</span>
                </div>
                <div class="stat-badge">
                    <i class="fas fa-check-circle"></i>
                    <span><?php echo $acceptedAppsCount; ?> Hired</span>
                </div>
            </div>
            
            <?php else: ?>
            <!-- No Profile Yet -->
            <div class="empty-state">
                <i class="fas fa-building"></i>
                <h3 style="margin-bottom: 10px;">No Company Profile Yet</h3>
                <p style="margin-bottom: 20px;">Set up your company profile to start attracting top talent.</p>
                <button class="btn btn-success" onclick="openProfileModal()">
                    <i class="fas fa-plus-circle"></i> Create Company Profile
                </button>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Statistics Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($totalJobsCount); ?></h3>
                    <p>Total Jobs Posted</p>
                    <small><?php echo $activeJobsCount; ?> active</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($totalAppsCount); ?></h3>
                    <p>Total Applications</p>
                    <small>Lifetime applications</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($pendingAppsCount); ?></h3>
                    <p>Pending Review</p>
                    <small>Need attention</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon purple">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo number_format($acceptedAppsCount); ?></h3>
                    <p>Accepted</p>
                    <small>Hired candidates</small>
                </div>
            </div>
        </div>
        
        <!-- Main Content Grid -->
        <div class="main-content-grid">
            <!-- Your Job Postings -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-briefcase"></i>
                        Your Job Postings
                    </h3>
                    <a href="all_jobs.php">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (count($recentJobsList) > 0): ?>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>Job Title</th>
                                <th>Posted Date</th>
                                <th>Applicants</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentJobsList as $job): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($job['Job_Title']); ?></strong>
                                </td>
                                <td>
                                    <i class="far fa-calendar-alt" style="color: var(--gray);"></i>
                                    <?php echo date('M d, Y', strtotime($job['Posted_Date'])); ?>
                                </td>
                                <td>
                                    <a href="view_applicants.php?id=<?php echo $job['Job_ID']; ?>" class="btn-action applicants">
                                        <i class="fas fa-users"></i>
                                        <?php echo $job['app_count']; ?> Applicants
                                    </a>
                                </td>
                                <td>
                                    <span class="status-badge <?php echo ($job['Status'] == 'Active') ? 'status-active' : 'status-closed'; ?>">
                                        <?php echo $job['Status']; ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="edit_job.php?id=<?php echo $job['Job_ID']; ?>" class="btn-action edit" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="view_applicants.php?id=<?php echo $job['Job_ID']; ?>" class="btn-action view" title="View Applicants">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="#" onclick="confirmDeleteJob(<?php echo $job['Job_ID']; ?>)" class="btn-action delete" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-briefcase"></i>
                    <p>You haven't posted any jobs yet.</p>
                    <a href="post_job.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Post Your First Job
                    </a>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Recent Applications -->
            <div class="card">
                <div class="card-header">
                    <h3>
                        <i class="fas fa-clock"></i>
                        Recent Applications
                    </h3>
                    <a href="all_applications.php">
                        View All <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                
                <?php if (count($recentApplications) > 0): ?>
                <div class="applications-list">
                    <?php foreach ($recentApplications as $app): 
                        $statusClass = '';
                        switch($app['Application_Status']) {
                            case 'Pending': $statusClass = 'status-pending'; break;
                            case 'Reviewed': $statusClass = 'status-reviewed'; break;
                            case 'Accepted': $statusClass = 'status-accepted'; break;
                            case 'Rejected': $statusClass = 'status-rejected'; break;
                        }
                    ?>
                    <div class="application-item">
                        <div class="applicant-info">
                            <h4><?php echo htmlspecialchars($app['Full_Name']); ?></h4>
                            <p>
                                <i class="fas fa-briefcase"></i>
                                <?php echo htmlspecialchars($app['Job_Title']); ?>
                            </p>
                            <p>
                                <i class="fas fa-envelope"></i>
                                <?php echo htmlspecialchars($app['Email']); ?>
                            </p>
                        </div>
                        <div class="applicant-meta">
                            <div class="date">
                                <i class="far fa-clock"></i>
                                <?php echo date('M d, Y', strtotime($app['Application_Date'])); ?>
                            </div>
                            <span class="status-badge <?php echo $statusClass; ?>">
                                <?php echo $app['Application_Status']; ?>
                            </span>
                            <div style="margin-top: 8px;">
                                <a href="view_application.php?id=<?php echo $app['Application_ID']; ?>" class="btn-action view">
                                    <i class="fas fa-eye"></i> Review
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <p>No applications received yet</p>
                    <p style="font-size: 0.9rem;">When candidates apply, they'll appear here</p>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h3>
                    <i class="fas fa-bolt"></i>
                    Quick Actions
                </h3>
            </div>
            
            <div class="quick-actions">
                <button onclick="openProfileModal()" class="quick-action-item">
                    <i class="fas fa-building"></i>
                    <span>Edit Profile</span>
                </button>
                
                <a href="post_job.php" class="quick-action-item">
                    <i class="fas fa-plus-circle"></i>
                    <span>Post Job</span>
                </a>
                
                <a href="all_jobs.php" class="quick-action-item">
                    <i class="fas fa-briefcase"></i>
                    <span>Manage Jobs</span>
                </a>
                
                <a href="all_applications.php" class="quick-action-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Applications</span>
                </a>
                
                <a href="reports.php" class="quick-action-item">
                    <i class="fas fa-chart-bar"></i>
                    <span>Reports</span>
                </a>
                
                <a href="#" onclick="scrollToProfile()" class="quick-action-item">
                    <i class="fas fa-eye"></i>
                    <span>View Profile</span>
                </a>
            </div>
        </div>
    </div>
</div>

<!-- Company Profile Modal (Edit Form) -->
<div class="modal-overlay" id="profileModal">
    <div class="modal-container">
        <div class="modal-header">
            <h3>
                <i class="fas fa-building"></i>
                <?php echo $companyProfile ? 'Edit Company Profile' : 'Setup Company Profile'; ?>
            </h3>
            <button class="modal-close" onclick="closeModal('profileModal')">&times;</button>
        </div>
        
        <!-- FIXED: Changed action to empty string to submit to same page -->
        <form method="POST" action="" enctype="multipart/form-data" id="profileForm">
            <!-- Current Logo Display -->
            <?php if ($companyProfile && !empty($companyProfile['Company_Logo'])): ?>
            <div class="current-logo">
                <div class="logo-preview">
                    <img src="../<?php echo htmlspecialchars($companyProfile['Company_Logo']); ?>" alt="Company Logo">
                </div>
                <div>
                    <p style="color: var(--dark); font-weight: 600;">Current Logo</p>
                    <p style="color: var(--gray); font-size: 0.9rem;">Upload a new logo to replace it</p>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Company Logo Upload -->
            <div class="form-group">
                <label>
                    <i class="fas fa-image"></i>
                    Company Logo
                </label>
                <div class="file-upload">
                    <div class="file-upload-label" id="fileUploadLabel">
                        <i class="fas fa-cloud-upload-alt"></i>
                        <div>
                            <span><strong>Click to upload</strong> or drag and drop</span>
                            <div style="font-size: 0.9rem; margin-top: 5px;">PNG, JPG, GIF (Max 2MB)</div>
                        </div>
                    </div>
                    <input type="file" name="company_logo" id="companyLogo" accept="image/*">
                </div>
                <div class="file-info" id="fileInfo">
                    <i class="fas fa-info-circle"></i>
                    <?php if ($companyProfile && !empty($companyProfile['Company_Logo'])): ?>
                        Current logo: <?php echo basename($companyProfile['Company_Logo']); ?>
                    <?php else: ?>
                        No file selected
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Company Information -->
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-building"></i>
                        Company Name *
                    </label>
                    <input type="text" name="company_name" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Company_Name'] ?? ''); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-globe"></i>
                        Website
                    </label>
                    <input type="url" name="company_website" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Company_Website'] ?? ''); ?>" 
                           placeholder="https://example.com">
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-tag"></i>
                        Industry *
                    </label>
                    <select name="industry" class="form-control" required>
                        <option value="">Select Industry</option>
                        <option value="Technology" <?php echo ($companyProfile['Industry'] ?? '') == 'Technology' ? 'selected' : ''; ?>>Technology</option>
                        <option value="Healthcare" <?php echo ($companyProfile['Industry'] ?? '') == 'Healthcare' ? 'selected' : ''; ?>>Healthcare</option>
                        <option value="Finance" <?php echo ($companyProfile['Industry'] ?? '') == 'Finance' ? 'selected' : ''; ?>>Finance</option>
                        <option value="Education" <?php echo ($companyProfile['Industry'] ?? '') == 'Education' ? 'selected' : ''; ?>>Education</option>
                        <option value="Manufacturing" <?php echo ($companyProfile['Industry'] ?? '') == 'Manufacturing' ? 'selected' : ''; ?>>Manufacturing</option>
                        <option value="Retail" <?php echo ($companyProfile['Industry'] ?? '') == 'Retail' ? 'selected' : ''; ?>>Retail</option>
                        <option value="Construction" <?php echo ($companyProfile['Industry'] ?? '') == 'Construction' ? 'selected' : ''; ?>>Construction</option>
                        <option value="Transportation" <?php echo ($companyProfile['Industry'] ?? '') == 'Transportation' ? 'selected' : ''; ?>>Transportation</option>
                        <option value="Hospitality" <?php echo ($companyProfile['Industry'] ?? '') == 'Hospitality' ? 'selected' : ''; ?>>Hospitality</option>
                        <option value="Other" <?php echo ($companyProfile['Industry'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-users"></i>
                        Company Size
                    </label>
                    <select name="company_size" class="form-control">
                        <option value="">Select Size</option>
                        <option value="1-10" <?php echo ($companyProfile['Company_Size'] ?? '') == '1-10' ? 'selected' : ''; ?>>1-10 employees</option>
                        <option value="11-50" <?php echo ($companyProfile['Company_Size'] ?? '') == '11-50' ? 'selected' : ''; ?>>11-50 employees</option>
                        <option value="51-200" <?php echo ($companyProfile['Company_Size'] ?? '') == '51-200' ? 'selected' : ''; ?>>51-200 employees</option>
                        <option value="201-500" <?php echo ($companyProfile['Company_Size'] ?? '') == '201-500' ? 'selected' : ''; ?>>201-500 employees</option>
                        <option value="501-1000" <?php echo ($companyProfile['Company_Size'] ?? '') == '501-1000' ? 'selected' : ''; ?>>501-1000 employees</option>
                        <option value="1000+" <?php echo ($companyProfile['Company_Size'] ?? '') == '1000+' ? 'selected' : ''; ?>>1000+ employees</option>
                    </select>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-calendar"></i>
                        Founded Year
                    </label>
                    <input type="number" name="founded_year" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Founded_Year'] ?? ''); ?>" 
                           min="1800" max="<?php echo date('Y'); ?>" placeholder="YYYY">
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-map-marker-alt"></i>
                        Address
                    </label>
                    <input type="text" name="address" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Address'] ?? ''); ?>" 
                           placeholder="City, Country">
                </div>
            </div>
            
            <div class="form-group">
                <label>
                    <i class="fas fa-align-left"></i>
                    Company Description *
                </label>
                <textarea name="company_description" class="form-control" rows="4" 
                          placeholder="Tell job seekers about your company..." required><?php echo htmlspecialchars($companyProfile['Company_Description'] ?? ''); ?></textarea>
            </div>
            
            <!-- Contact Information -->
            <h4 style="color: var(--dark); margin: 20px 0 15px;">Contact Information</h4>
            
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-user"></i>
                        Contact Person *
                    </label>
                    <input type="text" name="contact_person" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Contact_Person'] ?? $_SESSION['full_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-phone"></i>
                        Contact Phone *
                    </label>
                    <input type="tel" name="contact_phone" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Contact_Phone'] ?? ''); ?>" 
                           placeholder="+251 XXX XXX XXX" required>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label>
                        <i class="fas fa-envelope"></i>
                        Contact Email *
                    </label>
                    <input type="email" name="contact_email" class="form-control" 
                           value="<?php echo htmlspecialchars($companyProfile['Contact_Email'] ?? $_SESSION['email']); ?>" required>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-outline" onclick="closeModal('profileModal')">Cancel</button>
                <button type="submit" name="update_profile" class="btn btn-success">
                    <i class="fas fa-save"></i>
                    Save Profile
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
// Modal functions
function openModal(modalId) {
    document.getElementById(modalId).classList.add('active');
}

function closeModal(modalId) {
    document.getElementById(modalId).classList.remove('active');
}

function openProfileModal() {
    openModal('profileModal');
}

// Scroll to profile section
function scrollToProfile() {
    document.getElementById('company-profile-view').scrollIntoView({
        behavior: 'smooth',
        block: 'start'
    });
}

// File upload handling
const fileInput = document.getElementById('companyLogo');
const fileInfo = document.getElementById('fileInfo');
const fileLabel = document.getElementById('fileUploadLabel');

if (fileInput) {
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            // Validate file type
            if (!file.type.match('image.*')) {
                alert('Please upload an image file (PNG, JPG, GIF)');
                this.value = '';
                fileInfo.innerHTML = '<i class="fas fa-info-circle"></i> No file selected';
                fileLabel.style.borderColor = 'var(--secondary)';
                fileLabel.style.background = '#f8f9fa';
                return;
            }
            
            // Validate file size (2MB max)
            if (file.size > 2 * 1024 * 1024) {
                alert('File size must be less than 2MB');
                this.value = '';
                fileInfo.innerHTML = '<i class="fas fa-info-circle"></i> No file selected';
                fileLabel.style.borderColor = 'var(--secondary)';
                fileLabel.style.background = '#f8f9fa';
            } else {
                fileInfo.innerHTML = `<i class="fas fa-check-circle" style="color: var(--success);"></i> Selected: ${file.name} (${fileSize} MB)`;
                fileLabel.style.borderColor = 'var(--success)';
                fileLabel.style.background = '#e8f5e9';
            }
        } else {
            fileInfo.innerHTML = '<i class="fas fa-info-circle"></i> No file selected';
            fileLabel.style.borderColor = 'var(--secondary)';
            fileLabel.style.background = '#f8f9fa';
        }
    });
}

// Confirm delete job
function confirmDeleteJob(jobId) {
    if (confirm('Are you sure you want to delete this job posting? This action cannot be undone.')) {
        window.location.href = 'delete_job.php?id=' + jobId;
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

// Form submission loading state
document.getElementById('profileForm')?.addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving...';
    submitBtn.disabled = true;
});

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    if (event.target.classList.contains('modal-overlay')) {
        event.target.classList.remove('active');
    }
});

// Keyboard shortcut
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 'p') {
        e.preventDefault();
        window.location.href = 'post_job.php';
    }
    
    if (e.ctrlKey && e.key === 'e' && !e.target.matches('input, textarea')) {
        e.preventDefault();
        openProfileModal();
    }
    
    if (e.ctrlKey && e.key === 'v') {
        e.preventDefault();
        scrollToProfile();
    }
});
</script>

<?php include '../includes/footer.php'; ?>