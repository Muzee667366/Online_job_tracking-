<?php 
require_once '../config/db_connect.php';
include '../includes/header.php'; 

// Get filter parameters
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$job_type = isset($_GET['job_type']) ? $_GET['job_type'] : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$experience = isset($_GET['experience']) ? $_GET['experience'] : '';
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

// Get unique categories for filter dropdown
$categories = $conn->query("SELECT DISTINCT Job_Category FROM tbl_jobs WHERE Status = 'Active' AND Job_Category IS NOT NULL")->fetchAll(PDO::FETCH_COLUMN);

// Get total jobs count for pagination
$count_sql = "SELECT COUNT(*) FROM tbl_jobs WHERE Status = 'Active'";
$count_params = [];

if ($category) {
    $count_sql .= " AND Job_Category LIKE ?";
    $count_params[] = "%$category%";
}
if ($job_type) {
    $count_sql .= " AND Job_Type = ?";
    $count_params[] = $job_type;
}
if ($location) {
    $count_sql .= " AND Location LIKE ?";
    $count_params[] = "%$location%";
}

$count_stmt = $conn->prepare($count_sql);
$count_stmt->execute($count_params);
$total_jobs = $count_stmt->fetchColumn();
$total_pages = ceil($total_jobs / $limit);
?>

<style>
/* Modern Job Search Page Styles */
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
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.1);
    --shadow-md: 0 5px 20px rgba(0,0,0,0.15);
    --shadow-lg: 0 10px 30px rgba(0,0,0,0.2);
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Page Wrapper */
.jobs-page-wrapper {
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

/* Main Container */
.jobs-container {
    max-width: 1200px;
    margin: 0 auto;
    position: relative;
    z-index: 10;
    animation: fadeIn 0.8s ease-out;
}

/* Page Header */
.page-header {
    text-align: center;
    margin-bottom: 40px;
}

.page-header h1 {
    font-size: 2.8rem;
    color: white;
    margin-bottom: 10px;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.page-header p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1.2rem;
}

/* Search Section */
.search-section {
    background: white;
    border-radius: 20px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: var(--shadow-lg);
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
}

.search-form {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
    align-items: end;
}

.form-group {
    margin-bottom: 0;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    color: var(--dark);
    font-weight: 600;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group label i {
    color: var(--secondary);
    margin-right: 5px;
}

.form-control {
    width: 100%;
    padding: 12px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
}

.form-control:hover {
    border-color: var(--secondary);
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
    background: white;
}

.search-btn {
    background: var(--gradient);
    color: white;
    border: none;
    padding: 12px 30px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    height: 100%;
}

.search-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.reset-btn {
    background: var(--gray);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.reset-btn:hover {
    background: var(--dark);
    transform: translateY(-2px);
}

/* Active Filters */
.active-filters {
    margin-top: 20px;
    display: flex;
    flex-wrap: wrap;
    gap: 10px;
    align-items: center;
}

.filter-tag {
    background: #e8f0fe;
    color: var(--secondary);
    padding: 8px 15px;
    border-radius: 30px;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.filter-tag i {
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tag i:hover {
    color: var(--accent);
}

/* Results Header */
.results-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.results-count {
    background: white;
    padding: 10px 20px;
    border-radius: 30px;
    box-shadow: var(--shadow-sm);
    color: var(--dark);
    font-weight: 500;
}

.results-count span {
    color: var(--secondary);
    font-weight: 700;
    font-size: 1.2rem;
}

.sort-section {
    display: flex;
    align-items: center;
    gap: 10px;
}

.sort-section select {
    padding: 8px 15px;
    border: 2px solid #e0e0e0;
    border-radius: 8px;
    font-size: 0.95rem;
    background: white;
    cursor: pointer;
}

/* Jobs Grid */
.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.job-card {
    background: white;
    border-radius: 20px;
    padding: 25px;
    box-shadow: var(--shadow-md);
    transition: all 0.3s ease;
    border: 1px solid rgba(255, 255, 255, 0.2);
    backdrop-filter: blur(10px);
    position: relative;
    overflow: hidden;
    animation: fadeInUp 0.6s ease-out;
}

.job-card:hover {
    transform: translateY(-5px);
    box-shadow: var(--shadow-lg);
}

.job-card::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.job-card:hover::before {
    transform: scaleX(1);
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: start;
    margin-bottom: 15px;
}

.job-title {
    font-size: 1.3rem;
    color: var(--dark);
    margin-bottom: 5px;
    font-weight: 600;
}

.job-company {
    color: var(--gray);
    font-size: 0.95rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.job-badge {
    padding: 5px 10px;
    border-radius: 5px;
    font-size: 0.8rem;
    font-weight: 600;
}

.badge-fulltime {
    background: #d4edda;
    color: #155724;
}

.badge-parttime {
    background: #fff3cd;
    color: #856404;
}

.badge-contract {
    background: #d1ecf1;
    color: #0c5460;
}

.badge-remote {
    background: #e8f0fe;
    color: var(--secondary);
}

.job-details {
    margin: 15px 0;
    padding: 15px 0;
    border-top: 1px solid #f0f0f0;
    border-bottom: 1px solid #f0f0f0;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: var(--gray);
    font-size: 0.95rem;
}

.detail-item i {
    width: 20px;
    color: var(--secondary);
}

.job-description {
    color: var(--gray);
    line-height: 1.6;
    margin-bottom: 20px;
    font-size: 0.95rem;
}

.job-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.job-salary {
    background: var(--gradient);
    color: white;
    padding: 8px 15px;
    border-radius: 30px;
    font-size: 0.9rem;
    font-weight: 600;
}

.btn-apply {
    background: var(--secondary);
    color: white;
    padding: 10px 20px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
}

.btn-apply:hover {
    background: var(--primary);
    transform: translateX(5px);
}

.btn-apply i {
    font-size: 0.9rem;
}

/* Empty State */
.empty-state {
    text-align: center;
    padding: 60px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: var(--shadow-md);
}

.empty-state i {
    font-size: 4rem;
    color: var(--gray);
    margin-bottom: 20px;
    opacity: 0.5;
}

.empty-state h3 {
    color: var(--dark);
    font-size: 1.8rem;
    margin-bottom: 10px;
}

.empty-state p {
    color: var(--gray);
    margin-bottom: 30px;
}

/* Pagination */
.pagination {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.page-item {
    list-style: none;
}

.page-link {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: white;
    color: var(--dark);
    text-decoration: none;
    border-radius: 8px;
    transition: all 0.3s ease;
    font-weight: 500;
}

.page-link:hover {
    background: var(--secondary);
    color: white;
    transform: translateY(-2px);
}

.page-link.active {
    background: var(--gradient);
    color: white;
}

.page-dots {
    display: flex;
    align-items: center;
    color: white;
    padding: 0 10px;
}

/* Quick Stats */
.quick-stats {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.stat-pill {
    background: rgba(255, 255, 255, 0.1);
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.2);
    border-radius: 50px;
    padding: 15px 25px;
    text-align: center;
    color: white;
    transition: all 0.3s ease;
}

.stat-pill:hover {
    transform: translateY(-3px);
    background: rgba(255, 255, 255, 0.2);
}

.stat-pill .number {
    font-size: 1.8rem;
    font-weight: 700;
    display: block;
}

.stat-pill .label {
    font-size: 0.9rem;
    opacity: 0.9;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(5deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .page-header h1 {
        font-size: 2rem;
    }
    
    .search-form {
        grid-template-columns: 1fr;
    }
    
    .jobs-grid {
        grid-template-columns: 1fr;
    }
    
    .results-header {
        flex-direction: column;
        align-items: stretch;
    }
    
    .sort-section {
        justify-content: space-between;
    }
}

/* Loading Skeleton */
.skeleton {
    background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
    background-size: 200% 100%;
    animation: loading 1.5s infinite;
}

@keyframes loading {
    0% { background-position: 200% 0; }
    100% { background-position: -200% 0; }
}
</style>

<div class="jobs-page-wrapper">
    <!-- Animated Background -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    
    <div class="jobs-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1><i class="fas fa-briefcase"></i> Find Your Dream Job</h1>
            <p>Discover thousands of job opportunities tailored to your skills</p>
        </div>
        
        <!-- Quick Stats -->
        <?php
        $total_active = $conn->query("SELECT COUNT(*) FROM tbl_jobs WHERE Status = 'Active'")->fetchColumn();
        $total_categories = $conn->query("SELECT COUNT(DISTINCT Job_Category) FROM tbl_jobs WHERE Status = 'Active'")->fetchColumn();
        $total_companies = $conn->query("SELECT COUNT(DISTINCT Employer_ID) FROM tbl_jobs WHERE Status = 'Active'")->fetchColumn();
        ?>
        <div class="quick-stats">
            <div class="stat-pill">
                <span class="number"><?php echo number_format($total_active); ?></span>
                <span class="label">Active Jobs</span>
            </div>
            <div class="stat-pill">
                <span class="number"><?php echo number_format($total_categories); ?></span>
                <span class="label">Categories</span>
            </div>
            <div class="stat-pill">
                <span class="number"><?php echo number_format($total_companies); ?></span>
                <span class="label">Companies</span>
            </div>
            <div class="stat-pill">
                <span class="number">🚀</span>
                <span class="label">Hiring Now</span>
            </div>
        </div>
        
        <!-- Search Section -->
        <div class="search-section">
            <form method="GET" class="search-form">
                <div class="form-group">
                    <label><i class="fas fa-tag"></i> Category</label>
                    <select name="category" class="form-control">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat); ?>" <?php echo $category == $cat ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-clock"></i> Job Type</label>
                    <select name="job_type" class="form-control">
                        <option value="">All Types</option>
                        <option value="Full Time" <?php echo $job_type == 'Full Time' ? 'selected' : ''; ?>>Full Time</option>
                        <option value="Part Time" <?php echo $job_type == 'Part Time' ? 'selected' : ''; ?>>Part Time</option>
                        <option value="Contract" <?php echo $job_type == 'Contract' ? 'selected' : ''; ?>>Contract</option>
                        <option value="Internship" <?php echo $job_type == 'Internship' ? 'selected' : ''; ?>>Internship</option>
                        <option value="Remote" <?php echo $job_type == 'Remote' ? 'selected' : ''; ?>>Remote</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" name="location" class="form-control" placeholder="City or Region" value="<?php echo htmlspecialchars($location); ?>">
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-chart-line"></i> Experience</label>
                    <select name="experience" class="form-control">
                        <option value="">Any Experience</option>
                        <option value="entry" <?php echo $experience == 'entry' ? 'selected' : ''; ?>>Entry Level</option>
                        <option value="mid" <?php echo $experience == 'mid' ? 'selected' : ''; ?>>Mid Level</option>
                        <option value="senior" <?php echo $experience == 'senior' ? 'selected' : ''; ?>>Senior Level</option>
                        <option value="executive" <?php echo $experience == 'executive' ? 'selected' : ''; ?>>Executive</option>
                    </select>
                </div>
                
                <div>
                    <button type="submit" class="search-btn">
                        <i class="fas fa-search"></i> Search Jobs
                    </button>
                </div>
                
                <div>
                    <a href="jobs.php" class="reset-btn">
                        <i class="fas fa-undo"></i> Reset
                    </a>
                </div>
            </form>
            
            <!-- Active Filters -->
            <?php if ($category || $job_type || $location || $experience): ?>
            <div class="active-filters">
                <span style="color: var(--gray);">Active Filters:</span>
                <?php if ($category): ?>
                <span class="filter-tag">
                    <?php echo htmlspecialchars($category); ?>
                    <a href="?<?php 
                        $params = $_GET;
                        unset($params['category']);
                        echo http_build_query($params);
                    ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
                
                <?php if ($job_type): ?>
                <span class="filter-tag">
                    <?php echo htmlspecialchars($job_type); ?>
                    <a href="?<?php 
                        $params = $_GET;
                        unset($params['job_type']);
                        echo http_build_query($params);
                    ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
                
                <?php if ($location): ?>
                <span class="filter-tag">
                    <?php echo htmlspecialchars($location); ?>
                    <a href="?<?php 
                        $params = $_GET;
                        unset($params['location']);
                        echo http_build_query($params);
                    ?>"><i class="fas fa-times"></i></a>
                </span>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Results Header -->
        <div class="results-header">
            <div class="results-count">
                <i class="fas fa-briefcase" style="margin-right: 8px; color: var(--secondary);"></i>
                <span><?php echo number_format($total_jobs); ?></span> jobs found
            </div>
            
            <div class="sort-section">
                <label for="sort" style="color: white;">Sort by:</label>
                <select id="sort" onchange="window.location.href='?<?php 
                    $params = $_GET;
                    unset($params['sort']);
                    echo http_build_query($params);
                ?>&sort='+this.value">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>Newest First</option>
                    <option value="oldest" <?php echo $sort == 'oldest' ? 'selected' : ''; ?>>Oldest First</option>
                    <option value="title" <?php echo $sort == 'title' ? 'selected' : ''; ?>>Job Title</option>
                </select>
            </div>
        </div>
        
        <!-- Jobs Grid -->
        <div class="jobs-grid">
            <?php
            // Build the main query
            $sql = "SELECT j.*, u.Full_Name as Company_Name 
                    FROM tbl_jobs j 
                    LEFT JOIN tbl_users u ON j.Employer_ID = u.User_ID 
                    WHERE j.Status = 'Active'";
            $params = [];
            
            if ($category) {
                $sql .= " AND j.Job_Category LIKE ?";
                $params[] = "%$category%";
            }
            if ($job_type) {
                $sql .= " AND j.Job_Type = ?";
                $params[] = $job_type;
            }
            if ($location) {
                $sql .= " AND j.Location LIKE ?";
                $params[] = "%$location%";
            }
            
            // Add sorting
            switch($sort) {
                case 'oldest':
                    $sql .= " ORDER BY j.Posted_Date ASC";
                    break;
                case 'title':
                    $sql .= " ORDER BY j.Job_Title ASC";
                    break;
                default:
                    $sql .= " ORDER BY j.Posted_Date DESC";
            }
            
            // Add pagination
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
            
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
            
            if ($stmt->rowCount() > 0):
                while ($job = $stmt->fetch(PDO::FETCH_ASSOC)):
                    // Determine badge class
                    $badge_class = 'badge-fulltime';
                    $job_type_display = $job['Job_Type'] ?? 'Full Time';
                    
                    if (stripos($job_type_display, 'part') !== false) {
                        $badge_class = 'badge-parttime';
                    } elseif (stripos($job_type_display, 'contract') !== false) {
                        $badge_class = 'badge-contract';
                    } elseif (stripos($job_type_display, 'remote') !== false) {
                        $badge_class = 'badge-remote';
                    }
            ?>
            <div class="job-card">
                <div class="job-header">
                    <div>
                        <h3 class="job-title"><?php echo htmlspecialchars($job['Job_Title']); ?></h3>
                        <div class="job-company">
                            <i class="fas fa-building"></i>
                            <?php echo htmlspecialchars($job['Company_Name'] ?? 'Company Name'); ?>
                        </div>
                    </div>
                    <span class="job-badge <?php echo $badge_class; ?>">
                        <?php echo htmlspecialchars($job_type_display); ?>
                    </span>
                </div>
                
                <div class="job-details">
                    <div class="detail-item">
                        <i class="fas fa-tag"></i>
                        <?php echo htmlspecialchars($job['Job_Category'] ?? 'General'); ?>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php echo htmlspecialchars($job['Location'] ?? 'Addis Ababa'); ?>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-calendar"></i>
                        Posted: <?php echo date('M d, Y', strtotime($job['Posted_Date'] ?? $job['Posting_Date'])); ?>
                    </div>
                </div>
                
                <div class="job-description">
                    <?php 
                    $desc = $job['Job_Description'] ?? '';
                    echo htmlspecialchars(substr($desc, 0, 120)) . (strlen($desc) > 120 ? '...' : '');
                    ?>
                </div>
                
                <div class="job-footer">
                    <?php if (!empty($job['Salary_Min']) || !empty($job['Salary_Max'])): ?>
                    <div class="job-salary">
                        <i class="fas fa-money-bill-wave"></i>
                        <?php 
                        if (!empty($job['Salary_Min']) && !empty($job['Salary_Max'])) {
                            echo number_format($job['Salary_Min']) . ' - ' . number_format($job['Salary_Max']) . ' ETB';
                        } elseif (!empty($job['Salary_Min'])) {
                            echo 'From ' . number_format($job['Salary_Min']) . ' ETB';
                        } elseif (!empty($job['Salary_Max'])) {
                            echo 'Up to ' . number_format($job['Salary_Max']) . ' ETB';
                        }
                        ?>
                    </div>
                    <?php else: ?>
                    <div></div>
                    <?php endif; ?>
                    
                    <a href="apply_job.php?id=<?php echo $job['Job_ID']; ?>" class="btn-apply">
                        Apply Now <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php 
                endwhile;
            else:
            ?>
            <div class="empty-state" style="grid-column: 1 / -1;">
                <i class="fas fa-search"></i>
                <h3>No Jobs Found</h3>
                <p>Try adjusting your search filters or check back later for new opportunities.</p>
                <a href="jobs.php" class="btn btn-primary" style="display: inline-block; padding: 12px 30px;">
                    <i class="fas fa-undo"></i> Clear All Filters
                </a>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <ul class="pagination">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php 
                    $params = $_GET;
                    $params['page'] = $page - 1;
                    echo http_build_query($params);
                ?>">
                    <i class="fas fa-chevron-left"></i>
                </a>
            </li>
            <?php endif; ?>
            
            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                <?php if ($i == 1 || $i == $total_pages || ($i >= $page - 2 && $i <= $page + 2)): ?>
                <li class="page-item">
                    <a class="page-link <?php echo $i == $page ? 'active' : ''; ?>" href="?<?php 
                        $params = $_GET;
                        $params['page'] = $i;
                        echo http_build_query($params);
                    ?>"><?php echo $i; ?></a>
                </li>
                <?php elseif ($i == $page - 3 || $i == $page + 3): ?>
                <li class="page-dots">...</li>
                <?php endif; ?>
            <?php endfor; ?>
            
            <?php if ($page < $total_pages): ?>
            <li class="page-item">
                <a class="page-link" href="?<?php 
                    $params = $_GET;
                    $params['page'] = $page + 1;
                    echo http_build_query($params);
                ?>">
                    <i class="fas fa-chevron-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- JavaScript for Enhanced Functionality -->
<script>
// Live search suggestions (optional)
const searchInput = document.querySelector('input[name="category"]');
if (searchInput) {
    searchInput.addEventListener('input', debounce(function() {
        // You could implement AJAX search here
        console.log('Searching for:', this.value);
    }, 500));
}

// Debounce function to limit API calls
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func.apply(this, args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Smooth scroll to top when changing pages
document.querySelectorAll('.page-link').forEach(link => {
    link.addEventListener('click', function(e) {
        if (!this.classList.contains('active')) {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }
    });
});

// Save search preferences to localStorage (optional)
function saveSearchPreferences() {
    const preferences = {
        category: '<?php echo $category; ?>',
        job_type: '<?php echo $job_type; ?>',
        location: '<?php echo $location; ?>',
        experience: '<?php echo $experience; ?>'
    };
    localStorage.setItem('jobSearchPreferences', JSON.stringify(preferences));
}

// Load search preferences (optional)
function loadSearchPreferences() {
    const saved = localStorage.getItem('jobSearchPreferences');
    if (saved && !window.location.search) {
        const prefs = JSON.parse(saved);
        // You could auto-fill the form here
    }
}

// Call on page load
document.addEventListener('DOMContentLoaded', function() {
    loadSearchPreferences();
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Press '/' to focus search
    if (e.key === '/' && !e.ctrlKey && !e.altKey && !e.metaKey) {
        e.preventDefault();
        document.querySelector('input[name="category"]')?.focus();
    }
    
    // Press 'r' to reset filters
    if (e.key === 'r' && e.ctrlKey) {
        e.preventDefault();
        window.location.href = 'jobs.php';
    }
});

// Tooltip for filter tags
document.querySelectorAll('.filter-tag i').forEach(icon => {
    icon.title = 'Remove filter';
});
</script>

<?php include '../includes/footer.php'; ?>