<?php 
require_once '../config/db_connect.php';
include '../includes/header.php'; 
?>

<style>
/* About Page Styles */
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
    --gradient-accent: linear-gradient(135deg, #3498db, #e74c3c);
}

/* Hero Section */
.about-hero {
    background: var(--gradient);
    padding: 80px 20px;
    border-radius: 30px;
    margin-bottom: 50px;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
    animation: fadeIn 1s ease-out;
}

.about-hero::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 30s linear infinite;
}

.about-hero::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    height: 4px;
    background: var(--gradient-accent);
}

.about-hero h1 {
    font-size: 3.5rem;
    margin-bottom: 20px;
    position: relative;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
    animation: slideUp 1s ease-out;
}

.about-hero p {
    font-size: 1.3rem;
    max-width: 800px;
    margin: 0 auto;
    opacity: 0.95;
    position: relative;
    animation: slideUp 1s ease-out 0.2s both;
}

/* Stats Section */
.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    margin: 50px 0;
}

.stat-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.stat-card::before {
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

.stat-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.15);
}

.stat-card:hover::before {
    transform: scaleX(1);
}

.stat-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    animation: bounce 3s infinite;
}

.stat-number {
    font-size: 2.5rem;
    font-weight: 800;
    color: var(--secondary);
    margin: 10px 0;
}

.stat-label {
    color: var(--gray);
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

/* Mission & Vision Section */
.mission-vision {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin: 50px 0;
}

.mv-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
    position: relative;
    overflow: hidden;
}

.mv-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.1);
}

.mv-icon {
    width: 80px;
    height: 80px;
    background: var(--gradient);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 25px;
}

.mv-icon i {
    font-size: 2.5rem;
    color: white;
}

.mv-card h3 {
    font-size: 1.8rem;
    color: var(--dark);
    margin-bottom: 15px;
}

.mv-card p {
    color: var(--gray);
    line-height: 1.8;
}

/* Features Grid */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 30px;
    margin: 50px 0;
}

.feature-item {
    background: white;
    border-radius: 20px;
    padding: 30px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.feature-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.1);
}

.feature-icon {
    width: 70px;
    height: 70px;
    background: linear-gradient(135deg, rgba(52, 152, 219, 0.1), rgba(231, 76, 60, 0.1));
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin: 0 auto 20px;
}

.feature-icon i {
    font-size: 2rem;
    color: var(--secondary);
}

.feature-item h4 {
    font-size: 1.3rem;
    color: var(--dark);
    margin-bottom: 10px;
}

.feature-item p {
    color: var(--gray);
    font-size: 0.95rem;
    line-height: 1.6;
}

/* Team Section */
.team-section {
    margin: 60px 0;
    text-align: center;
}

.section-title {
    margin-bottom: 40px;
    position: relative;
}

.section-title h2 {
    font-size: 2.5rem;
    color: var(--dark);
    display: inline-block;
    padding-bottom: 15px;
    position: relative;
}

.section-title h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 50%;
    transform: translateX(-50%);
    width: 80px;
    height: 4px;
    background: var(--gradient);
    border-radius: 2px;
}

.section-title p {
    color: var(--gray);
    font-size: 1.1rem;
    margin-top: 15px;
}

.team-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
    gap: 25px;
    margin-top: 30px;
}

.team-member {
    background: white;
    border-radius: 20px;
    padding: 25px 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
}

.team-member:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.15);
}

.member-avatar {
    width: 100px;
    height: 100px;
    background: var(--gradient);
    border-radius: 50%;
    margin: 0 auto 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 4px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.member-avatar i {
    font-size: 3rem;
    color: white;
}

.team-member h4 {
    font-size: 1.2rem;
    color: var(--dark);
    margin-bottom: 5px;
}

.member-id {
    color: var(--secondary);
    font-size: 0.9rem;
    font-weight: 600;
    margin-bottom: 10px;
}

.member-role {
    color: var(--gray);
    font-size: 0.9rem;
    background: #f8fafc;
    padding: 5px 15px;
    border-radius: 20px;
    display: inline-block;
}

/* Timeline Section */
.timeline-section {
    margin: 60px 0;
}

.timeline {
    position: relative;
    padding: 20px 0;
}

.timeline::before {
    content: '';
    position: absolute;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 100%;
    background: var(--gradient);
    border-radius: 2px;
}

.timeline-item {
    display: flex;
    justify-content: space-between;
    margin: 40px 0;
    position: relative;
}

.timeline-content {
    width: 45%;
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
}

.timeline-content:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.1);
}

.timeline-content.right {
    margin-left: auto;
}

.timeline-date {
    color: var(--secondary);
    font-weight: 700;
    font-size: 1.1rem;
    margin-bottom: 10px;
}

.timeline-title {
    font-size: 1.3rem;
    color: var(--dark);
    margin-bottom: 10px;
}

.timeline-text {
    color: var(--gray);
    line-height: 1.6;
}

/* Card Styles */
.card {
    background: white;
    border-radius: 20px;
    padding: 35px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    border: 1px solid #e2e8f0;
    transition: all 0.3s ease;
    margin-bottom: 30px;
}

.card:hover {
    box-shadow: 0 20px 40px rgba(52, 152, 219, 0.1);
}

.card h2 {
    color: var(--primary);
    font-size: 2rem;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.card h2::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 60px;
    height: 3px;
    background: var(--gradient);
    border-radius: 2px;
}

.card h3 {
    color: var(--primary);
    font-size: 1.5rem;
    margin-bottom: 15px;
}

.card ul {
    list-style: none;
    padding: 0;
}

.card ul li {
    padding: 10px 0;
    padding-left: 30px;
    position: relative;
    color: var(--gray);
}

.card ul li::before {
    content: '✓';
    position: absolute;
    left: 0;
    color: var(--success);
    font-weight: bold;
    font-size: 1.2rem;
}

/* CTA Section */
.cta-section {
    background: var(--gradient);
    border-radius: 30px;
    padding: 60px 40px;
    margin: 60px 0;
    text-align: center;
    color: white;
    position: relative;
    overflow: hidden;
}

.cta-section::before {
    content: '';
    position: absolute;
    top: -50%;
    left: -50%;
    width: 200%;
    height: 200%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: rotate 30s linear infinite;
}

.cta-section h2 {
    font-size: 2.8rem;
    margin-bottom: 20px;
    position: relative;
}

.cta-section p {
    font-size: 1.2rem;
    max-width: 600px;
    margin: 0 auto 30px;
    opacity: 0.95;
    position: relative;
}

.cta-buttons {
    display: flex;
    gap: 20px;
    justify-content: center;
    position: relative;
}

.btn-cta {
    padding: 15px 40px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
}

.btn-cta-primary {
    background: white;
    color: var(--secondary);
}

.btn-cta-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.2);
}

.btn-cta-secondary {
    background: transparent;
    color: white;
    border: 2px solid white;
}

.btn-cta-secondary:hover {
    background: white;
    color: var(--secondary);
    transform: translateY(-3px);
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes slideUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes rotate {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .about-hero h1 {
        font-size: 2.5rem;
    }
    
    .about-hero p {
        font-size: 1.1rem;
    }
    
    .section-title h2 {
        font-size: 2rem;
    }
    
    .timeline::before {
        left: 30px;
    }
    
    .timeline-item {
        flex-direction: column;
    }
    
    .timeline-content {
        width: 100%;
        margin-left: 60px !important;
    }
    
    .cta-section h2 {
        font-size: 2rem;
    }
    
    .cta-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .btn-cta {
        width: 100%;
        max-width: 300px;
        justify-content: center;
    }
}

/* Counter Animation */
@keyframes countUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
</style>

<!-- Hero Section -->
<section class="about-hero">
    <h1>About Online Job Tracking System</h1>
    <p>Bridging the gap between talent and opportunity through automated recruitment technology.</p>
</section>

<!-- Stats Section -->
<div class="stats-grid">
    <?php
    // Get statistics from database
    try {
        $totalUsers = $conn->query("SELECT COUNT(*) FROM tbl_users")->fetchColumn();
        $totalJobs = $conn->query("SELECT COUNT(*) FROM tbl_jobs")->fetchColumn();
        $totalApplications = $conn->query("SELECT COUNT(*) FROM tbl_applications")->fetchColumn();
        $totalCompanies = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Role_ID = 2")->fetchColumn();
    } catch (PDOException $e) {
        $totalUsers = 0; $totalJobs = 0; $totalApplications = 0; $totalCompanies = 0;
    }
    ?>
    
    <div class="stat-card">
        <div class="stat-icon">👥</div>
        <div class="stat-number"><?php echo number_format($totalUsers); ?></div>
        <div class="stat-label">Registered Users</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">💼</div>
        <div class="stat-number"><?php echo number_format($totalJobs); ?></div>
        <div class="stat-label">Active Jobs</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">📄</div>
        <div class="stat-number"><?php echo number_format($totalApplications); ?></div>
        <div class="stat-label">Applications</div>
    </div>
    
    <div class="stat-card">
        <div class="stat-icon">🏢</div>
        <div class="stat-number"><?php echo number_format($totalCompanies); ?></div>
        <div class="stat-label">Companies</div>
    </div>
</div>

<!-- Mission & Vision -->
<div class="mission-vision">
    <div class="mv-card">
        <div class="mv-icon">
            <i class="fas fa-bullseye"></i>
        </div>
        <h3>Our Mission</h3>
        <p>To revolutionize the recruitment process in Ethiopia by providing a seamless, efficient, and accessible platform that connects job seekers with employers, eliminating traditional barriers and fostering professional growth.</p>
    </div>
    
    <div class="mv-card">
        <div class="mv-icon">
            <i class="fas fa-eye"></i>
        </div>
        <h3>Our Vision</h3>
        <p>To become Ethiopia's leading digital recruitment solution, setting the standard for modern hiring practices and contributing to the reduction of unemployment through technology.</p>
    </div>
</div>

<!-- Project Background Card -->
<div class="card">
    <h2>Project Background</h2>
    <p>
        In today's fast-paced world, the traditional manual method of job searching and recruitment is becoming obsolete. 
        As detailed in our initial study at Arsi University, many organizations still rely on physical notice boards and 
        paper applications, which are time-consuming and prone to data loss.
    </p>
    <p style="margin-top: 15px;">
        The <strong>Online Job Tracking System</strong> was developed to solve these challenges by providing a 
        centralized digital platform where employers can post vacancies and job seekers can apply from anywhere, 
        at any time.
    </p>
</div>

<!-- Objectives and Features Grid -->
<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
    <div class="card">
        <h3><i class="fas fa-target" style="color: var(--secondary); margin-right: 10px;"></i> Our Objectives</h3>
        <ul>
            <li>To automate the job application and recruitment process.</li>
            <li>To provide a secure and user-friendly interface for all actors.</li>
            <li>To ensure real-time tracking of application statuses.</li>
            <li>To reduce the cost and time associated with manual hiring.</li>
            <li>To create a centralized database for job opportunities.</li>
        </ul>
    </div>

    <div class="card">
        <h3><i class="fas fa-star" style="color: var(--secondary); margin-right: 10px;"></i> Key Features</h3>
        <ul>
            <li><strong>Role-Based Access:</strong> Tailored experiences for Managers, Employers, and Job Seekers.</li>
            <li><strong>Resume Management:</strong> Instant PDF uploads for professional profiling.</li>
            <li><strong>Secure Database:</strong> Protected by PDO and Bcrypt encryption standards.</li>
            <li><strong>Real-time Tracking:</strong> Monitor application status from submission to hiring.</li>
            <li><strong>Smart Search:</strong> Advanced filtering for job opportunities.</li>
        </ul>
    </div>
</div>

<!-- Features Showcase -->
<div class="features-grid">
    <div class="feature-item">
        <div class="feature-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        <h4>Secure Platform</h4>
        <p>Enterprise-grade security with encrypted data and protected user information.</p>
    </div>
    
    <div class="feature-item">
        <div class="feature-icon">
            <i class="fas fa-mobile-alt"></i>
        </div>
        <h4>Mobile Responsive</h4>
        <p>Access from any device with our fully responsive design.</p>
    </div>
    
    <div class="feature-item">
        <div class="feature-icon">
            <i class="fas fa-bolt"></i>
        </div>
        <h4>Fast Processing</h4>
        <p>Quick application submission and instant status updates.</p>
    </div>
    
    <div class="feature-item">
        <div class="feature-icon">
            <i class="fas fa-chart-line"></i>
        </div>
        <h4>Analytics</h4>
        <p>Detailed insights and reports for employers and managers.</p>
    </div>
</div>

<!-- Timeline Section -->
<div class="timeline-section">
    <div class="section-title">
        <h2>Project Timeline</h2>
        <p>Our journey from concept to completion</p>
    </div>
    
    <div class="timeline">
        <div class="timeline-item">
            <div class="timeline-content">
                <div class="timeline-date">October 2025</div>
                <div class="timeline-title">Project Initiation</div>
                <div class="timeline-text">Initial research and requirement gathering at Arsi University.</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-content right">
                <div class="timeline-date">November 2025</div>
                <div class="timeline-title">System Design</div>
                <div class="timeline-text">UML modeling, database design, and architecture planning.</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-content">
                <div class="timeline-date">December 2025</div>
                <div class="timeline-title">Development Phase</div>
                <div class="timeline-text">Frontend and backend development using PHP and MySQL.</div>
            </div>
        </div>
        
        <div class="timeline-item">
            <div class="timeline-content right">
                <div class="timeline-date">January 2026</div>
                <div class="timeline-title">Testing & Deployment</div>
                <div class="timeline-text">Comprehensive testing and final deployment.</div>
            </div>
        </div>
    </div>
</div>

<!-- Team Section -->
<section class="team-section">
    <div class="section-title">
        <h2>Developed By</h2>
        <p>Computer Science Department, Arsi University</p>
    </div>
    
    <div class="team-grid">
        <div class="team-member">
            <div class="member-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Addisu Teshome</h4>
            <div class="member-id">ID: 13485/15</div>
            <div class="member-role">Team Lead</div>
        </div>
        
        <div class="team-member">
            <div class="member-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Muzayen Hussein</h4>
            <div class="member-id">ID: 13907/15</div>
            <div class="member-role">Backend Developer</div>
        </div>
        
        <div class="team-member">
            <div class="member-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Nuhamin Sileshi</h4>
            <div class="member-id">ID: 13968/15</div>
            <div class="member-role">Frontend Developer</div>
        </div>
        
        <div class="team-member">
            <div class="member-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Akiber Adisu</h4>
            <div class="member-id">ID: 13497/15</div>
            <div class="member-role">Database Designer</div>
        </div>
        
        <div class="team-member">
            <div class="member-avatar">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h4>Natnael Asnake</h4>
            <div class="member-id">ID: 14515/15</div>
            <div class="member-role">UI/UX Designer</div>
        </div>
    </div>
    
    <div style="margin-top: 30px; padding: 20px; background: #f8fafc; border-radius: 15px; display: inline-block;">
        <p style="color: var(--gray);">
            <i class="fas fa-calendar-alt" style="color: var(--secondary); margin-right: 10px;"></i>
            Project Date: January 21, 2026
        </p>
    </div>
</section>

<!-- Technologies Used -->
<div class="card" style="text-align: center;">
    <h3><i class="fas fa-tools" style="color: var(--secondary); margin-right: 10px;"></i> Technologies Used</h3>
    <div style="display: flex; flex-wrap: wrap; gap: 15px; justify-content: center; margin-top: 20px;">
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">PHP 8.0</span>
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">MySQL</span>
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">HTML5</span>
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">CSS3</span>
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">JavaScript</span>
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">UML</span>
        <span class="btn" style="background: #f1f5f9; color: #1e293b; cursor: default; padding: 10px 20px;">PDO</span>
    </div>
</div>

<!-- Call to Action -->
<section class="cta-section">
    <h2>Ready to Start Your Journey?</h2>
    <p>Join thousands of job seekers and employers who trust our platform for their recruitment needs.</p>
    <div class="cta-buttons">
        <a href="register.php" class="btn-cta btn-cta-primary">
            <i class="fas fa-user-plus"></i> Get Started Today
        </a>
        <a href="contact.php" class="btn-cta btn-cta-secondary">
            <i class="fas fa-envelope"></i> Contact Us
        </a>
    </div>
</section>

<!-- Add Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<script>
// Counter animation for stats
function animateNumbers() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(number => {
        const finalValue = parseInt(number.innerText.replace(/,/g, ''));
        let currentValue = 0;
        const increment = finalValue / 50;
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                number.innerText = finalValue.toLocaleString();
                clearInterval(timer);
            } else {
                number.innerText = Math.floor(currentValue).toLocaleString();
            }
        }, 30);
    });
}

// Trigger animation when page loads
window.addEventListener('load', animateNumbers);

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>