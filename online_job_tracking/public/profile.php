<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';
include '../includes/header.php'; 

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['role_id'];

// Fetch current user data
$stmt = $conn->prepare("SELECT Full_Name, Email FROM tbl_users WHERE User_ID = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Fetch additional profile data based on role
$profileData = [];
if ($user_role == 2) { // Employer
    $stmt = $conn->prepare("SELECT * FROM tbl_employer_profiles WHERE Employer_ID = ?");
    $stmt->execute([$user_id]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
} elseif ($user_role == 3) { // Job Seeker
    $stmt = $conn->prepare("SELECT * FROM tbl_job_seeker_profiles WHERE Seeker_ID = ?");
    $stmt->execute([$user_id]);
    $profileData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Check if resume exists
$resumePath = $profileData['Resume_Path'] ?? null;
?>

<style>
:root {
    --primary: #2c3e50;
    --primary-light: #34495e;
    --secondary: #3498db;
    --accent: #e74c3c;
    --success: #27ae60;
    --warning: #f39c12;
    --dark: #2c3e50;
    --light: #f8fafc;
    --gray: #7f8c8d;
    --gradient: linear-gradient(135deg, #2c3e50, #3498db);
    --gradient-success: linear-gradient(135deg, #27ae60, #2ecc71);
    --gradient-danger: linear-gradient(135deg, #e74c3c, #c0392b);
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
.profile-wrapper {
    min-height: calc(100vh - 200px);
    padding: 40px 20px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
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
.profile-container {
    max-width: 700px;
    width: 100%;
    margin: 0 auto;
    position: relative;
    z-index: 10;
    animation: fadeIn 0.8s ease-out;
}

/* Profile Card */
.profile-card {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 30px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Card Header */
.card-header {
    background: var(--gradient);
    padding: 40px 30px;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.card-header::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
    animation: rotate 30s linear infinite;
}

.profile-avatar {
    width: 100px;
    height: 100px;
    background: white;
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 3rem;
    color: var(--secondary);
    border: 4px solid rgba(255,255,255,0.3);
    box-shadow: var(--shadow-md);
    animation: pulse 2s infinite;
}

.card-header h2 {
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
    margin: 10px 0 5px;
    position: relative;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
}

.card-header p {
    color: rgba(255,255,255,0.9);
    font-size: 1.1rem;
    position: relative;
}

.role-badge-header {
    display: inline-block;
    background: rgba(255,255,255,0.2);
    color: white;
    padding: 8px 20px;
    border-radius: 30px;
    font-size: 0.95rem;
    margin-top: 10px;
    border: 1px solid rgba(255,255,255,0.3);
}

/* Card Body */
.card-body {
    padding: 40px;
}

/* Section Divider */
.section-divider {
    display: flex;
    align-items: center;
    margin: 30px 0 20px;
}

.section-divider .line {
    flex: 1;
    height: 2px;
    background: linear-gradient(to right, transparent, var(--secondary), transparent);
}

.section-divider .text {
    padding: 0 15px;
    color: var(--secondary);
    font-weight: 600;
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 12px;
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
    border-left: 4px solid var(--secondary);
}

/* Form Groups */
.form-group {
    margin-bottom: 25px;
    position: relative;
    animation: fadeInUp 0.6s ease-out;
    animation-fill-mode: both;
}

.form-group:nth-child(1) { animation-delay: 0.1s; }
.form-group:nth-child(2) { animation-delay: 0.2s; }
.form-group:nth-child(3) { animation-delay: 0.3s; }
.form-group:nth-child(4) { animation-delay: 0.4s; }

/* Labels */
.form-label {
    display: block;
    margin-bottom: 10px;
    color: var(--dark);
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-label i {
    color: var(--secondary);
    margin-right: 8px;
    width: 20px;
}

.form-label .required {
    color: var(--accent);
    margin-left: 5px;
}

/* Input Fields */
.form-control {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

.form-control:hover {
    border-color: var(--secondary);
    box-shadow: 0 5px 15px rgba(52, 152, 219, 0.1);
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary);
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
    transform: translateY(-2px);
}

.form-control:disabled {
    background: #f8f9fa;
    cursor: not-allowed;
}

/* Input with Icon */
.input-icon-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    left: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    transition: all 0.3s ease;
}

.input-icon-wrapper .form-control {
    padding-left: 45px;
}

/* Password Strength Meter */
.password-strength {
    margin-top: 10px;
}

.strength-bar {
    height: 5px;
    background: #e2e8f0;
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 5px;
}

.strength-bar-fill {
    height: 100%;
    width: 0%;
    transition: all 0.3s ease;
    border-radius: 10px;
}

.strength-text {
    font-size: 0.85rem;
    color: var(--gray);
}

/* Password Requirements */
.password-requirements {
    background: #f8fafc;
    border-radius: 12px;
    padding: 15px;
    margin-top: 10px;
    font-size: 0.9rem;
}

.requirement {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 8px;
    color: var(--gray);
    transition: all 0.3s ease;
}

.requirement i {
    font-size: 0.9rem;
    width: 18px;
}

.requirement.met {
    color: var(--success);
}

.requirement.met i {
    color: var(--success);
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
    padding: 25px;
    background: #f8f9fa;
    border: 2px dashed var(--secondary);
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.file-upload-label:hover {
    background: #e8f0fe;
    border-color: var(--primary);
}

.file-upload-label i {
    font-size: 2.5rem;
    color: var(--secondary);
}

.file-upload-label .upload-text {
    text-align: left;
}

.file-upload-label .upload-text strong {
    color: var(--secondary);
    display: block;
    margin-bottom: 5px;
}

.file-upload-label .upload-text small {
    color: var(--gray);
    font-size: 0.85rem;
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

/* Current Resume Display */
.current-resume {
    background: #f8f9fa;
    border-radius: 10px;
    padding: 15px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 10px;
}

.resume-info {
    display: flex;
    align-items: center;
    gap: 10px;
}

.resume-info i {
    font-size: 2rem;
    color: var(--accent);
}

.resume-details h4 {
    color: var(--dark);
    margin-bottom: 3px;
}

.resume-details p {
    color: var(--gray);
    font-size: 0.85rem;
}

.btn-download {
    background: var(--secondary);
    color: white;
    padding: 8px 15px;
    border-radius: 8px;
    text-decoration: none;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
    transition: all 0.3s ease;
}

.btn-download:hover {
    background: var(--primary);
    transform: translateY(-2px);
}

/* Submit Button */
.btn-save {
    width: 100%;
    padding: 18px;
    background: var(--gradient-success);
    color: white;
    border: none;
    border-radius: 15px;
    font-size: 1.2rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin: 20px 0;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-save::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.2);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn-save:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 30px -10px rgba(39, 174, 96, 0.5);
}

.btn-save:hover::before {
    width: 300px;
    height: 300px;
}

.btn-save:active {
    transform: translateY(0);
}

.btn-save i {
    transition: transform 0.3s ease;
    font-size: 1.2rem;
}

.btn-save:hover i {
    transform: translateX(5px);
}

.btn-save:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Secondary Button */
.btn-secondary {
    width: 100%;
    padding: 15px;
    background: var(--gradient);
    color: white;
    border: none;
    border-radius: 15px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.btn-secondary:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 30px -10px rgba(52, 152, 219, 0.5);
}

/* Loading State */
.btn-save.loading,
.btn-secondary.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-save.loading i,
.btn-secondary.loading i {
    animation: spin 1s linear infinite;
}

/* Tooltip */
.tooltip {
    position: relative;
    display: inline-block;
    margin-left: 5px;
    color: var(--gray);
    cursor: help;
}

.tooltip:hover::after {
    content: attr(data-tooltip);
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    padding: 8px 12px;
    background: var(--dark);
    color: white;
    font-size: 0.85rem;
    border-radius: 8px;
    white-space: nowrap;
    z-index: 1000;
    margin-bottom: 5px;
    box-shadow: var(--shadow-sm);
}

/* Card Footer */
.card-footer {
    padding: 20px 40px 30px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
    text-align: center;
}

.footer-note {
    color: var(--gray);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
}

.footer-note i {
    color: var(--secondary);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
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

@keyframes float {
    0%, 100% { transform: translateY(0) rotate(0deg); }
    50% { transform: translateY(-30px) rotate(5deg); }
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .card-header {
        padding: 30px 20px;
    }
    
    .card-header h2 {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 30px 20px;
    }
    
    .card-footer {
        padding: 20px;
    }
    
    .current-resume {
        flex-direction: column;
        text-align: center;
    }
    
    .resume-info {
        flex-direction: column;
    }
}

/* Success Check Animation */
.checkmark {
    width: 56px;
    height: 56px;
    border-radius: 50%;
    display: block;
    stroke-width: 2;
    stroke: #fff;
    stroke-miterlimit: 10;
    margin: 10% auto;
    box-shadow: inset 0px 0px 0px var(--success);
    animation: fill 0.4s ease-in-out 0.4s forwards, scale 0.3s ease-in-out 0.9s both;
}

.checkmark__circle {
    stroke-dasharray: 166;
    stroke-dashoffset: 166;
    stroke-width: 2;
    stroke-miterlimit: 10;
    stroke: var(--success);
    fill: none;
    animation: stroke 0.6s cubic-bezier(0.65, 0, 0.45, 1) forwards;
}

.checkmark__check {
    transform-origin: 50% 50%;
    stroke-dasharray: 48;
    stroke-dashoffset: 48;
    animation: stroke 0.3s cubic-bezier(0.65, 0, 0.45, 1) 0.8s forwards;
}

@keyframes stroke {
    100% { stroke-dashoffset: 0; }
}

@keyframes scale {
    0%, 100% { transform: none; }
    50% { transform: scale3d(1.1, 1.1, 1); }
}

@keyframes fill {
    100% { box-shadow: inset 0px 0px 0px 30px var(--success); }
}
</style>

<div class="profile-wrapper">
    <!-- Animated Background -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    
    <div class="profile-container">
        <!-- Alert Messages -->
        <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-error">
            <i class="fas fa-exclamation-circle"></i>
            <span><?php echo htmlspecialchars($_GET['error']); ?></span>
        </div>
        <?php endif; ?>
        
        <?php if (isset($_GET['success'])): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($_GET['success']); ?></span>
        </div>
        <?php endif; ?>
        
        <!-- Main Card -->
        <div class="profile-card">
            <div class="card-header">
                <div class="profile-avatar">
                    <i class="fas fa-user-circle"></i>
                </div>
                <h2>Update Profile</h2>
                <p>Manage your account information</p>
                <div class="role-badge-header">
                    <i class="fas <?php echo $user_role == 2 ? 'fa-building' : ($user_role == 3 ? 'fa-user-graduate' : 'fa-crown'); ?>"></i>
                    <?php 
                    if ($user_role == 2) echo 'Employer Account';
                    elseif ($user_role == 3) echo 'Job Seeker Account';
                    else echo 'Manager Account';
                    ?>
                </div>
            </div>
            
            <div class="card-body">
                <!-- Profile Update Form -->
                <form action="../modules/auth/update_profile.php" method="POST" id="profileForm">
                    <!-- Full Name Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-user"></i>
                            Full Name
                            <span class="required">*</span>
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" 
                                   name="full_name" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($user['Full_Name']); ?>" 
                                   required
                                   minlength="3"
                                   maxlength="100"
                                   pattern="[A-Za-z\s]+"
                                   title="Only letters and spaces allowed">
                        </div>
                    </div>
                    
                    <!-- Email Field -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-envelope"></i>
                            Email Address
                            <span class="required">*</span>
                            <span class="tooltip" data-tooltip="We'll never share your email">ⓘ</span>
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-envelope input-icon"></i>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($user['Email']); ?>" 
                                   required
                                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                                   title="Enter a valid email address">
                        </div>
                    </div>
                    
                    <!-- Password Field (Optional) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-lock"></i>
                            New Password
                            <span class="tooltip" data-tooltip="Leave blank to keep current password">ⓘ</span>
                        </label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" 
                                   name="new_password" 
                                   id="newPassword"
                                   class="form-control" 
                                   placeholder="Enter new password (optional)"
                                   minlength="8">
                            <i class="fas fa-eye" id="togglePassword" style="position: absolute; right: 15px; top: 50%; transform: translateY(-50%); cursor: pointer; color: var(--gray);"></i>
                        </div>
                        
                        <!-- Password Strength Meter (only shows when typing) -->
                        <div class="password-strength" id="passwordStrength" style="display: none;">
                            <div class="strength-bar">
                                <div class="strength-bar-fill" id="strengthBar"></div>
                            </div>
                            <span class="strength-text" id="strengthText">Enter password</span>
                        </div>
                        
                        <!-- Password Requirements -->
                        <div class="password-requirements" id="passwordRequirements" style="display: none;">
                            <div class="requirement" id="req-length">
                                <i class="fas fa-circle"></i> At least 8 characters
                            </div>
                            <div class="requirement" id="req-uppercase">
                                <i class="fas fa-circle"></i> One uppercase letter
                            </div>
                            <div class="requirement" id="req-lowercase">
                                <i class="fas fa-circle"></i> One lowercase letter
                            </div>
                            <div class="requirement" id="req-number">
                                <i class="fas fa-circle"></i> One number
                            </div>
                            <div class="requirement" id="req-special">
                                <i class="fas fa-circle"></i> One special character
                            </div>
                        </div>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-save" id="saveBtn">
                        <i class="fas fa-save"></i>
                        Save Changes
                    </button>
                </form>
                
                <!-- Section Divider -->
                <div class="section-divider">
                    <span class="line"></span>
                    <span class="text"><i class="fas fa-file-pdf"></i> Professional Documents</span>
                    <span class="line"></span>
                </div>
                
                <!-- Current Resume Display (if exists) -->
                <?php if ($resumePath): ?>
                <div class="current-resume">
                    <div class="resume-info">
                        <i class="fas fa-file-pdf"></i>
                        <div class="resume-details">
                            <h4>Current Resume</h4>
                            <p>Uploaded: Recently</p>
                        </div>
                    </div>
                    <a href="<?php echo htmlspecialchars($resumePath); ?>" class="btn-download" download>
                        <i class="fas fa-download"></i> Download
                    </a>
                </div>
                <?php endif; ?>
                
                <!-- Resume Upload Form -->
                <form action="../modules/jobseeker/upload_resume.php" method="POST" enctype="multipart/form-data" id="resumeForm">
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-file-pdf"></i>
                            Upload Resume
                            <?php if (!$resumePath): ?><span class="required">*</span><?php endif; ?>
                        </label>
                        
                        <div class="file-upload">
                            <div class="file-upload-label" id="fileUploadLabel">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div class="upload-text">
                                    <strong>Click to upload or drag and drop</strong>
                                    <small>PDF only (Max 5MB)</small>
                                </div>
                            </div>
                            <input type="file" name="resume" id="resumeFile" accept=".pdf" <?php echo !$resumePath ? 'required' : ''; ?>>
                        </div>
                        
                        <div class="file-info" id="fileInfo">
                            <i class="fas fa-info-circle"></i>
                            <?php echo $resumePath ? 'Current resume: ' . basename($resumePath) : 'No file selected'; ?>
                        </div>
                    </div>
                    
                    <button type="submit" class="btn-secondary" id="uploadBtn">
                        <i class="fas fa-upload"></i>
                        <?php echo $resumePath ? 'Update Resume' : 'Upload Resume'; ?>
                    </button>
                </form>
                
                <!-- Additional fields for Job Seekers (optional) -->
                <?php if ($user_role == 3 && empty($profileData)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #fff3cd; border-radius: 10px; color: #856404;">
                    <i class="fas fa-info-circle"></i>
                    Complete your profile to increase your chances of getting hired. 
                    <a href="jobseeker_profile.php" style="color: var(--secondary); font-weight: 600;">Click here</a> to add more details.
                </div>
                <?php endif; ?>
                
                <!-- Additional fields for Employers (optional) -->
                <?php if ($user_role == 2 && empty($profileData)): ?>
                <div style="margin-top: 20px; padding: 15px; background: #d1ecf1; border-radius: 10px; color: #0c5460;">
                    <i class="fas fa-info-circle"></i>
                    Complete your company profile to build trust with job seekers.
                    <a href="employer_profile.php" style="color: var(--secondary); font-weight: 600;">Click here</a> to add company details.
                </div>
                <?php endif; ?>
            </div>
            
            <div class="card-footer">
                <p class="footer-note">
                    <i class="fas fa-shield-alt"></i>
                    Your information is securely encrypted and never shared with third parties
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- JavaScript for Enhanced Functionality -->
<script>
// Password visibility toggle
const togglePassword = document.getElementById('togglePassword');
const passwordInput = document.getElementById('newPassword');

if (togglePassword && passwordInput) {
    togglePassword.addEventListener('click', function() {
        const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
        passwordInput.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });
    
    // Show/hide password strength meter
    passwordInput.addEventListener('focus', function() {
        document.getElementById('passwordStrength').style.display = 'block';
        document.getElementById('passwordRequirements').style.display = 'block';
    });
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        
        if (password.length > 0) {
            document.getElementById('passwordStrength').style.display = 'block';
            document.getElementById('passwordRequirements').style.display = 'block';
            
            // Password strength checker
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            const hasLength = password.length >= 8;
            const hasUpperCase = /[A-Z]/.test(password);
            const hasLowerCase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);
            
            // Update requirement icons
            document.getElementById('req-length').className = 'requirement ' + (hasLength ? 'met' : '');
            document.getElementById('req-uppercase').className = 'requirement ' + (hasUpperCase ? 'met' : '');
            document.getElementById('req-lowercase').className = 'requirement ' + (hasLowerCase ? 'met' : '');
            document.getElementById('req-number').className = 'requirement ' + (hasNumber ? 'met' : '');
            document.getElementById('req-special').className = 'requirement ' + (hasSpecial ? 'met' : '');
            
            // Calculate strength
            const requirements = [hasLength, hasUpperCase, hasLowerCase, hasNumber, hasSpecial];
            const metCount = requirements.filter(Boolean).length;
            
            // Update strength bar
            const strengthPercent = (metCount / 5) * 100;
            strengthBar.style.width = strengthPercent + '%';
            
            if (metCount <= 2) {
                strengthBar.style.background = '#ef4444';
                strengthText.textContent = 'Weak password';
            } else if (metCount <= 4) {
                strengthBar.style.background = '#f59e0b';
                strengthText.textContent = 'Medium password';
            } else {
                strengthBar.style.background = '#10b981';
                strengthText.textContent = 'Strong password';
            }
        } else {
            document.getElementById('passwordStrength').style.display = 'none';
            document.getElementById('passwordRequirements').style.display = 'none';
        }
    });
}

// File upload handling
const fileInput = document.getElementById('resumeFile');
const fileInfo = document.getElementById('fileInfo');
const fileLabel = document.getElementById('fileUploadLabel');

if (fileInput) {
    fileInput.addEventListener('change', function() {
        if (this.files && this.files[0]) {
            const file = this.files[0];
            const fileSize = (file.size / 1024 / 1024).toFixed(2);
            
            // Validate file type
            if (file.type !== 'application/pdf') {
                alert('Please upload a PDF file only');
                this.value = '';
                fileInfo.innerHTML = '<i class="fas fa-info-circle"></i> No file selected';
                fileLabel.style.borderColor = 'var(--secondary)';
                fileLabel.style.background = '#f8f9fa';
                return;
            }
            
            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB');
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

// Form submission with loading state
document.getElementById('profileForm')?.addEventListener('submit', function(e) {
    const saveBtn = document.getElementById('saveBtn');
    
    // Show loading state
    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Saving Changes...';
    saveBtn.classList.add('loading');
    saveBtn.disabled = true;
});

document.getElementById('resumeForm')?.addEventListener('submit', function(e) {
    const uploadBtn = document.getElementById('uploadBtn');
    
    // Show loading state
    uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Uploading...';
    uploadBtn.classList.add('loading');
    uploadBtn.disabled = true;
});

// Live email validation
document.querySelector('input[name="email"]')?.addEventListener('input', function() {
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
    const isValid = emailPattern.test(this.value);
    
    if (this.value.length > 0) {
        this.style.borderColor = isValid ? '#10b981' : '#ef4444';
    } else {
        this.style.borderColor = '#e2e8f0';
    }
});

// Live name validation
document.querySelector('input[name="full_name"]')?.addEventListener('input', function() {
    const namePattern = /^[A-Za-z\s]+$/;
    const isValid = namePattern.test(this.value) && this.value.length >= 3;
    
    if (this.value.length > 0) {
        this.style.borderColor = isValid ? '#10b981' : '#ef4444';
    } else {
        this.style.borderColor = '#e2e8f0';
    }
});

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

// Keyboard shortcut (Ctrl+S to save)
document.addEventListener('keydown', function(e) {
    if (e.ctrlKey && e.key === 's') {
        e.preventDefault();
        document.getElementById('saveBtn')?.click();
    }
});

// Demo data filler (for testing)
if (window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1') {
    const demoBtn = document.createElement('button');
    demoBtn.innerHTML = '<i class="fas fa-flask"></i> Fill Demo Data';
    demoBtn.style.cssText = `
        position: fixed;
        bottom: 100px;
        right: 30px;
        background: var(--gradient-success);
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 30px;
        cursor: pointer;
        font-weight: 600;
        box-shadow: var(--shadow-md);
        z-index: 1000;
        transition: all 0.3s ease;
    `;
    
    demoBtn.onmouseover = () => {
        demoBtn.style.transform = 'translateY(-3px)';
        demoBtn.style.boxShadow = 'var(--shadow-lg)';
    };
    
    demoBtn.onmouseout = () => {
        demoBtn.style.transform = 'translateY(0)';
        demoBtn.style.boxShadow = 'var(--shadow-md)';
    };
    
    demoBtn.onclick = () => {
        document.querySelector('input[name="full_name"]').value = 'John Doe';
        document.querySelector('input[name="email"]').value = 'john.doe@example.com';
        document.querySelector('input[name="new_password"]').value = 'Password123!';
        
        // Trigger validation
        document.querySelector('input[name="full_name"]').dispatchEvent(new Event('input'));
        document.querySelector('input[name="email"]').dispatchEvent(new Event('input'));
        
        alert('Demo data filled! Click Save Changes to update.');
    };
    
    document.body.appendChild(demoBtn);
}
</script>

<?php include '../includes/footer.php'; ?>