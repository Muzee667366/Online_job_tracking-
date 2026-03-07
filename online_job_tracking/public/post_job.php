<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security check: Only employers can post jobs
if ($_SESSION['role_id'] != 2) {
    $_SESSION['error_message'] = "Access denied. Only employers can post jobs.";
    header("Location: ../public/login.php");
    exit();
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
    --dark: #2c3e50;
    --light: #f8fafc;
    --gray: #7f8c8d;
    --gradient: linear-gradient(135deg, #2c3e50, #3498db);
    --gradient-success: linear-gradient(135deg, #27ae60, #2ecc71);
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
.post-job-wrapper {
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
.post-job-container {
    max-width: 800px;
    width: 100%;
    margin: 0 auto;
    position: relative;
    z-index: 10;
    animation: fadeIn 0.8s ease-out;
}

/* Job Post Card */
.job-post-card {
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

.header-icon {
    font-size: 4rem;
    margin-bottom: 15px;
    animation: bounce 2s infinite;
    filter: drop-shadow(0 10px 20px rgba(0,0,0,0.2));
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

/* Card Body */
.card-body {
    padding: 40px;
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
.form-group:nth-child(5) { animation-delay: 0.5s; }
.form-group:nth-child(6) { animation-delay: 0.6s; }

/* Labels */
.form-label {
    display: block;
    margin-bottom: 10px;
    color: var(--dark);
    font-weight: 600;
    font-size: 1rem;
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
    font-size: 1.2rem;
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

/* Textarea */
textarea.form-control {
    resize: vertical;
    min-height: 150px;
    line-height: 1.6;
}

/* Select Field */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%233498db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 20px;
    padding-right: 45px;
}

/* Input Groups (for salary range) */
.input-group {
    display: flex;
    gap: 15px;
    align-items: center;
}

.input-group .form-control {
    flex: 1;
}

.input-group-text {
    color: var(--gray);
    font-weight: 600;
    padding: 0 10px;
}

/* Character Counter */
.char-counter {
    text-align: right;
    font-size: 0.85rem;
    margin-top: 5px;
    color: var(--gray);
}

.char-counter.warning {
    color: var(--warning);
}

.char-counter.danger {
    color: var(--accent);
}

/* Form Row (for two columns) */
.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 25px;
}

@media (max-width: 768px) {
    .form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }
}

/* Toggle Switch (for job status) */
.toggle-switch {
    position: relative;
    display: inline-block;
    width: 60px;
    height: 34px;
}

.toggle-switch input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-slider {
    position: absolute;
    cursor: pointer;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background-color: #ccc;
    transition: .4s;
    border-radius: 34px;
}

.toggle-slider:before {
    position: absolute;
    content: "";
    height: 26px;
    width: 26px;
    left: 4px;
    bottom: 4px;
    background-color: white;
    transition: .4s;
    border-radius: 50%;
}

input:checked + .toggle-slider {
    background-color: var(--success);
}

input:checked + .toggle-slider:before {
    transform: translateX(26px);
}

.toggle-label {
    margin-left: 70px;
    display: block;
    font-weight: 600;
    color: var(--dark);
}

/* File Upload */
.file-upload {
    position: relative;
    margin-top: 5px;
}

.file-upload-label {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 20px;
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

/* Submit Button */
.btn-submit {
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
    margin: 30px 0 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.btn-submit::before {
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

.btn-submit:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 30px -10px rgba(39, 174, 96, 0.5);
}

.btn-submit:hover::before {
    width: 300px;
    height: 300px;
}

.btn-submit:active {
    transform: translateY(0);
}

.btn-submit i {
    transition: transform 0.3s ease;
    font-size: 1.2rem;
}

.btn-submit:hover i {
    transform: translateX(5px);
}

.btn-submit:disabled {
    opacity: 0.7;
    cursor: not-allowed;
}

/* Loading State */
.btn-submit.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-submit.loading i {
    animation: spin 1s linear infinite;
}

/* Preview Button */
.btn-preview {
    background: transparent;
    border: 2px solid var(--secondary);
    color: var(--secondary);
    padding: 12px 25px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    margin-top: 10px;
}

.btn-preview:hover {
    background: var(--secondary);
    color: white;
    transform: translateY(-2px);
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

/* Category Badge Preview */
.category-preview {
    margin-top: 10px;
    padding: 10px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.category-badge {
    background: var(--secondary);
    color: white;
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* Job Type Tags */
.job-type-tags {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
    margin-top: 10px;
}

.job-type-tag {
    padding: 8px 15px;
    border: 2px solid #e2e8f0;
    border-radius: 30px;
    font-size: 0.9rem;
    cursor: pointer;
    transition: all 0.3s ease;
    color: var(--gray);
}

.job-type-tag:hover {
    border-color: var(--secondary);
    color: var(--secondary);
}

.job-type-tag.active {
    background: var(--secondary);
    color: white;
    border-color: var(--secondary);
}
</style>

<div class="post-job-wrapper">
    <!-- Animated Background -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    
    <div class="post-job-container">
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
        <div class="job-post-card">
            <div class="card-header">
                <div class="header-icon">
                    <i class="fas fa-briefcase"></i>
                </div>
                <h2>Post a New Vacancy</h2>
                <p>Reach thousands of qualified candidates</p>
            </div>
            
            <div class="card-body">
                <form action="../modules/employer/post_job_action.php" method="POST" id="postJobForm" enctype="multipart/form-data">
                    <!-- Job Title -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-heading"></i>
                            Job Title
                            <span class="required">*</span>
                            <span class="tooltip" data-tooltip="Enter a clear, descriptive job title">ⓘ</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               class="form-control" 
                               placeholder="e.g., Senior PHP Developer"
                               required 
                               maxlength="100"
                               id="jobTitle">
                        <div class="char-counter" id="titleCounter">0/100 characters</div>
                    </div>
                    
                    <!-- Job Category -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-tag"></i>
                            Category
                            <span class="required">*</span>
                        </label>
                        <select name="category" class="form-control" required id="jobCategory">
                            <option value="" disabled selected>-- Select a Category --</option>
                            <option value="IT/Software">💻 IT / Software</option>
                            <option value="Healthcare">🏥 Healthcare</option>
                            <option value="Education">📚 Education</option>
                            <option value="Finance">💰 Finance</option>
                            <option value="Marketing">📊 Marketing</option>
                            <option value="Sales">📈 Sales</option>
                            <option value="Engineering">⚙️ Engineering</option>
                            <option value="Hospitality">🍽️ Hospitality</option>
                            <option value="Construction">🏗️ Construction</option>
                            <option value="Transportation">🚚 Transportation</option>
                            <option value="Retail">🛍️ Retail</option>
                            <option value="Other">📌 Other</option>
                        </select>
                    </div>
                    
                    <!-- Job Type (Enhanced) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-clock"></i>
                            Job Type
                            <span class="required">*</span>
                        </label>
                        <div class="job-type-tags">
                            <label class="job-type-tag">
                                <input type="radio" name="job_type" value="Full Time" style="display: none;" checked>
                                <span>⏰ Full Time</span>
                            </label>
                            <label class="job-type-tag">
                                <input type="radio" name="job_type" value="Part Time" style="display: none;">
                                <span>⌛ Part Time</span>
                            </label>
                            <label class="job-type-tag">
                                <input type="radio" name="job_type" value="Contract" style="display: none;">
                                <span>📝 Contract</span>
                            </label>
                            <label class="job-type-tag">
                                <input type="radio" name="job_type" value="Internship" style="display: none;">
                                <span>🎓 Internship</span>
                            </label>
                            <label class="job-type-tag">
                                <input type="radio" name="job_type" value="Remote" style="display: none;">
                                <span>🏠 Remote</span>
                            </label>
                        </div>
                    </div>
                    
                    <!-- Location -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-map-marker-alt"></i>
                            Location
                        </label>
                        <input type="text" 
                               name="location" 
                               class="form-control" 
                               placeholder="e.g., Addis Ababa, Ethiopia"
                               id="location">
                    </div>
                    
                    <!-- Salary Range (Optional) -->
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-money-bill-wave"></i>
                                Salary (Min)
                            </label>
                            <input type="number" 
                                   name="salary_min" 
                                   class="form-control" 
                                   placeholder="e.g., 10000"
                                   min="0"
                                   step="1000"
                                   id="salaryMin">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                <i class="fas fa-money-bill-wave"></i>
                                Salary (Max)
                            </label>
                            <input type="number" 
                                   name="salary_max" 
                                   class="form-control" 
                                   placeholder="e.g., 20000"
                                   min="0"
                                   step="1000"
                                   id="salaryMax">
                        </div>
                    </div>
                    
                    <!-- Experience Level -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-chart-line"></i>
                            Experience Level
                        </label>
                        <select name="experience" class="form-control">
                            <option value="">-- Select Experience Level --</option>
                            <option value="Entry Level">Entry Level</option>
                            <option value="Mid Level">Mid Level</option>
                            <option value="Senior Level">Senior Level</option>
                            <option value="Manager">Manager</option>
                            <option value="Executive">Executive</option>
                        </select>
                    </div>
                    
                    <!-- Application Deadline -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-calendar-alt"></i>
                            Application Deadline
                        </label>
                        <input type="date" 
                               name="deadline" 
                               class="form-control"
                               min="<?php echo date('Y-m-d'); ?>"
                               id="deadline">
                    </div>
                    
                    <!-- Job Description -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-align-left"></i>
                            Job Description
                            <span class="required">*</span>
                        </label>
                        <textarea name="description" 
                                  class="form-control" 
                                  rows="8" 
                                  placeholder="Describe the role, responsibilities, and qualifications..."
                                  required
                                  id="jobDescription"></textarea>
                        <div class="char-counter" id="descCounter">0 characters</div>
                    </div>
                    
                    <!-- Requirements (Optional) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-list-check"></i>
                            Requirements
                        </label>
                        <textarea name="requirements" 
                                  class="form-control" 
                                  rows="5" 
                                  placeholder="List the requirements (one per line)&#10;e.g.:&#10;- Bachelor's degree in Computer Science&#10;- 3+ years PHP experience&#10;- Strong communication skills"
                                  id="requirements"></textarea>
                    </div>
                    
                    <!-- Benefits (Optional) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-gift"></i>
                            Benefits
                        </label>
                        <textarea name="benefits" 
                                  class="form-control" 
                                  rows="4" 
                                  placeholder="List the benefits offered (one per line)&#10;e.g.:&#10;- Health insurance&#10;- Paid time off&#10;- Professional development"
                                  id="benefits"></textarea>
                    </div>
                    
                    <!-- Company Logo Upload (Optional) -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-image"></i>
                            Company Logo
                        </label>
                        <div class="file-upload">
                            <div class="file-upload-label" id="fileUploadLabel">
                                <i class="fas fa-cloud-upload-alt"></i>
                                <div>
                                    <span><strong>Click to upload</strong> or drag and drop</span>
                                    <div style="font-size: 0.9rem; margin-top: 5px;">PNG, JPG, GIF up to 2MB</div>
                                </div>
                            </div>
                            <input type="file" name="company_logo" id="companyLogo" accept="image/*">
                        </div>
                        <div class="file-info" id="fileInfo" style="margin-top: 10px; color: var(--gray);">
                            <i class="fas fa-info-circle"></i>
                            No file selected
                        </div>
                    </div>
                    
                    <!-- Job Status Toggle -->
                    <div class="form-group">
                        <label class="form-label">
                            <i class="fas fa-toggle-on"></i>
                            Job Status
                        </label>
                        <div style="position: relative;">
                            <label class="toggle-switch">
                                <input type="checkbox" name="status" id="jobStatus" value="Active" checked>
                                <span class="toggle-slider"></span>
                            </label>
                            <span class="toggle-label" id="statusLabel">Active (Visible to job seekers)</span>
                        </div>
                    </div>
                    
                    <!-- Terms Checkbox -->
                    <div style="display: flex; align-items: center; gap: 10px; margin: 20px 0; color: var(--gray);">
                        <input type="checkbox" id="terms" name="terms" required style="width: 18px; height: 18px;">
                        <label for="terms">I confirm that all information provided is accurate and complete</label>
                    </div>
                    
                    <!-- Submit Button -->
                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fas fa-paper-plane"></i>
                        Publish Job Now
                    </button>
                    
                    <!-- Preview Button (Optional) -->
                    <button type="button" class="btn-preview" onclick="previewJob()">
                        <i class="fas fa-eye"></i>
                        Preview Job Listing
                    </button>
                </form>
            </div>
            
            <div class="card-footer">
                <p class="footer-note">
                    <i class="fas fa-info-circle"></i>
                    Your job posting will be reviewed and published immediately. You can edit or close it anytime.
                </p>
            </div>
        </div>
    </div>
</div>

<!-- Preview Modal -->
<div class="modal-overlay" id="previewModal" style="display: none;">
    <div class="modal-container" style="max-width: 700px;">
        <div class="modal-header">
            <h3><i class="fas fa-eye"></i> Job Preview</h3>
            <button class="modal-close" onclick="closePreview()">&times;</button>
        </div>
        <div class="modal-body" id="previewContent">
            <!-- Preview content will be loaded here -->
        </div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="closePreview()">Close</button>
            <button class="btn btn-success" onclick="document.getElementById('postJobForm').submit();">
                <i class="fas fa-paper-plane"></i> Publish Job
            </button>
        </div>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- JavaScript for Enhanced Functionality -->
<script>
// Character counters
const titleInput = document.getElementById('jobTitle');
const titleCounter = document.getElementById('titleCounter');
const descInput = document.getElementById('jobDescription');
const descCounter = document.getElementById('descCounter');

if (titleInput) {
    titleInput.addEventListener('input', function() {
        const length = this.value.length;
        titleCounter.textContent = `${length}/100 characters`;
        titleCounter.className = length > 90 ? 'char-counter warning' : 'char-counter';
    });
}

if (descInput) {
    descInput.addEventListener('input', function() {
        const length = this.value.length;
        descCounter.textContent = `${length} characters`;
    });
}

// Job type tag selection
document.querySelectorAll('.job-type-tag').forEach(tag => {
    tag.addEventListener('click', function() {
        document.querySelectorAll('.job-type-tag').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        const radio = this.querySelector('input[type="radio"]');
        if (radio) radio.checked = true;
    });
});

// Salary validation
const salaryMin = document.getElementById('salaryMin');
const salaryMax = document.getElementById('salaryMax');

if (salaryMin && salaryMax) {
    salaryMin.addEventListener('change', function() {
        if (salaryMax.value && parseInt(this.value) > parseInt(salaryMax.value)) {
            alert('Minimum salary cannot be greater than maximum salary');
            this.value = '';
        }
    });
    
    salaryMax.addEventListener('change', function() {
        if (salaryMin.value && parseInt(this.value) < parseInt(salaryMin.value)) {
            alert('Maximum salary cannot be less than minimum salary');
            this.value = '';
        }
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

// Job status toggle
const jobStatus = document.getElementById('jobStatus');
const statusLabel = document.getElementById('statusLabel');

if (jobStatus && statusLabel) {
    jobStatus.addEventListener('change', function() {
        if (this.checked) {
            statusLabel.textContent = 'Active (Visible to job seekers)';
            statusLabel.style.color = 'var(--success)';
        } else {
            statusLabel.textContent = 'Draft (Hidden from job seekers)';
            statusLabel.style.color = 'var(--gray)';
        }
    });
}

// Form submission with loading state
document.getElementById('postJobForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const terms = document.getElementById('terms');
    const title = document.getElementById('jobTitle').value.trim();
    const category = document.getElementById('jobCategory').value;
    const description = document.getElementById('jobDescription').value.trim();
    
    if (!terms.checked) {
        e.preventDefault();
        alert('Please confirm that the information is accurate');
        return;
    }
    
    if (!title || !category || !description) {
        e.preventDefault();
        alert('Please fill in all required fields');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Publishing Job...';
    submitBtn.classList.add('loading');
    submitBtn.disabled = true;
});

// Preview function
function previewJob() {
    const title = document.getElementById('jobTitle').value || '[Job Title]';
    const category = document.getElementById('jobCategory').options[document.getElementById('jobCategory').selectedIndex]?.text || '[Category]';
    const description = document.getElementById('jobDescription').value || '[Description]';
    const location = document.getElementById('location').value || 'Not specified';
    const salaryMin = document.getElementById('salaryMin').value;
    const salaryMax = document.getElementById('salaryMax').value;
    const jobType = document.querySelector('input[name="job_type"]:checked')?.value || 'Full Time';
    
    let salaryText = 'Not specified';
    if (salaryMin && salaryMax) {
        salaryText = `${salaryMin} - ${salaryMax} ETB`;
    } else if (salaryMin) {
        salaryText = `From ${salaryMin} ETB`;
    } else if (salaryMax) {
        salaryText = `Up to ${salaryMax} ETB`;
    }
    
    const previewHTML = `
        <div style="padding: 20px;">
            <h2 style="color: var(--dark); margin-bottom: 10px;">${title}</h2>
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                <span style="background: var(--secondary); color: white; padding: 5px 15px; border-radius: 20px;">${jobType}</span>
                <span style="background: #e8f0fe; color: var(--secondary); padding: 5px 15px; border-radius: 20px;">${category}</span>
            </div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; margin-bottom: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                <div><i class="fas fa-map-marker-alt" style="color: var(--secondary);"></i> <strong>Location:</strong> ${location}</div>
                <div><i class="fas fa-money-bill-wave" style="color: var(--success);"></i> <strong>Salary:</strong> ${salaryText}</div>
            </div>
            <div style="margin-bottom: 20px;">
                <h3 style="color: var(--dark); margin-bottom: 10px;">Description</h3>
                <p style="color: var(--gray); line-height: 1.8;">${description.replace(/\n/g, '<br>')}</p>
            </div>
            <div style="color: var(--gray); font-size: 0.9rem;">
                <i class="fas fa-clock"></i> Posted: Just now
            </div>
        </div>
    `;
    
    document.getElementById('previewContent').innerHTML = previewHTML;
    document.getElementById('previewModal').style.display = 'flex';
}

function closePreview() {
    document.getElementById('previewModal').style.display = 'none';
}

// Close modal when clicking outside
window.addEventListener('click', function(event) {
    const modal = document.getElementById('previewModal');
    if (event.target === modal) {
        modal.style.display = 'none';
    }
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closePreview();
    }
    
    if (e.ctrlKey && e.key === 'p' && !e.target.matches('input, textarea')) {
        e.preventDefault();
        document.getElementById('submitBtn').click();
    }
});

// Auto-fill demo data (for testing)
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
        document.getElementById('jobTitle').value = 'Senior PHP Developer';
        document.getElementById('jobCategory').value = 'IT/Software';
        document.querySelector('input[name="job_type"][value="Full Time"]').checked = true;
        document.querySelector('.job-type-tag').classList.add('active');
        document.getElementById('location').value = 'Addis Ababa, Ethiopia';
        document.getElementById('salaryMin').value = '15000';
        document.getElementById('salaryMax').value = '25000';
        document.getElementById('jobDescription').value = 'We are looking for an experienced PHP Developer to join our growing team. The ideal candidate will have strong experience with PHP, MySQL, and modern web technologies.\n\nResponsibilities:\n- Develop and maintain web applications\n- Collaborate with cross-functional teams\n- Write clean, maintainable code\n- Troubleshoot and debug applications\n\nRequirements:\n- 3+ years PHP experience\n- Strong MySQL knowledge\n- Experience with Laravel framework\n- Good communication skills';
        document.getElementById('requirements').value = '- Bachelor\'s degree in Computer Science or related field\n- 3+ years PHP development experience\n- Strong MySQL and database design skills\n- Experience with Laravel framework\n- Knowledge of JavaScript, HTML, CSS\n- Excellent problem-solving abilities';
        document.getElementById('benefits').value = '- Competitive salary\n- Health insurance\n- Paid time off\n- Professional development opportunities\n- Flexible working hours\n- Friendly work environment';
        document.getElementById('jobStatus').checked = true;
        
        // Trigger input events
        titleInput.dispatchEvent(new Event('input'));
        descInput.dispatchEvent(new Event('input'));
        
        alert('Demo data filled! Review and publish the job.');
    };
    
    document.body.appendChild(demoBtn);
}
</script>

<!-- Modal Styles -->
<style>
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
    max-width: 700px;
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
</style>

<?php include '../includes/footer.php'; ?>