<?php 
require_once '../config/db_connect.php';
include '../includes/header.php'; 

// Get statistics with error handling
try {
    $totalJobs = $conn->query("SELECT COUNT(*) FROM tbl_jobs")->fetchColumn();
    $totalCompanies = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Role_ID = 2")->fetchColumn();
    $totalJobSeekers = $conn->query("SELECT COUNT(*) FROM tbl_users WHERE Role_ID = 3")->fetchColumn();
    $recentJobs = $conn->query("SELECT * FROM tbl_jobs ORDER BY Posting_Date DESC LIMIT 3")->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $totalJobs = 0;
    $totalCompanies = 0;
    $totalJobSeekers = 0;
    $recentJobs = [];
}
?>

<style>
/* Modern CSS with animations */
:root {
    --primary: #2563eb;
    --primary-dark: #1e40af;
    --primary-light: #60a5fa;
    --secondary: #7c3aed;
    --accent: #f59e0b;
    --dark: #0f172a;
    --light: #f8fafc;
    --gray: #64748b;
    --success: #10b981;
    --gradient: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
}

/* Hero Section Styles */
.hero-section {
    padding: 100px 20px;
    background: var(--gradient);
    border-radius: 30px;
    margin-bottom: 50px;
    position: relative;
    overflow: hidden;
    animation: fadeIn 1s ease-out;
}

.hero-section::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320"><path fill="%23ffffff" fill-opacity="0.1" d="M0,96L48,112C96,128,192,160,288,160C384,160,480,128,576,122.7C672,117,768,139,864,154.7C960,171,1056,181,1152,165.3C1248,149,1344,107,1392,85.3L1440,64L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path></svg>') bottom no-repeat;
    background-size: cover;
    opacity: 0.1;
    animation: wave 10s linear infinite;
}

.hero-content {
    position: relative;
    z-index: 2;
    animation: slideUp 1s ease-out 0.3s both;
}

.hero-title {
    font-size: 4rem;
    margin-bottom: 20px;
    font-weight: 800;
    line-height: 1.2;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
}

.hero-subtitle {
    font-size: 1.3rem;
    opacity: 0.95;
    max-width: 800px;
    margin: 0 auto 40px;
    line-height: 1.6;
}

/* Button Styles */
.btn-group {
    display: flex;
    gap: 20px;
    justify-content: center;
    flex-wrap: wrap;
}

.btn {
    padding: 15px 45px;
    border-radius: 50px;
    font-weight: 600;
    font-size: 1.1rem;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
    position: relative;
    overflow: hidden;
}

.btn::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 0;
    height: 0;
    border-radius: 50%;
    background: rgba(255,255,255,0.3);
    transform: translate(-50%, -50%);
    transition: width 0.6s, height 0.6s;
}

.btn:hover::before {
    width: 300px;
    height: 300px;
}

.btn-primary {
    background: white;
    color: var(--primary);
    box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}

.btn-primary:hover {
    transform: translateY(-3px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.2);
    color: var(--primary-dark);
}

.btn-outline {
    background: transparent;
    color: white;
    border: 2px solid white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.1);
}

.btn-outline:hover {
    background: white;
    color: var(--primary);
    transform: translateY(-3px);
}

/* Feature Cards */
.features-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 30px;
    margin: 60px 0;
}

.feature-card {
    background: white;
    border-radius: 20px;
    padding: 40px 30px;
    text-align: center;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    position: relative;
    overflow: hidden;
    animation: fadeInUp 1s ease-out;
}

.feature-card::after {
    content: '';
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: var(--gradient);
    transform: scaleX(0);
    transition: transform 0.3s ease;
}

.feature-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 20px 40px rgba(37, 99, 235, 0.15);
}

.feature-card:hover::after {
    transform: scaleX(1);
}

.feature-icon {
    font-size: 4rem;
    margin-bottom: 20px;
    animation: bounce 2s infinite;
}

.feature-card h3 {
    font-size: 1.8rem;
    color: var(--dark);
    margin-bottom: 15px;
}

.feature-card p {
    color: var(--gray);
    line-height: 1.6;
}

/* Statistics Section */
.stats-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 30px;
    padding: 60px 40px;
    margin: 50px 0;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 30px;
    text-align: center;
    animation: fadeIn 1s ease-out;
}

.stat-item {
    position: relative;
    padding: 20px;
    transition: transform 0.3s ease;
}

.stat-item:hover {
    transform: translateY(-5px);
}

.stat-item:hover .stat-number {
    color: var(--secondary);
}

.stat-number {
    font-size: 3.5rem;
    font-weight: 800;
    color: var(--primary);
    margin: 0;
    line-height: 1.2;
    animation: countUp 2s ease-out;
    transition: color 0.3s ease;
}

.stat-label {
    color: var(--gray);
    font-size: 1.1rem;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.stat-item:not(:last-child)::after {
    content: '';
    position: absolute;
    right: -15px;
    top: 50%;
    transform: translateY(-50%);
    height: 50px;
    width: 2px;
    background: linear-gradient(to bottom, transparent, var(--primary-light), transparent);
}

/* Recent Jobs Section */
.recent-jobs {
    margin: 60px 0;
}

.section-title {
    text-align: center;
    margin-bottom: 40px;
}

.section-title h2 {
    font-size: 2.8rem;
    color: var(--dark);
    position: relative;
    display: inline-block;
    padding-bottom: 15px;
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

.jobs-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
}

.job-card {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
    animation: fadeInUp 1s ease-out;
}

.job-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 15px 30px rgba(37, 99, 235, 0.1);
    border-color: var(--primary-light);
}

.job-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.job-title {
    font-size: 1.4rem;
    color: var(--dark);
    font-weight: 600;
}

.job-category {
    background: var(--primary-light);
    color: white;
    padding: 5px 12px;
    border-radius: 20px;
    font-size: 0.9rem;
}

.job-company {
    display: flex;
    align-items: center;
    gap: 10px;
    color: var(--gray);
    margin-bottom: 15px;
}

.job-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e2e8f0;
}

.job-date {
    color: var(--gray);
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 5px;
}

.btn-apply {
    background: var(--primary);
    color: white;
    padding: 8px 20px;
    border-radius: 25px;
    text-decoration: none;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

.btn-apply:hover {
    background: var(--primary-dark);
    transform: scale(1.05);
}

/* Testimonials Section */
.testimonials-section {
    margin: 60px 0;
}

.testimonials-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 30px;
    margin-top: 40px;
}

.testimonial-card {
    background: white;
    border-radius: 20px;
    padding: 30px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
    transition: all 0.3s ease;
    border: 1px solid #e2e8f0;
    text-align: center;
}

.testimonial-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(37, 99, 235, 0.1);
}

.testimonial-quote {
    font-size: 2rem;
    color: var(--primary-light);
    margin-bottom: 15px;
}

.testimonial-text {
    color: var(--gray);
    font-style: italic;
    margin-bottom: 20px;
    line-height: 1.6;
}

.testimonial-author {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.testimonial-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: var(--gradient);
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.5rem;
}

.testimonial-info h4 {
    color: var(--dark);
    margin: 0;
    font-size: 1.1rem;
}

.testimonial-info p {
    color: var(--gray);
    margin: 0;
    font-size: 0.9rem;
}

/* Newsletter Section */
.newsletter-section {
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: 20px;
    padding: 50px;
    margin: 60px 0;
    text-align: center;
}

.newsletter-title {
    font-size: 2rem;
    color: var(--dark);
    margin-bottom: 15px;
}

.newsletter-text {
    color: var(--gray);
    max-width: 500px;
    margin: 0 auto 30px;
}

.newsletter-form {
    display: flex;
    gap: 10px;
    max-width: 500px;
    margin: 0 auto;
}

.newsletter-input {
    flex: 1;
    padding: 15px;
    border: 2px solid #e2e8f0;
    border-radius: 10px;
    font-size: 1rem;
    transition: all 0.3s ease;
}

.newsletter-input:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
}

.newsletter-button {
    background: var(--gradient);
    color: white;
    border: none;
    padding: 15px 30px;
    border-radius: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.newsletter-button:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
}

/* Call to Action Section */
.cta-section {
    background: linear-gradient(135deg, var(--dark) 0%, #1e293b 100%);
    border-radius: 30px;
    padding: 80px 40px;
    margin: 60px 0;
    text-align: center;
    color: white;
    animation: fadeIn 1s ease-out;
}

.cta-title {
    font-size: 3rem;
    margin-bottom: 20px;
}

.cta-text {
    font-size: 1.2rem;
    opacity: 0.9;
    max-width: 700px;
    margin: 0 auto 40px;
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
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

@keyframes wave {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(20px); }
}

@keyframes bounce {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-10px); }
}

@keyframes countUp {
    from { opacity: 0; transform: translateY(20px); }
    to { opacity: 1; transform: translateY(0); }
}

@keyframes float {
    0%, 100% { transform: translateY(0); }
    50% { transform: translateY(-20px); }
}

@keyframes spin {
    to { transform: rotate(360deg); }
}

/* Responsive Design */
@media (max-width: 768px) {
    .hero-title {
        font-size: 2.5rem;
    }
    
    .hero-subtitle {
        font-size: 1.1rem;
    }
    
    .btn-group {
        flex-direction: column;
        align-items: center;
    }
    
    .btn {
        width: 100%;
        max-width: 300px;
    }
    
    .stats-section {
        grid-template-columns: 1fr;
    }
    
    .stat-item:not(:last-child)::after {
        display: none;
    }
    
    .section-title h2 {
        font-size: 2rem;
    }
    
    .newsletter-form {
        flex-direction: column;
    }
    
    .newsletter-button {
        width: 100%;
    }
}

/* Loading Animation */
.loading-spinner {
    display: inline-block;
    width: 50px;
    height: 50px;
    border: 3px solid rgba(37, 99, 235, 0.3);
    border-radius: 50%;
    border-top-color: var(--primary);
    animation: spin 1s ease-in-out infinite;
}

/* Button Hover Enhancement */
.btn-primary:hover i {
    transform: translateX(5px);
}

.btn-primary i {
    transition: transform 0.3s ease;
}
</style>

<!-- Hero Section with Animation -->
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Your Next Career Move<br>Starts Here</h1>
        <p class="hero-subtitle">
            The most efficient platform for Job Seekers to find opportunities and for Employers to manage talent. 
            Fully automated tracking as per the 2026 Recruitment Standards.
        </p>
        <div class="btn-group">
            <a href="register.php" class="btn btn-primary">
                <i class="fas fa-user-plus"></i> Join Now
            </a>
            <a href="jobs.php" class="btn btn-outline">
                <i class="fas fa-briefcase"></i> Browse Jobs
            </a>
        </div>
    </div>
</section>

<!-- Floating Elements Animation -->
<div style="position: relative; height: 0;">
    <div style="position: absolute; top: -100px; left: 10%; animation: float 6s infinite;">
        <i class="fas fa-circle" style="color: var(--primary-light); opacity: 0.2; font-size: 2rem;"></i>
    </div>
    <div style="position: absolute; top: -50px; right: 15%; animation: float 8s infinite;">
        <i class="fas fa-square" style="color: var(--secondary); opacity: 0.2; font-size: 3rem;"></i>
    </div>
</div>

<!-- Feature Cards with Icons -->
<div class="features-grid">
    <div class="feature-card">
        <div class="feature-icon">🔍</div>
        <h3>Smart Search</h3>
        <p>Use our advanced filtering system with real-time JavaScript to find the exact job category you're looking for instantly.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">📄</div>
        <h3>Resume Management</h3>
        <p>Upload your professional PDF credentials and let employers discover you based on your unique skills and experience.</p>
    </div>
    
    <div class="feature-card">
        <div class="feature-icon">📊</div>
        <h3>Real-time Tracking</h3>
        <p>Stay updated on your application status. From "Pending Review" to "Successfully Hired," track every step.</p>
    </div>
</div>

<!-- Statistics Section with Live Data -->
<div class="stats-section">
    <div class="stat-item">
        <h2 class="stat-number"><?php echo number_format($totalJobs); ?></h2>
        <p class="stat-label">Active Jobs</p>
        <div style="font-size: 2rem; color: var(--primary-light);">💼</div>
    </div>
    <div class="stat-item">
        <h2 class="stat-number"><?php echo number_format($totalCompanies); ?></h2>
        <p class="stat-label">Top Companies</p>
        <div style="font-size: 2rem; color: var(--primary-light);">🏢</div>
    </div>
    <div class="stat-item">
        <h2 class="stat-number"><?php echo number_format($totalJobSeekers); ?></h2>
        <p class="stat-label">Active Job Seekers</p>
        <div style="font-size: 2rem; color: var(--primary-light);">👥</div>
    </div>
    <div class="stat-item">
        <h2 class="stat-number">95%</h2>
        <p class="stat-label">Success Rate</p>
        <div style="font-size: 2rem; color: var(--primary-light);">⭐</div>
    </div>
</div>

<!-- Recent Job Listings -->
<?php if (!empty($recentJobs)): ?>
<div class="recent-jobs">
    <div class="section-title">
        <h2>Recent Job Opportunities</h2>
        <p style="color: var(--gray);">Discover your next career move</p>
    </div>
    
    <div class="jobs-grid">
        <?php foreach ($recentJobs as $job): ?>
        <div class="job-card">
            <div class="job-header">
                <h3 class="job-title"><?php echo htmlspecialchars($job['Job_Title']); ?></h3>
                <span class="job-category"><?php echo htmlspecialchars($job['Job_Category']); ?></span>
            </div>
            <div class="job-company">
                <i class="fas fa-building"></i>
                <span><?php echo htmlspecialchars($job['Company_Name'] ?? 'Company Name'); ?></span>
            </div>
            <div class="job-description" style="color: var(--gray); margin-bottom: 15px;">
                <?php echo substr(htmlspecialchars($job['Job_Description']), 0, 100); ?>...
            </div>
            <div class="job-footer">
                <span class="job-date">
                    <i class="far fa-calendar"></i>
                    <?php echo date('M d, Y', strtotime($job['Posting_Date'])); ?>
                </span>
                <a href="job-details.php?id=<?php echo $job['Job_ID']; ?>" class="btn-apply">
                    Apply Now <i class="fas fa-arrow-right"></i>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
    <div style="text-align: center; margin-top: 40px;">
        <a href="jobs.php" class="btn btn-primary" style="display: inline-flex; align-items: center; gap: 10px; padding: 12px 40px;">
            <span>View All Jobs</span>
            <i class="fas fa-arrow-right" style="transition: transform 0.3s ease;"></i>
        </a>
    </div>
</div>
<?php endif; ?>

<!-- Testimonials Section -->
<div class="testimonials-section">
    <div class="section-title">
        <h2>What Our Users Say</h2>
        <p style="color: var(--gray);">Trusted by job seekers and employers</p>
    </div>
    
    <div class="testimonials-grid">
        <div class="testimonial-card">
            <div class="testimonial-quote">
                <i class="fas fa-quote-right"></i>
            </div>
            <p class="testimonial-text">"This platform helped me find my dream job within a week. The application process was seamless and the real-time tracking kept me informed at every step!"</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">
                    <i class="fas fa-user"></i>
                </div>
                <div class="testimonial-info">
                    <h4>Abebe Kebede</h4>
                    <p>Software Engineer</p>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <div class="testimonial-quote">
                <i class="fas fa-quote-right"></i>
            </div>
            <p class="testimonial-text">"As an employer, finding qualified candidates has never been easier. The platform's filtering system helped us find the perfect match for our team quickly."</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">
                    <i class="fas fa-building"></i>
                </div>
                <div class="testimonial-info">
                    <h4>Tigist Haile</h4>
                    <p>HR Manager</p>
                </div>
            </div>
        </div>
        
        <div class="testimonial-card">
            <div class="testimonial-quote">
                <i class="fas fa-quote-right"></i>
            </div>
            <p class="testimonial-text">"The real-time tracking feature is amazing. I always know where my application stands. This transparency makes the job search process much less stressful."</p>
            <div class="testimonial-author">
                <div class="testimonial-avatar">
                    <i class="fas fa-user-graduate"></i>
                </div>
                <div class="testimonial-info">
                    <h4>Meron Ayele</h4>
                    <p>Recent Graduate</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Newsletter Section -->
<div class="newsletter-section">
    <h3 class="newsletter-title">Stay Updated</h3>
    <p class="newsletter-text">Get the latest job opportunities delivered straight to your inbox.</p>
    <form class="newsletter-form" onsubmit="event.preventDefault(); alert('Thank you for subscribing! You will receive job alerts soon.'); this.reset();">
        <input type="email" class="newsletter-input" placeholder="Enter your email address" required>
        <button type="submit" class="newsletter-button">
            <i class="fas fa-paper-plane"></i> Subscribe
        </button>
    </form>
</div>

<!-- Call to Action Section -->
<section class="cta-section">
    <h2 class="cta-title">Ready to Transform Your Career?</h2>
    <p class="cta-text">
        Join thousands of successful job seekers and employers who trust our platform for their recruitment needs.
    </p>
    <div class="btn-group">
        <a href="register.php?type=jobseeker" class="btn btn-primary">
            <i class="fas fa-user-graduate"></i> I'm a Job Seeker
        </a>
        <a href="register.php?type=employer" class="btn btn-outline">
            <i class="fas fa-building"></i> I'm an Employer
        </a>
    </div>
</section>

<!-- Add Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- JavaScript for Enhanced Functionality -->
<script>
// Mobile menu toggle function (if not already in header)
function toggleMenu() {
    const menu = document.getElementById('mainMenu');
    if (menu) {
        menu.classList.toggle('show');
        const btn = document.querySelector('.mobile-menu-btn i');
        if (btn) {
            if (menu.classList.contains('show')) {
                btn.classList.remove('fa-bars');
                btn.classList.add('fa-times');
            } else {
                btn.classList.remove('fa-times');
                btn.classList.add('fa-bars');
            }
        }
    }
}

// Animate statistics numbers on page load
document.addEventListener('DOMContentLoaded', function() {
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(number => {
        const text = number.innerText.replace(/,/g, '');
        const finalValue = parseInt(text);
        
        if (!isNaN(finalValue) && finalValue > 0) {
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
        }
    });
    
    // Highlight active page in menu
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.menu a');
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
});

// Smooth scroll for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('mainMenu');
    const btn = document.querySelector('.mobile-menu-btn');
    
    if (menu && btn) {
        if (!menu.contains(event.target) && !btn.contains(event.target) && menu.classList.contains('show')) {
            menu.classList.remove('show');
            const icon = btn.querySelector('i');
            if (icon) {
                icon.classList.remove('fa-times');
                icon.classList.add('fa-bars');
            }
        }
    }
});

// Close mobile menu on window resize
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const menu = document.getElementById('mainMenu');
        const btn = document.querySelector('.mobile-menu-btn i');
        if (menu) {
            menu.classList.remove('show');
        }
        if (btn) {
            btn.classList.remove('fa-times');
            btn.classList.add('fa-bars');
        }
    }
});

// Add scroll-to-top button functionality
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Create scroll-to-top button if it doesn't exist
window.addEventListener('scroll', function() {
    let scrollBtn = document.querySelector('.scroll-top');
    
    if (!scrollBtn && window.scrollY > 300) {
        const btn = document.createElement('button');
        btn.className = 'scroll-top';
        btn.innerHTML = '<i class="fas fa-arrow-up"></i>';
        btn.onclick = scrollToTop;
        btn.style.cssText = `
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: var(--gradient);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            z-index: 999;
            animation: fadeIn 0.3s ease-out;
        `;
        btn.onmouseover = () => {
            btn.style.transform = 'translateY(-5px)';
            btn.style.boxShadow = '0 5px 25px rgba(37, 99, 235, 0.4)';
        };
        btn.onmouseout = () => {
            btn.style.transform = 'translateY(0)';
            btn.style.boxShadow = '0 2px 20px rgba(0,0,0,0.1)';
        };
        document.body.appendChild(btn);
    } else if (scrollBtn && window.scrollY <= 300) {
        scrollBtn.remove();
    }
});
</script>

<?php include '../includes/footer.php'; ?>