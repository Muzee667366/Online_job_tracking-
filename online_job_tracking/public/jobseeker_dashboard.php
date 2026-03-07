<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security check: Ensure user is a Job Seeker (Role 3)
if ($_SESSION['role_id'] != 3) {
    $_SESSION['error_message'] = "Access denied. Only job seekers can access this page.";
    header("Location: ../public/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get statistics
try {
    // Total applications
    $totalApps = $conn->prepare("SELECT COUNT(*) FROM tbl_applications WHERE User_ID = ?");
    $totalApps->execute([$user_id]);
    $totalApplications = $totalApps->fetchColumn();
    
    // Pending applications
    $pendingApps = $conn->prepare("SELECT COUNT(*) FROM tbl_applications WHERE User_ID = ? AND Application_Status = 'Pending'");
    $pendingApps->execute([$user_id]);
    $pendingCount = $pendingApps->fetchColumn();
    
    // Saved jobs count (if table exists)
    $savedJobs = $conn->prepare("SELECT COUNT(*) FROM tbl_saved_jobs WHERE User_ID = ?");
    $savedJobs->execute([$user_id]);
    $savedCount = $savedJobs->fetchColumn();
    
    // Profile completion percentage
    $profile = $conn->prepare("SELECT * FROM tbl_job_seeker_profiles WHERE Seeker_ID = ?");
    $profile->execute([$user_id]);
    $profileData = $profile->fetch(PDO::FETCH_ASSOC);
    
    $profileFields = 0;
    $totalFields = 10;
    if ($profileData) {
        if (!empty($profileData['Resume_Path'])) $profileFields++;
        if (!empty($profileData['Skills'])) $profileFields++;
        if (!empty($profileData['Education_Level'])) $profileFields++;
        if (!empty($profileData['Years_of_Experience'])) $profileFields++;
        if (!empty($profileData['Current_Job_Title'])) $profileFields++;
    }
    $profileCompletion = round(($profileFields / $totalFields) * 100);
    
} catch (PDOException $e) {
    error_log("Job Seeker dashboard error: " . $e->getMessage());
    $totalApplications = 0;
    $pendingCount = 0;
    $savedCount = 0;
    $profileCompletion = 0;
}

include '../includes/header.php'; 
?>

<style>
/* Modern Job Seeker Dashboard Styles */
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

.profile-avatar {
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

.btn-danger {
    background: var(--accent);
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

.stat-icon.applications {
    background: var(--gradient);
}

.stat-icon.pending {
    background: var(--gradient-warning);
}

.stat-icon.saved {
    background: var(--gradient-success);
}

.stat-icon.profile {
    background: linear-gradient(135deg, #9b59b6, #8e44ad);
}

.stat-details h3 {
    font-size: 1.8rem;
    color: var(--dark);
    margin-bottom: 5px;
}

.stat-details p {
    color: var(--gray);
    font-size: 0.9rem;
}

/* Profile Completion Bar */
.progress-bar {
    width: 100%;
    height: 8px;
    background: #f0f0f0;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 10px;
}

.progress-fill {
    height: 100%;
    background: var(--gradient-success);
    border-radius: 4px;
    transition: width 0.3s ease;
}

/* Search Section */
.search-section {
    background: white;
    border-radius: 15px;
    padding: 20px;
    margin-bottom: 25px;
    box-shadow: var(--shadow-sm);
    border: 1px solid #e0e0e0;
}

.search-box {
    position: relative;
}

.search-box i {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    font-size: 1.2rem;
}

#jobSearch {
    width: 100%;
    padding: 15px 15px 15px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

#jobSearch:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

/* Section Cards */
.section-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-md);
    border: 1px solid rgba(255, 255, 255, 0.2);
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f0f0f0;
}

.section-header h3 {
    color: var(--dark);
    font-size: 1.4rem;
    display: flex;
    align-items: center;
    gap: 10px;
}

.section-header h3 i {
    color: var(--secondary);
}

.section-header a {
    color: var(--secondary);
    text-decoration: none;
    display: flex;
    align-items: center;
    gap: 5px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.section-header a:hover {
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

tr {
    transition: background 0.3s ease;
}

tr:hover td {
    background: #f8f9fa;
}

/* Status Badges */
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
    display: inline-block;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
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

.status-withdrawn {
    background: #e2e3e5;
    color: #383d41;
}

/* Action Buttons */
.btn-apply {
    background: var(--secondary);
    color: white;
    padding: 6px 15px;
    border-radius: 5px;
    text-decoration: none;
    font-size: 0.85rem;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

.btn-apply:hover {
    background: var(--primary);
    transform: translateY(-2px);
}

.btn-view {
    color: var(--secondary);
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 0.9rem;
    transition: all 0.3s ease;
}

.btn-view:hover {
    color: var(--primary);
    transform: translateX(3px);
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

/* Recommended Jobs */
.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.job-card {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 20px;
    transition: all 0.3s ease;
    border: 1px solid #e0e0e0;
}

.job-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-md);
    border-color: var(--secondary);
}

.job-card h4 {
    color: var(--dark);
    margin-bottom: 10px;
    font-size: 1.2rem;
}

.job-card .company {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.job-card .category {
    background: #e8f0fe;
    color: var(--secondary);
    padding: 4px 10px;
    border-radius: 5px;
    font-size: 0.8rem;
    display: inline-block;
    margin-bottom: 15px;
}

.job-card .footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
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
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
    
    .section-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    th, td {
        padding: 10px 5px;
        font-size: 0.9rem;
    }
    
    .jobs-grid {
        grid-template-columns: 1fr;
    }
}

/* Quick Tips */
.tips-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 25px;
    margin-top: 30px;
    color: white;
}

.tips-section h4 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 15px;
    font-size: 1.2rem;
}

.tips-section ul {
    list-style: none;
    padding: 0;
}

.tips-section li {
    padding: 8px 0;
    display: flex;
    align-items: center;
    gap: 10px;
}

.tips-section li i {
    color: #ffd700;
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
                <div class="profile-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="welcome-text">
                    <h1>Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?>!</h1>
                    <p>
                        <i class="fas fa-map-marker-alt"></i>
                        Job Seeker Dashboard
                    </p>
                </div>
            </div>
            
            <div class="header-actions">
                <a href="profile.php" class="btn btn-outline">
                    <i class="fas fa-user-edit"></i>
                    Complete Profile (<?php echo $profileCompletion; ?>%)
                </a>
                <a href="saved_jobs.php" class="btn btn-primary">
                    <i class="fas fa-bookmark"></i>
                    Saved Jobs (<?php echo $savedCount; ?>)
                </a>
                <a href="../modules/auth/logout.php" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon applications">
                    <i class="fas fa-file-alt"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $totalApplications; ?></h3>
                    <p>Total Applications</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon pending">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $pendingCount; ?></h3>
                    <p>Pending Reviews</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon saved">
                    <i class="fas fa-bookmark"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $savedCount; ?></h3>
                    <p>Saved Jobs</p>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon profile">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo $profileCompletion; ?>%</h3>
                    <p>Profile Complete</p>
                    <div class="progress-bar">
                        <div class="progress-fill" style="width: <?php echo $profileCompletion; ?>%;"></div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Available Jobs Section -->
        <div class="section-card">
            <div class="section-header">
                <h3>
                    <i class="fas fa-briefcase"></i>
                    Available Job Vacancies
                </h3>
                <a href="jobs.php">
                    Browse All Jobs <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <!-- Search Box -->
            <div class="search-section">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="jobSearch" placeholder="🔍 Search by Job Title or Category..." onkeyup="filterJobs()">
                </div>
            </div>
            
            <div class="table-responsive">
                <table id="jobsTable">
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Category</th>
                            <th>Location</th>
                            <th>Type</th>
                            <th>Posted</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Fetch jobs from database with more details
                        $stmt = $conn->query("SELECT * FROM tbl_jobs WHERE Status = 'Active' ORDER BY Job_ID DESC LIMIT 10");
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                            $posted_date = isset($row['Posted_Date']) ? date('M d', strtotime($row['Posted_Date'])) : 'Recent';
                            $location = $row['Location'] ?? 'Addis Ababa';
                            $job_type = $row['Job_Type'] ?? 'Full Time';
                            
                            echo "<tr>";
                            echo "<td style='padding: 12px; border-bottom: 1px solid #eee;'><strong>" . htmlspecialchars($row['Job_Title']) . "</strong></td>";
                            echo "<td style='padding: 12px; border-bottom: 1px solid #eee;'><span style='background: #e8f0fe; color: var(--secondary); padding: 4px 8px; border-radius: 5px;'>" . htmlspecialchars($row['Job_Category']) . "</span></td>";
                            echo "<td style='padding: 12px; border-bottom: 1px solid #eee;'><i class='fas fa-map-marker-alt' style='color: var(--gray); margin-right: 5px;'></i>" . htmlspecialchars($location) . "</td>";
                            echo "<td style='padding: 12px; border-bottom: 1px solid #eee;'>" . htmlspecialchars($job_type) . "</td>";
                            echo "<td style='padding: 12px; border-bottom: 1px solid #eee;'><i class='far fa-calendar' style='color: var(--gray); margin-right: 5px;'></i>" . $posted_date . "</td>";
                            echo "<td style='padding: 12px; border-bottom: 1px solid #eee;'>
                                    <a href='apply_job.php?id=" . $row['Job_ID'] . "' class='btn-apply'>
                                        <i class='fas fa-paper-plane'></i> Apply Now
                                    </a>
                                  </td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <?php if ($stmt->rowCount() == 0): ?>
            <div class="empty-state">
                <i class="fas fa-briefcase"></i>
                <p>No jobs available at the moment. Check back later!</p>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- My Applications Section -->
        <div class="section-card">
            <div class="section-header">
                <h3>
                    <i class="fas fa-history"></i>
                    My Applications
                </h3>
                <a href="my_applications.php">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Job Title</th>
                            <th>Company</th>
                            <th>Date Applied</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $conn->prepare("
                            SELECT a.*, j.Job_Title, j.Job_Category, u.Full_Name as Company_Name 
                            FROM tbl_applications a 
                            JOIN tbl_jobs j ON a.Job_ID = j.Job_ID 
                            LEFT JOIN tbl_users u ON j.Employer_ID = u.User_ID
                            WHERE a.User_ID = ? 
                            ORDER BY a.Application_Date DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$_SESSION['user_id']]);
                        
                        if ($stmt->rowCount() > 0):
                            while($app = $stmt->fetch(PDO::FETCH_ASSOC)):
                                $status = $app['Application_Status'] ?? 'Pending';
                                $status_class = '';
                                
                                switch(strtolower($status)) {
                                    case 'pending':
                                        $status_class = 'status-pending';
                                        break;
                                    case 'reviewed':
                                        $status_class = 'status-reviewed';
                                        break;
                                    case 'accepted':
                                        $status_class = 'status-accepted';
                                        break;
                                    case 'rejected':
                                        $status_class = 'status-rejected';
                                        break;
                                    case 'withdrawn':
                                        $status_class = 'status-withdrawn';
                                        break;
                                    default:
                                        $status_class = 'status-pending';
                                }
                        ?>
                        <tr>
                            <td style='padding:12px; border-bottom:1px solid #eee;'>
                                <strong><?php echo htmlspecialchars($app['Job_Title']); ?></strong>
                            </td>
                            <td style='padding:12px; border-bottom:1px solid #eee;'>
                                <i class='fas fa-building' style='color: var(--gray); margin-right: 5px;'></i>
                                <?php echo htmlspecialchars($app['Company_Name'] ?? 'Company Name'); ?>
                            </td>
                            <td style='padding:12px; border-bottom:1px solid #eee;'>
                                <i class='far fa-calendar-alt' style='color: var(--gray); margin-right: 5px;'></i>
                                <?php echo date('M d, Y', strtotime($app['Application_Date'])); ?>
                            </td>
                            <td style='padding:12px; border-bottom:1px solid #eee;'>
                                <span class="status-badge <?php echo $status_class; ?>">
                                    <?php echo $status; ?>
                                </span>
                            </td>
                            <td style='padding:12px; border-bottom:1px solid #eee;'>
                                <a href='view_application.php?id=<?php echo $app['Application_ID']; ?>' class='btn-view'>
                                    View Details <i class='fas fa-arrow-right'></i>
                                </a>
                            </td>
                        </tr>
                        <?php 
                            endwhile;
                        else:
                        ?>
                        <tr>
                            <td colspan="5" style="padding: 40px; text-align: center;">
                                <div class="empty-state">
                                    <i class="fas fa-inbox"></i>
                                    <p>You haven't applied to any jobs yet.</p>
                                    <a href="jobs.php" class="btn btn-primary" style="display: inline-block; padding: 8px 20px;">
                                        Browse Jobs
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Recommended Jobs (Optional) -->
        <div class="section-card">
            <div class="section-header">
                <h3>
                    <i class="fas fa-star"></i>
                    Recommended for You
                </h3>
                <a href="recommended_jobs.php">
                    View All <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            
            <div class="jobs-grid">
                <?php
                // Simple recommendation based on user's previous applications
                $rec_stmt = $conn->prepare("
                    SELECT DISTINCT j.*, u.Full_Name as Company_Name 
                    FROM tbl_jobs j
                    LEFT JOIN tbl_users u ON j.Employer_ID = u.User_ID
                    WHERE j.Status = 'Active' 
                    AND j.Job_ID NOT IN (
                        SELECT Job_ID FROM tbl_applications WHERE User_ID = ?
                    )
                    ORDER BY j.Posted_Date DESC 
                    LIMIT 3
                ");
                $rec_stmt->execute([$_SESSION['user_id']]);
                
                while($rec_job = $rec_stmt->fetch(PDO::FETCH_ASSOC)):
                ?>
                <div class="job-card">
                    <h4><?php echo htmlspecialchars($rec_job['Job_Title']); ?></h4>
                    <div class="company">
                        <i class="fas fa-building"></i>
                        <?php echo htmlspecialchars($rec_job['Company_Name'] ?? 'Company Name'); ?>
                    </div>
                    <div>
                        <span class="category"><?php echo htmlspecialchars($rec_job['Job_Category']); ?></span>
                    </div>
                    <p style="color: var(--gray); font-size: 0.9rem; margin-top: 10px;">
                        <?php echo substr(htmlspecialchars($rec_job['Job_Description'] ?? ''), 0, 80); ?>...
                    </p>
                    <div class="footer">
                        <span style="color: var(--secondary); font-weight: 600;">
                            <i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($rec_job['Location'] ?? 'Addis Ababa'); ?>
                        </span>
                        <a href="apply_job.php?id=<?php echo $rec_job['Job_ID']; ?>" class="btn-apply">
                            Apply <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>
        
        <!-- Quick Tips Section -->
        <div class="tips-section">
            <h4>
                <i class="fas fa-lightbulb"></i>
                Quick Tips for Job Seekers
            </h4>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                <ul>
                    <li><i class="fas fa-check-circle"></i> Complete your profile to 100% for better visibility</li>
                    <li><i class="fas fa-check-circle"></i> Upload a professional resume in PDF format</li>
                    <li><i class="fas fa-check-circle"></i> Apply to jobs that match your skills</li>
                </ul>
                <ul>
                    <li><i class="fas fa-check-circle"></i> Check your application status regularly</li>
                    <li><i class="fas fa-check-circle"></i> Save jobs you're interested in for later</li>
                    <li><i class="fas fa-check-circle"></i> Keep your contact information updated</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Search Filter -->
<script>
function filterJobs() {
    var input, filter, table, tr, td, i, txtValue;
    input = document.getElementById("jobSearch");
    filter = input.value.toUpperCase();
    table = document.getElementById("jobsTable");
    tr = table.getElementsByTagName("tr");
    
    for (i = 0; i < tr.length; i++) {
        td = tr[i].getElementsByTagName("td")[0]; // Job Title column
        td2 = tr[i].getElementsByTagName("td")[1]; // Category column
        
        if (td || td2) {
            txtValue = td.textContent || td.innerText;
            txtValue2 = td2.textContent || td2.innerText;
            if (txtValue.toUpperCase().indexOf(filter) > -1 || txtValue2.toUpperCase().indexOf(filter) > -1) {
                tr[i].style.display = "";
            } else {
                tr[i].style.display = "none";
            }
        }
    }
}

// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);

// Keyboard shortcut for search
document.addEventListener('keydown', function(e) {
    if (e.key === '/' && !e.ctrlKey && !e.altKey && !e.metaKey) {
        e.preventDefault();
        document.getElementById('jobSearch').focus();
    }
});

// Add animation to cards on scroll
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.animation = 'fadeIn 0.8s ease-out';
            observer.unobserve(entry.target);
        }
    });
}, observerOptions);

document.querySelectorAll('.stat-card, .section-card, .job-card').forEach(el => {
    observer.observe(el);
});
</script>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Additional CSS for alerts -->
<style>
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

@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(-20px);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
</style>

<?php include '../includes/footer.php'; ?>