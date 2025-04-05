<?php
/**
 * StudyBud Dashboard Page
 * 
 * This is the main dashboard page that users see after logging in.
 * It displays personalized content, course recommendations, and learning progress.
 * 
 * @version 1.0
 * @author StudyBud Team
 */

// Include configuration
require_once __DIR__ . '/../../config/config.php';
require_once UTILS_PATH . '/session.php';

// Session is already started in config.php

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: ' . get_url('pages/auth/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Your personalized learning platform">
    <title>StudyBud - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="<?php echo CSS_URL; ?>/modern-style.css">
    <script src="<?php echo JS_URL; ?>/modern-script.js" defer></script>
</head>
<body>
    <!-- Mobile Navigation Backdrop -->
    <div class="backdrop"></div>
    
    <!-- Main Navigation -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="<?php echo get_url('pages/main/dashboard.php'); ?>">
                <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="StudyBud">
            </a>
        </div>
        
        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" aria-label="Toggle navigation">
            <i class="fas fa-bars"></i>
        </button>
        
        <!-- Navigation Links -->
        <div class="navbar-collapse">
            <!-- No close button needed -->
            
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a href="<?php echo get_url('pages/main/dashboard.php'); ?>" class="nav-link active" aria-current="page">
                        <i class="fas fa-home"></i> Home
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo get_url('pages/main/courses.php'); ?>" class="nav-link">
                        <i class="fas fa-graduation-cap"></i> Courses
                    </a>
                </li>
                <li class="nav-item">
                    <a href="<?php echo get_url('pages/main/library.php'); ?>" class="nav-link">
                        <i class="fas fa-book"></i> Library
                    </a>
                </li>
                <li class="nav-item">
                    <a href="profile.html" class="nav-link">
                        <i class="fas fa-user"></i> Profile
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- User Dropdown -->
        <div class="user-dropdown">
            <div class="user-dropdown-toggle" tabindex="0" aria-haspopup="true" aria-expanded="false">
                <img src="<?php echo IMAGES_URL; ?>/default-profile.png" alt="Profile" class="profile-pic" id="userProfilePic">
                <i class="fas fa-chevron-down"></i>
            </div>
            <div class="user-dropdown-menu" aria-label="User menu">
                <a href="profile.html" class="user-dropdown-item">
                    <i class="fas fa-user"></i> My Profile
                </a>
                <a href="settings.html" class="user-dropdown-item">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <div class="user-dropdown-divider"></div>
                <a href="logout.php" class="user-dropdown-item">
                    <i class="fas fa-sign-out-alt"></i> Logout
                </a>
            </div>
        </div>
    </nav>
    
    <!-- Breadcrumb Navigation -->
    <div class="container">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="<?php echo BASE_URL; ?>/index.php">StudyBud</a></li>
                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
            </ol>
        </nav>
    </div>

    <main class="container">
        <!-- Welcome Section -->
        <div class="welcome-card">
            <h1>Welcome to StudyBud, <span id="welcomeName"></span>!</h1>
            <p>Continue your learning journey today. Track your progress, explore new courses, and achieve your goals.</p>
        </div>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Courses Enrolled</h3>
                <div class="value" id="courses-enrolled">0</div>
            </div>
            <div class="stat-card">
                <h3>Hours Learned</h3>
                <div class="value" id="hours-learned">0</div>
            </div>
            <div class="stat-card">
                <h3>Certificates</h3>
                <div class="value" id="certificates">0</div>
            </div>
            <div class="stat-card">
                <h3>Completion Rate</h3>
                <div class="value" id="completion-rate">0%</div>
            </div>
        </div>

        <!-- Continue Watching Section -->
        <section aria-labelledby="continue-watching-heading">
            <h2 id="continue-watching-heading"><i class="fas fa-play-circle"></i> Continue Watching</h2>
            <div class="course-grid" id="continue-watching-container">
                <!-- Course cards will be loaded dynamically from the database -->
                <div id="no-courses-message" class="no-content-message">
                    <i class="fas fa-book-open fa-3x mb-3"></i>
                    <p>You haven't started any courses yet. Explore our course library to get started!</p>
                    <a href="<?php echo get_url('pages/main/courses.php'); ?>" class="btn btn-primary mt-3">Browse Courses</a>
                </div>
            </div>
        </section>

        <!-- Recommended Courses Section -->
        <section aria-labelledby="recommended-courses-heading">
            <h2 id="recommended-courses-heading"><i class="fas fa-star"></i> Recommended Courses</h2>
            <div class="course-grid" id="recommended-courses-container">
                <!-- Loading indicator will be replaced by JS -->
            </div>
        </section>
        
        <!-- Recently Added Section -->
        <section aria-labelledby="recently-added-heading">
            <h2 id="recently-added-heading"><i class="fas fa-clock"></i> Recently Added</h2>
            <div class="course-grid" id="recently-added-container">
                <!-- Will be populated by JavaScript -->
            </div>
        </section>
    </main>
    
    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col">
                    <h3>StudyBud</h3>
                    <p>Your personalized learning platform. Learn at your own pace, track your progress, and achieve your goals.</p>
                </div>
                <div class="col">
                    <h3>Quick Links</h3>
                    <ul class="footer-links">
                        <li><a href="<?php echo get_url('pages/main/dashboard.php'); ?>">Dashboard</a></li>
                        <li><a href="<?php echo get_url('pages/main/courses.php'); ?>">All Courses</a></li>
                        <li><a href="profile.html">My Profile</a></li>
                        <li><a href="help.html">Help & Support</a></li>
                    </ul>
                </div>
                <div class="col">
                    <h3>Contact Us</h3>
                    <ul class="footer-links">
                        <li><a href="mailto:support@studybud.com"><i class="fas fa-envelope"></i> support@studybud.com</a></li>
                        <li><a href="tel:+1234567890"><i class="fas fa-phone"></i> +123 456 7890</a></li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 StudyBud. All rights reserved.</p>
            </div>
        </div>
    </footer>
    <!-- The JavaScript functionality has been moved to modern-script.js -->
</body>
</html>
