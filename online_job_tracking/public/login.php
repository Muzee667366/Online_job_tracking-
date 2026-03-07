<?php include '../includes/header.php'; ?>

<style>
/* Modern Login Page Styles */
:root {
    --primary: #2c3e50;
    --primary-light: #34495e;
    --secondary: #3498db;
    --accent: #e74c3c;
    --success: #27ae60;
    --warning: #f39c12;
    --dark: #2c3e50;
    --light: #ecf0f1;
    --gray: #7f8c8d;
    --gradient: linear-gradient(135deg, #2c3e50, #3498db);
    --shadow-sm: 0 2px 10px rgba(0,0,0,0.1);
    --shadow-md: 0 5px 20px rgba(0,0,0,0.15);
    --shadow-lg: 0 10px 30px rgba(0,0,0,0.2);
}

/* Page Background */
body {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    min-height: 100vh;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

/* Main Container */
.login-wrapper {
    min-height: calc(100vh - 200px);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 20px;
    position: relative;
    overflow: hidden;
}

/* Animated Background Elements */
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

/* Floating Particles */
.particle {
    position: absolute;
    width: 6px;
    height: 6px;
    background: rgba(255, 255, 255, 0.3);
    border-radius: 50%;
}

.particle-1 { top: 20%; left: 15%; animation: float-particle 8s infinite; }
.particle-2 { top: 60%; right: 20%; animation: float-particle 10s infinite reverse; }
.particle-3 { top: 40%; left: 30%; animation: float-particle 12s infinite 1s; }
.particle-4 { bottom: 30%; right: 30%; animation: float-particle 9s infinite 0.5s; }

/* Login Card */
.login-card {
    max-width: 450px;
    width: 100%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(10px);
    border-radius: 30px;
    box-shadow: var(--shadow-lg);
    overflow: hidden;
    position: relative;
    z-index: 10;
    animation: slideUpFade 0.8s ease-out;
    border: 1px solid rgba(255, 255, 255, 0.2);
}

/* Card Header */
.card-header {
    background: var(--gradient);
    padding: 40px 30px 30px;
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

.card-header::after {
    content: '';
    position: absolute;
    bottom: -20px;
    left: 0;
    right: 0;
    height: 40px;
    background: inherit;
    filter: blur(20px);
    opacity: 0.5;
}

.header-icon {
    width: 90px;
    height: 90px;
    background: rgba(255, 255, 255, 0.2);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
    border: 3px solid rgba(255, 255, 255, 0.5);
    animation: pulse 2s infinite;
}

.header-icon i {
    font-size: 3rem;
    color: white;
}

.card-header h2 {
    color: white;
    font-size: 2.5rem;
    font-weight: 700;
    margin: 10px 0 5px;
    position: relative;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    letter-spacing: -0.5px;
}

.card-header p {
    color: rgba(255, 255, 255, 0.9);
    font-size: 1rem;
    position: relative;
    font-weight: 300;
}

/* Card Body */
.card-body {
    padding: 40px;
    background: white;
}

/* Welcome Message */
.welcome-message {
    text-align: center;
    margin-bottom: 30px;
}

.welcome-message h3 {
    color: var(--dark);
    font-size: 1.5rem;
    font-weight: 600;
    margin-bottom: 5px;
}

.welcome-message p {
    color: var(--gray);
    font-size: 0.95rem;
}

/* Form Groups */
.form-group {
    margin-bottom: 25px;
    position: relative;
}

.form-group label {
    display: flex;
    align-items: center;
    gap: 8px;
    margin-bottom: 10px;
    color: var(--dark);
    font-weight: 500;
    font-size: 0.95rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.form-group label i {
    color: var(--secondary);
    font-size: 1rem;
    width: 18px;
}

/* Input Fields */
.input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
}

.input-wrapper i.input-icon {
    position: absolute;
    left: 15px;
    color: var(--gray);
    font-size: 1.2rem;
    transition: all 0.3s ease;
    z-index: 1;
}

.form-control {
    width: 100%;
    padding: 15px 15px 15px 45px;
    border: 2px solid #e0e0e0;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #f8f9fa;
    box-sizing: border-box;
    font-family: inherit;
}

.form-control:hover {
    border-color: var(--secondary);
    background: white;
}

.form-control:focus {
    outline: none;
    border-color: var(--secondary);
    background: white;
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1);
}

.form-control:focus + .input-icon {
    color: var(--secondary);
}

/* Password Field Specific */
.password-wrapper {
    position: relative;
}

.password-toggle {
    position: absolute;
    right: 15px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--gray);
    cursor: pointer;
    transition: all 0.3s ease;
    z-index: 2;
    background: #f8f9fa;
    padding: 5px;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.password-toggle:hover {
    color: var(--secondary);
    background: white;
}

/* Form Options */
.form-options {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin: 20px 0 25px;
}

.remember-me {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    user-select: none;
}

.remember-me input[type="checkbox"] {
    width: 18px;
    height: 18px;
    cursor: pointer;
    accent-color: var(--secondary);
}

.remember-me span {
    color: var(--gray);
    font-size: 0.95rem;
}

.forgot-link {
    color: var(--secondary);
    text-decoration: none;
    font-size: 0.95rem;
    font-weight: 500;
    transition: all 0.3s ease;
    position: relative;
}

.forgot-link::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--gradient);
    transition: width 0.3s ease;
}

.forgot-link:hover {
    color: var(--accent);
}

.forgot-link:hover::after {
    width: 100%;
}

/* Login Button */
.btn-login {
    width: 100%;
    padding: 16px;
    background: var(--gradient);
    color: white;
    border: none;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    margin-bottom: 25px;
}

.btn-login::before {
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

.btn-login:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px var(--secondary);
}

.btn-login:hover::before {
    width: 300px;
    height: 300px;
}

.btn-login i {
    transition: transform 0.3s ease;
}

.btn-login:hover i {
    transform: translateX(5px);
}

/* Alternative Login */
.alternative-login {
    text-align: center;
    position: relative;
    margin: 30px 0 20px;
}

.alternative-login::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: linear-gradient(to right, transparent, #e0e0e0, transparent);
}

.alternative-login span {
    background: white;
    padding: 0 15px;
    color: var(--gray);
    font-size: 0.9rem;
    position: relative;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Social Login */
.social-login {
    display: flex;
    gap: 15px;
    justify-content: center;
    margin-top: 25px;
}

.social-btn {
    width: 50px;
    height: 50px;
    border-radius: 12px;
    border: 2px solid #e0e0e0;
    background: white;
    color: var(--gray);
    font-size: 1.3rem;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
}

.social-btn:hover {
    transform: translateY(-3px);
    border-color: transparent;
}

.social-btn.google:hover {
    background: #DB4437;
    color: white;
    box-shadow: 0 5px 15px -5px #DB4437;
}

.social-btn.facebook:hover {
    background: #4267B2;
    color: white;
    box-shadow: 0 5px 15px -5px #4267B2;
}

.social-btn.linkedin:hover {
    background: #0077B5;
    color: white;
    box-shadow: 0 5px 15px -5px #0077B5;
}

/* Card Footer */
.card-footer {
    text-align: center;
    padding: 30px;
    background: #f8f9fa;
    border-top: 1px solid #e0e0e0;
}

.register-link {
    color: var(--gray);
    font-size: 1rem;
    margin-bottom: 10px;
}

.register-link a {
    color: var(--secondary);
    text-decoration: none;
    font-weight: 600;
    margin-left: 5px;
    transition: all 0.3s ease;
    position: relative;
}

.register-link a::after {
    content: '';
    position: absolute;
    bottom: -2px;
    left: 0;
    width: 0;
    height: 2px;
    background: var(--gradient);
    transition: width 0.3s ease;
}

.register-link a:hover {
    color: var(--accent);
}

.register-link a:hover::after {
    width: 100%;
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

.alert i {
    font-size: 1.2rem;
}

/* Animations */
@keyframes slideUpFade {
    from {
        opacity: 0;
        transform: translateY(50px);
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

@keyframes float-particle {
    0%, 100% { transform: translate(0, 0); }
    25% { transform: translate(15px, -15px); }
    50% { transform: translate(25px, 0); }
    75% { transform: translate(15px, 15px); }
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes pulse {
    0%, 100% { transform: scale(1); }
    50% { transform: scale(1.05); }
}

/* Responsive Design */
@media (max-width: 480px) {
    .card-header {
        padding: 30px 20px;
    }
    
    .card-header h2 {
        font-size: 2rem;
    }
    
    .card-body {
        padding: 30px 20px;
    }
    
    .form-options {
        flex-direction: column;
        gap: 15px;
        align-items: flex-start;
    }
    
    .card-footer {
        padding: 20px;
    }
    
    .social-login {
        gap: 10px;
    }
}

/* Loading State */
.btn-login.loading {
    pointer-events: none;
    opacity: 0.8;
}

.btn-login.loading i {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style>

<div class="login-wrapper">
    <!-- Animated Background Elements -->
    <div class="bg-shape shape-1"></div>
    <div class="bg-shape shape-2"></div>
    <div class="bg-shape shape-3"></div>
    
    <!-- Floating Particles -->
    <div class="particle particle-1"></div>
    <div class="particle particle-2"></div>
    <div class="particle particle-3"></div>
    <div class="particle particle-4"></div>
    
    <!-- Login Card -->
    <div class="login-card">
        <div class="card-header">
            <div class="header-icon">
                <i class="fas fa-briefcase"></i>
            </div>
            <h2>Welcome Back</h2>
            <p>Sign in to your account</p>
        </div>
        
        <div class="card-body">
            <!-- Alert Messages (preserved from backend) -->
            <?php if (isset($_GET['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span><?php echo htmlspecialchars(urldecode($_GET['error'])); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span><?php echo htmlspecialchars(urldecode($_GET['success'])); ?></span>
            </div>
            <?php endif; ?>
            
            <?php if (isset($_GET['registered'])): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>Registration successful! Please login.</span>
            </div>
            <?php endif; ?>
            
            <!-- Welcome Message -->
            <div class="welcome-message">
                <h3>Secure Login</h3>
                <p>Please enter your credentials</p>
            </div>
            
            <!-- Login Form (completely unchanged backend) -->
            <form action="../modules/auth/login_action.php" method="POST" id="loginForm">
                <!-- Email Field -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" 
                               name="email" 
                               class="form-control" 
                               placeholder="Enter your email"
                               required 
                               value="<?php echo isset($_GET['email']) ? htmlspecialchars($_GET['email']) : ''; ?>">
                    </div>
                </div>
                
                <!-- Password Field -->
                <div class="form-group">
                    <label>
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <div class="password-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" 
                               name="password" 
                               id="password"
                               class="form-control" 
                               placeholder="Enter your password"
                               required>
                        <i class="fas fa-eye password-toggle" id="togglePassword"></i>
                    </div>
                </div>
                
                <!-- Form Options (added for better UX but won't affect backend) -->
                <div class="form-options">
                    <label class="remember-me">
                        <input type="checkbox" name="remember" id="remember">
                        <span>Remember me</span>
                    </label>
                    <a href="forgot-password.php" class="forgot-link">
                        Forgot Password?
                    </a>
                </div>
                
                <!-- Submit Button -->
                <button type="submit" class="btn-login" id="loginBtn">
                    <i class="fas fa-sign-in-alt"></i>
                    Sign In
                </button>
                
                <!-- Alternative Login (purely visual, won't affect backend) -->
                <div class="alternative-login">
                    <span>Or continue with</span>
                </div>
                
                <div class="social-login">
                    <button type="button" class="social-btn google" onclick="window.location.href='../modules/auth/google_login.php'">
                        <i class="fab fa-google"></i>
                    </button>
                    <button type="button" class="social-btn facebook" onclick="window.location.href='../modules/auth/facebook_login.php'">
                        <i class="fab fa-facebook-f"></i>
                    </button>
                    <button type="button" class="social-btn linkedin" onclick="window.location.href='../modules/auth/linkedin_login.php'">
                        <i class="fab fa-linkedin-in"></i>
                    </button>
                </div>
            </form>
        </div>
        
        <div class="card-footer">
            <p class="register-link">
                Don't have an account? 
                <a href="register.php">
                    Create Account <i class="fas fa-arrow-right"></i>
                </a>
            </p>
            <p style="color: var(--gray); font-size: 0.8rem; margin: 10px 0 0;">
                © 2026 Online Job Tracking System
            </p>
        </div>
    </div>
</div>

<!-- JavaScript for Enhanced UX (without affecting backend) -->
<script>
// Password visibility toggle
document.getElementById('togglePassword').addEventListener('click', function() {
    const password = document.getElementById('password');
    const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
    password.setAttribute('type', type);
    this.classList.toggle('fa-eye');
    this.classList.toggle('fa-eye-slash');
    
    // Visual feedback
    this.style.transform = 'translateY(-50%) scale(1.2)';
    setTimeout(() => {
        this.style.transform = 'translateY(-50%) scale(1)';
    }, 200);
});

// Form submission with loading state
document.getElementById('loginForm').addEventListener('submit', function(e) {
    const loginBtn = document.getElementById('loginBtn');
    const email = document.querySelector('input[name="email"]').value.trim();
    const password = document.querySelector('input[name="password"]').value;
    
    // Show loading state (visual only, doesn't affect submission)
    loginBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
    loginBtn.classList.add('loading');
    
    // Form will still submit normally to login_action.php
});

// Input focus effects
document.querySelectorAll('.form-control').forEach(input => {
    input.addEventListener('focus', function() {
        this.parentElement.querySelector('.input-icon').style.color = 'var(--secondary)';
    });
    
    input.addEventListener('blur', function() {
        if (!this.value) {
            this.parentElement.querySelector('.input-icon').style.color = 'var(--gray)';
        }
    });
});

// Smooth scroll to error messages
if (window.location.search.includes('error')) {
    document.querySelector('.alert-error')?.scrollIntoView({ 
        behavior: 'smooth', 
        block: 'center' 
    });
}

// Optional: Add keyboard shortcut (Enter key)
document.addEventListener('keypress', function(e) {
    if (e.key === 'Enter' && e.target.tagName !== 'BUTTON') {
        const form = document.getElementById('loginForm');
        if (form) {
            form.requestSubmit();
        }
    }
});
</script>

<!-- Add Font Awesome if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<?php include '../includes/footer.php'; ?>