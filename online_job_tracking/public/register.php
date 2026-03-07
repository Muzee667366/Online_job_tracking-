<?php include '../includes/header.php'; ?>

<style>
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --primary-light: #60a5fa;
    --secondary: #7c3aed;
    --success: #10b981;
    --danger: #ef4444;
    --warning: #f59e0b;
    --dark: #0f172a;
    --light: #f8fafc;
    --gray: #64748b;
    --gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Main Container */
.register-container {
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background Elements */
.bg-bubble {
    position: absolute;
    background: rgba(255, 255, 255, 0.05);
    border-radius: 50%;
    pointer-events: none;
}

.bg-bubble-1 {
    width: 300px;
    height: 300px;
    top: -150px;
    right: -150px;
    animation: float 20s infinite;
}

.bg-bubble-2 {
    width: 200px;
    height: 200px;
    bottom: -100px;
    left: -100px;
    animation: float 15s infinite reverse;
}

.bg-bubble-3 {
    width: 150px;
    height: 150px;
    top: 50%;
    left: 10%;
    animation: float 18s infinite;
}

/* Card Styles */
.register-card {
    max-width: 550px;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 30px;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    overflow: hidden;
    position: relative;
    z-index: 10;
    animation: slideUp 0.8s ease-out;
}

/* Card Header */
.card-header {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
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
    margin-bottom: 8px;
    color: var(--dark);
    font-weight: 600;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-label i {
    color: var(--primary);
    margin-right: 8px;
    width: 20px;
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
}

.form-control:hover {
    border-color: var(--primary-light);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

/* Select Field */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='24' height='24' viewBox='0 0 24 24' fill='none' stroke='%232563eb' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 15px center;
    background-size: 20px;
}

/* Role Selection Cards */
.role-selection {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin: 10px 0;
}

.role-option {
    position: relative;
}

.role-option input[type="radio"] {
    display: none;
}

.role-option label {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 20px;
    background: #f8fafc;
    border: 2px solid #e2e8f0;
    border-radius: 15px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    height: 100%;
}

.role-option input[type="radio"]:checked + label {
    background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(124, 58, 237, 0.1));
    border-color: var(--primary);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.1);
    transform: translateY(-3px);
}

.role-option label i {
    font-size: 2.5rem;
    margin-bottom: 10px;
    color: var(--primary);
}

.role-option label span {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 5px;
}

.role-option label small {
    color: var(--gray);
    font-size: 0.85rem;
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

/* Submit Button */
.btn-register {
    width: 100%;
    padding: 18px;
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    color: white;
    border: none;
    border-radius: 15px;
    font-size: 1.2rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    margin: 20px 0 15px;
}

.btn-register::before {
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

.btn-register:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 30px -10px rgba(37, 99, 235, 0.5);
}

.btn-register:hover::before {
    width: 300px;
    height: 300px;
}

.btn-register:active {
    transform: translateY(0);
}

.btn-register i {
    margin-right: 10px;
    transition: transform 0.3s ease;
}

.btn-register:hover i {
    transform: translateX(5px);
}

/* Footer Links */
.card-footer {
    text-align: center;
    padding: 20px 40px 40px;
    background: #f8fafc;
    border-top: 1px solid #e2e8f0;
}

.login-link {
    color: var(--gray);
    font-size: 1rem;
}

.login-link a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
    margin-left: 5px;
    position: relative;
    transition: all 0.3s ease;
}

.login-link a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    transition: width 0.3s ease;
}

.login-link a:hover {
    color: var(--primary-dark);
}

.login-link a:hover::after {
    width: 100%;
}

/* Terms and Conditions */
.terms {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 15px 0;
    font-size: 0.9rem;
    color: var(--gray);
}

.terms input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--primary);
}

.terms a {
    color: var(--primary);
    text-decoration: none;
    font-weight: 600;
}

.terms a:hover {
    text-decoration: underline;
}

/* Alert Messages */
.alert {
    padding: 15px 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    animation: slideIn 0.5s ease-out;
    display: flex;
    align-items: center;
    gap: 10px;
}

.alert-success {
    background: #d1fae5;
    color: #065f46;
    border-left: 4px solid var(--success);
}

.alert-error {
    background: #fee2e2;
    color: #991b1b;
    border-left: 4px solid var(--danger);
}

.alert i {
    font-size: 1.2rem;
}

/* Animations */
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
    50% { transform: translateY(-20px) rotate(5deg); }
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}

/* Loading State */
.btn-register.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-register.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
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
    
    .role-selection {
        grid-template-columns: 1fr;
    }
    
    .card-footer {
        padding: 20px;
    }
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
}

/* Input Icons */
.input-icon-wrapper {
    position: relative;
}

.input-icon {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    cursor: pointer;
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

<div class="register-container">
    <!-- Animated Background Bubbles -->
    <div class="bg-bubble bg-bubble-1"></div>
    <div class="bg-bubble bg-bubble-2"></div>
    <div class="bg-bubble bg-bubble-3"></div>
    
    <!-- Registration Card -->
    <div class="register-card">
        <div class="card-header">
            <div class="header-icon">🚀</div>
            <h2>Create Account</h2>
            <p>Join our professional community</p>
        </div>
        
        <div class="card-body">
            <!-- Alert Messages (if any) -->
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
            
            <form action="../modules/auth/register_action.php" method="POST" id="registrationForm">
                <!-- Full Name Field -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user"></i>
                        Full Name
                    </label>
                    <div class="input-icon-wrapper">
                        <input type="text" 
                               name="full_name" 
                               class="form-control" 
                               placeholder="Enter your full name"
                               required 
                               minlength="3"
                               maxlength="100"
                               pattern="[A-Za-z\s]+"
                               title="Only letters and spaces allowed">
                        <i class="fas fa-check-circle input-icon" style="color: var(--success); display: none;"></i>
                    </div>
                </div>
                
                <!-- Email Field -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                        <span class="tooltip" data-tooltip="We'll never share your email">ⓘ</span>
                    </label>
                    <div class="input-icon-wrapper">
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="you@example.com"
                               required 
                               pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                               title="Enter a valid email address">
                        <i class="fas fa-check-circle input-icon" style="color: var(--success); display: none;"></i>
                    </div>
                </div>
                
                <!-- Account Type - Enhanced with Radio Cards (Backward Compatible) -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-user-tag"></i>
                        Account Type
                    </label>
                    
                    <!-- Hidden select for backend compatibility -->
                    <select name="role_id" id="roleSelect" style="display: none;">
                        <option value="3" selected>Job Seeker</option>
                        <option value="2">Employer</option>
                        <option value="1">Manager</option>
                    </select>
                    
                    <!-- Visual Radio Cards -->
                    <div class="role-selection">
                        <div class="role-option">
                            <input type="radio" 
                                   name="role_visual" 
                                   id="roleJobSeeker" 
                                   value="3" 
                                   checked 
                                   onchange="document.getElementById('roleSelect').value='3'">
                            <label for="roleJobSeeker">
                                <i class="fas fa-user-graduate"></i>
                                <span>Job Seeker</span>
                                <small>Find your dream job</small>
                            </label>
                        </div>
                        
                        <div class="role-option">
                            <input type="radio" 
                                   name="role_visual" 
                                   id="roleEmployer" 
                                   value="2"
                                   onchange="document.getElementById('roleSelect').value='2'">
                            <label for="roleEmployer">
                                <i class="fas fa-building"></i>
                                <span>Employer</span>
                                <small>Hire talented people</small>
                            </label>
                        </div>
                        
                        <div class="role-option">
                            <input type="radio" 
                                   name="role_visual" 
                                   id="roleManager" 
                                   value="1"
                                   onchange="document.getElementById('roleSelect').value='1'">
                            <label for="roleManager">
                                <i class="fas fa-crown"></i>
                                <span>Manager</span>
                                <small>System Administrator</small>
                            </label>
                        </div>
                    </div>
                </div>
                
                <!-- Password Field with Strength Meter -->
                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="input-icon-wrapper">
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-control" 
                               placeholder="Create a strong password"
                               required 
                               minlength="8">
                        <i class="fas fa-eye input-icon" id="togglePassword" style="cursor: pointer;"></i>
                    </div>
                    
                    <!-- Password Strength Meter -->
                    <div class="password-strength">
                        <div class="strength-bar">
                            <div class="strength-bar-fill" id="strengthBar"></div>
                        </div>
                        <span class="strength-text" id="strengthText">Enter password</span>
                    </div>
                    
                    <!-- Password Requirements -->
                    <div class="password-requirements">
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
                
                <!-- Terms and Conditions -->
                <div class="terms">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">
                        I agree to the <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>
                    </label>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-register" id="submitBtn">
                    <i class="fas fa-user-plus"></i> Create Account
                </button>
            </form>
        </div>
        
        <div class="card-footer">
            <p class="login-link">
                Already have an account? 
                <a href="login.php">
                    Sign In <i class="fas fa-arrow-right"></i>
                </a>
            </p>
        </div>
    </div>
</div>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- JavaScript for Enhanced Functionality -->
<script>
// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
});

// Password strength checker
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strengthBar = document.getElementById('strengthBar');
    const strengthText = document.getElementById('strengthText');
    
    // Requirements checks
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
    
    if (password.length === 0) {
        strengthBar.style.background = '#e2e8f0';
        strengthText.textContent = 'Enter password';
    } else if (metCount <= 2) {
        strengthBar.style.background = '#ef4444';
        strengthText.textContent = 'Weak password';
    } else if (metCount <= 4) {
        strengthBar.style.background = '#f59e0b';
        strengthText.textContent = 'Medium password';
    } else {
        strengthBar.style.background = '#10b981';
        strengthText.textContent = 'Strong password';
    }
});

// Form validation with loading state
document.getElementById('registrationForm').addEventListener('submit', function(e) {
    const submitBtn = document.getElementById('submitBtn');
    const terms = document.getElementById('terms');
    
    if (!terms.checked) {
        e.preventDefault();
        alert('Please agree to the Terms and Conditions');
        return;
    }
    
    // Show loading state
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Creating Account...';
    submitBtn.classList.add('loading');
});

// Live email validation
document.querySelector('input[name="email"]').addEventListener('input', function() {
    const emailPattern = /^[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$/;
    const isValid = emailPattern.test(this.value);
    const icon = this.nextElementSibling;
    
    if (this.value.length > 0) {
        icon.style.display = isValid ? 'block' : 'none';
        this.style.borderColor = isValid ? '#10b981' : '#ef4444';
    } else {
        icon.style.display = 'none';
        this.style.borderColor = '#e2e8f0';
    }
});

// Live name validation
document.querySelector('input[name="full_name"]').addEventListener('input', function() {
    const namePattern = /^[A-Za-z\s]+$/;
    const isValid = namePattern.test(this.value) && this.value.length >= 3;
    const icon = this.nextElementSibling;
    
    if (this.value.length > 0) {
        icon.style.display = isValid ? 'block' : 'none';
        this.style.borderColor = isValid ? '#10b981' : '#ef4444';
    } else {
        icon.style.display = 'none';
        this.style.borderColor = '#e2e8f0';
    }
});

// Smooth scroll to errors
if (window.location.hash === '#error') {
    document.querySelector('.alert-error')?.scrollIntoView({ behavior: 'smooth' });
}
</script>

<?php include '../includes/footer.php'; ?>