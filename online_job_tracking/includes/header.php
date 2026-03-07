<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Job Tracking System</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Modern Professional Header Styles */
        :root {
            --primary: #2c3e50;      /* Dark blue-gray */
            --primary-light: #34495e; /* Lighter blue-gray */
            --secondary: #3498db;     /* Professional blue */
            --accent: #e74c3c;        /* Accent red for highlights */
            --text-light: #ecf0f1;    /* Light text */
            --text-dark: #2c3e50;      /* Dark text */
            --shadow: 0 2px 20px rgba(0,0,0,0.1);
            --gradient: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f5f7fa;
        }

        /* Navigation Bar */
        .nav {
            background: var(--gradient);
            padding: 0.8rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow);
            position: sticky;
            top: 0;
            z-index: 1000;
            backdrop-filter: blur(10px);
            border-bottom: 3px solid var(--accent);
        }

        /* Logo Section */
        .logo {
            display: flex;
            align-items: center;
            transition: transform 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.05);
        }

        .logo a {
            display: flex;
            align-items: center;
            text-decoration: none;
        }

        .logo img {
            max-height: 45px;
            width: auto;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .logo span {
            margin-left: 10px;
            font-size: 1.4rem;
            font-weight: 600;
            color: white;
            letter-spacing: 1px;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        /* Desktop Menu */
        .menu {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .menu a {
            color: var(--text-light);
            margin: 0 5px;
            padding: 10px 18px;
            text-decoration: none;
            font-weight: 500;
            border-radius: 30px;
            transition: all 0.3s ease;
            position: relative;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 1rem;
        }

        .menu a i {
            font-size: 1.1rem;
            transition: transform 0.3s ease;
        }

        /* Hover Effects */
        .menu a:hover {
            background: rgba(255, 255, 255, 0.15);
            transform: translateY(-2px);
        }

        .menu a:hover i {
            transform: translateY(-2px);
        }

        /* Active Link Indicator */
        .menu a.active {
            background: var(--accent);
            color: white;
            box-shadow: 0 4px 15px rgba(231, 76, 60, 0.3);
        }

        .menu a.active:hover {
            background: #c0392b;
        }

        /* Register Button Special Style */
        .menu a:last-child {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
        }

        .menu a:last-child:hover {
            border-color: var(--accent);
            background: transparent;
        }

        /* Mobile Menu Button */
        .mobile-menu-btn {
            display: none;
            background: transparent;
            border: none;
            color: white;
            font-size: 1.8rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .mobile-menu-btn:hover {
            transform: scale(1.1);
            color: var(--accent);
        }

        /* Responsive Design */
        @media screen and (max-width: 768px) {
            .nav {
                padding: 0.8rem 4%;
            }

            .logo span {
                font-size: 1.2rem;
            }

            .mobile-menu-btn {
                display: block;
            }

            .menu {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                right: 0;
                background: var(--gradient);
                flex-direction: column;
                padding: 20px;
                gap: 10px;
                box-shadow: var(--shadow);
                border-top: 2px solid var(--accent);
                animation: slideDown 0.3s ease-out;
            }

            .menu.show {
                display: flex;
            }

            .menu a {
                width: 100%;
                justify-content: center;
                padding: 15px;
                margin: 5px 0;
            }

            @keyframes slideDown {
                from {
                    opacity: 0;
                    transform: translateY(-10px);
                }
                to {
                    opacity: 1;
                    transform: translateY(0);
                }
            }
        }

        /* Small Mobile */
        @media screen and (max-width: 480px) {
            .logo span {
                display: none;
            }
            
            .logo img {
                max-height: 40px;
            }
        }

        /* User Dropdown Menu (for logged-in users) */
        .user-menu {
            position: relative;
            display: inline-block;
        }

        .user-menu-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 2px solid transparent;
            color: white;
            padding: 8px 20px;
            border-radius: 30px;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            font-size: 1rem;
        }

        .user-menu-btn:hover {
            border-color: var(--accent);
            background: rgba(255, 255, 255, 0.15);
        }

        .user-menu-btn i {
            font-size: 1.1rem;
        }

        .user-dropdown {
            display: none;
            position: absolute;
            top: 100%;
            right: 0;
            background: white;
            min-width: 200px;
            border-radius: 10px;
            box-shadow: var(--shadow);
            margin-top: 10px;
            overflow: hidden;
            animation: slideDown 0.3s ease-out;
        }

        .user-menu:hover .user-dropdown {
            display: block;
        }

        .user-dropdown a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: var(--text-dark);
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .user-dropdown a:hover {
            background: #f5f7fa;
            color: var(--accent);
            padding-left: 25px;
        }

        .user-dropdown i {
            width: 20px;
            color: var(--secondary);
        }

        /* Container Style */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
            animation: fadeIn 0.5s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Breadcrumb Navigation (optional) */
        .breadcrumb {
            background: white;
            padding: 15px 25px;
            border-radius: 10px;
            margin: 20px 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
        }

        .breadcrumb a {
            color: var(--secondary);
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 5px;
            transition: color 0.3s ease;
        }

        .breadcrumb a:hover {
            color: var(--accent);
        }

        .breadcrumb span {
            color: var(--text-dark);
            font-weight: 500;
        }

        .breadcrumb i {
            font-size: 0.9rem;
            color: var(--primary-light);
        }
    </style>
</head>
<body>
<!-- Navigation Bar -->
<nav class="nav">
    <!-- Logo Section -->
    <div class="logo">
        <a href="../public/index.php">
            <img src="../assets/img/bsd.png" alt="JobTracker Logo">
            <span>JobTracker</span>
        </a>
    </div>

    <!-- Desktop Menu -->
    <div class="menu" id="mainMenu">
        <a href="index.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'index.php' ? 'active' : ''; ?>">
            <i class="fas fa-home"></i> Home
        </a>
        <a href="about.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'about.php' ? 'active' : ''; ?>">
            <i class="fas fa-info-circle"></i> About Us
        </a>
        <a href="jobs.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'jobs.php' ? 'active' : ''; ?>">
            <i class="fas fa-briefcase"></i> Find Jobs
        </a>
        
        <?php if(isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true): ?>
            <!-- User Menu for Logged In Users -->
            <div class="user-menu">
                <button class="user-menu-btn">
                    <i class="fas fa-user-circle"></i>
                    <?php echo $_SESSION['user_name'] ?? 'Account'; ?>
                    <i class="fas fa-chevron-down"></i>
                </button>
                <div class="user-dropdown">
                    <a href="dashboard.php">
                        <i class="fas fa-tachometer-alt"></i> Dashboard
                    </a>
                    <a href="profile.php">
                        <i class="fas fa-user-edit"></i> My Profile
                    </a>
                    <?php if($_SESSION['user_role'] == 2): // Employer ?>
                        <a href="post-job.php">
                            <i class="fas fa-plus-circle"></i> Post Job
                        </a>
                    <?php endif; ?>
                    <a href="applications.php">
                        <i class="fas fa-file-alt"></i> My Applications
                    </a>
                    <hr style="margin: 8px 0; border: none; border-top: 1px solid #e2e8f0;">
                    <a href="../modules/auth/logout.php" style="color: #e74c3c;">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- Login/Register for Guests -->
            <a href="login.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'login.php' ? 'active' : ''; ?>">
                <i class="fas fa-sign-in-alt"></i> Login
            </a>
            <a href="register.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'register.php' ? 'active' : ''; ?>">
                <i class="fas fa-user-plus"></i> Register
            </a>
        <?php endif; ?>
    </div>

    <!-- Mobile Menu Button -->
    <button class="mobile-menu-btn" onclick="toggleMenu()">
        <i class="fas fa-bars"></i>
    </button>
</nav>

<!-- Optional: Breadcrumb Navigation -->
<div class="breadcrumb">
    <a href="index.php"><i class="fas fa-home"></i> Home</a>
    <?php
    // Dynamic breadcrumb based on current page
    $current_page = basename($_SERVER['PHP_SELF'], '.php');
    if($current_page != 'index') {
        echo '<i class="fas fa-chevron-right"></i>';
        echo '<span>' . ucfirst(str_replace('-', ' ', $current_page)) . '</span>';
    }
    ?>
</div>

<!-- Main Container -->
<div class="container">

<script>
// Mobile menu toggle function
function toggleMenu() {
    const menu = document.getElementById('mainMenu');
    menu.classList.toggle('show');
    
    // Change icon based on menu state
    const btn = document.querySelector('.mobile-menu-btn i');
    if (menu.classList.contains('show')) {
        btn.classList.remove('fa-bars');
        btn.classList.add('fa-times');
    } else {
        btn.classList.remove('fa-times');
        btn.classList.add('fa-bars');
    }
}

// Close mobile menu when clicking outside
document.addEventListener('click', function(event) {
    const menu = document.getElementById('mainMenu');
    const btn = document.querySelector('.mobile-menu-btn');
    
    if (!menu.contains(event.target) && !btn.contains(event.target) && menu.classList.contains('show')) {
        menu.classList.remove('show');
        const icon = btn.querySelector('i');
        icon.classList.remove('fa-times');
        icon.classList.add('fa-bars');
    }
});

// Close mobile menu when window resizes above mobile breakpoint
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) {
        const menu = document.getElementById('mainMenu');
        menu.classList.remove('show');
        const btn = document.querySelector('.mobile-menu-btn i');
        btn.classList.remove('fa-times');
        btn.classList.add('fa-bars');
    }
});

// Highlight active page in menu
document.addEventListener('DOMContentLoaded', function() {
    const currentPage = window.location.pathname.split('/').pop();
    const menuLinks = document.querySelectorAll('.menu a');
    
    menuLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPage) {
            link.classList.add('active');
        }
    });
});

// Smooth scroll to top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Add scroll-to-top button (optional)
window.addEventListener('scroll', function() {
    const scrollBtn = document.querySelector('.scroll-top');
    if (!scrollBtn) {
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
            display: ${window.scrollY > 300 ? 'flex' : 'none'};
            align-items: center;
            justify-content: center;
            box-shadow: var(--shadow);
            transition: all 0.3s ease;
            z-index: 999;
        `;
        document.body.appendChild(btn);
    } else {
        scrollBtn.style.display = window.scrollY > 300 ? 'flex' : 'none';
    }
});
</script>

<!-- Add Font Awesome if not already included -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<!-- Rest of your content goes here -->