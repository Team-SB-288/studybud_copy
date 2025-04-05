<?php
/**
 * StudyBud Main Page
 * Converted from HTML to PHP
 */

// Include configuration
require_once __DIR__ . '/../../config/config.php';
require_once UTILS_PATH . '/session.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ' . get_url('pages/auth/login.php'));
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Bud - Course Details</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            min-height: 100vh;
            background: #f5f7fa;
        }
        
        .navbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 30px;
            background: white;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        
        .logo img {
            height: 40px;
        }
        
        .nav-links {
            display: flex;
            gap: 30px;
        }
        
        .nav-links a {
            text-decoration: none;
            color: #333;
            font-weight: 500;
            transition: color 0.3s;
        }
        
        .nav-links a:hover, .nav-links a.active {
            color: #764ba2;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .profile-pic {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }
        
        .breadcrumbs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            color: #666;
            font-size: 14px;
        }
        
        .breadcrumbs a {
            color: #666;
            text-decoration: none;
        }
        
        .breadcrumbs a:hover {
            color: #764ba2;
        }
        
        .course-header {
            display: flex;
            gap: 30px;
            margin-bottom: 30px;
        }
        
        .course-image {
            width: 300px;
            height: 180px;
            border-radius: 10px;
            object-fit: cover;
        }
        
        .course-info {
            flex: 1;
        }
        
        .course-info h1 {
            color: #333;
            margin-bottom: 10px;
            font-size: 28px;
        }
        
        .course-info p {
            color: #666;
            margin-bottom: 20px;
            line-height: 1.6;
        }
        
        .course-meta {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 5px;
            color: #666;
            font-size: 14px;
        }
        
        .rating {
            color: #f9a825;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
        }
        
        .btn-primary {
            background: #764ba2;
            color: white;
            border: none;
        }
        
        .btn-primary:hover {
            background: #667eea;
        }
        
        .btn-outline {
            background: transparent;
            color: #764ba2;
            border: 1px solid #764ba2;
        }
        
        .btn-outline:hover {
            background: rgba(118, 75, 162, 0.1);
        }
        
        .course-content {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 30px;
        }
        
        .course-main {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .section-title {
            color: #333;
            margin-bottom: 20px;
            font-size: 20px;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
        }
        
        .course-description {
            margin-bottom: 30px;
            line-height: 1.6;
            color: #444;
        }
        
        .course-curriculum {
            margin-bottom: 30px;
        }
        
        .curriculum-section {
            margin-bottom: 20px;
        }
        
        .section-header {
            background: #f5f7fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background 0.3s;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-header:hover {
            background: #edf0f7;
        }
        
        .section-header h3 {
            font-size: 16px;
            color: #333;
        }
        
        .section-meta {
            font-size: 14px;
            color: #666;
        }
        
        .video-list {
            padding-left: 15px;
        }
        
        .video-item {
            display: flex;
            justify-content: space-between;
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
            transition: background 0.3s;
        }
        
        .video-item:last-child {
            border-bottom: none;
        }
        
        .video-item:hover {
            background: #f9f9f9;
        }
        
        .video-title {
            display: flex;
            align-items: center;
            gap: 10px;
            color: #333;
            text-decoration: none;
        }
        
        .video-title:hover {
            color: #764ba2;
        }
        
        .video-icon {
            color: #764ba2;
        }
        
        .video-duration {
            font-size: 14px;
            color: #666;
        }
        
        .course-sidebar {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            height: fit-content;
        }
        
        .instructor {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .instructor-image {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
        }
        
        .instructor-info h3 {
            font-size: 16px;
            color: #333;
            margin-bottom: 5px;
        }
        
        .instructor-info p {
            font-size: 14px;
            color: #666;
        }
        
        .course-stats {
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .stat-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 14px;
        }
        
        .stat-label {
            color: #666;
        }
        
        .stat-value {
            color: #333;
            font-weight: 500;
        }
        
        .share-course {
            margin-top: 20px;
        }
        
        .share-buttons {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        
        .share-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            text-decoration: none;
            transition: transform 0.3s;
        }
        
        .share-btn:hover {
            transform: translateY(-3px);
        }
        
        .facebook {
            background: #3b5998;
        }
        
        .twitter {
            background: #1da1f2;
        }
        
        .linkedin {
            background: #0077b5;
        }
        
        .whatsapp {
            background: #25d366;
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="logo">
            <img src="<?php echo IMAGES_URL; ?>/logo.png" alt="Study Bud">
        </div>
        <div class="nav-links">
            <a href="<?php echo get_url('pages/main/dashboard.php'); ?>">Home</a>
            <a href="<?php echo get_url('pages/main/courses.php'); ?>" class="active">Courses</a>
            <a href="<?php echo get_url('pages/main/library.php'); ?>">Library</a>
            <a href="profile.html">Profile</a>
        </div>
        <div class="user-info">
            <img src="https://via.placeholder.com/40" alt="Profile" class="profile-pic">
            <span>John Doe</span>
        </div>
    </nav>

    <main class="container">
        <div class="breadcrumbs">
            <a href="<?php echo get_url('pages/main/dashboard.php'); ?>">Home</a> &gt;
            <a href="<?php echo get_url('pages/main/courses.php'); ?>">Courses</a> &gt;
            <span>Web Development</span>
        </div>

        <div class="course-header">
            <img src="https://via.placeholder.com/300x180" alt="Course" class="course-image">
            <div class="course-info">
                <h1>Introduction to Web Development</h1>
                <p>Learn the fundamentals of web development, including HTML, CSS, and JavaScript to build modern, responsive websites from scratch.</p>
                <div class="course-meta">
                    <div class="meta-item">
                        <span>12 videos</span>
                    </div>
                    <div class="meta-item">
                        <span>8 hours total</span>
                    </div>
                    <div class="meta-item">
                        <span class="rating">4.8 ★★★★★</span>
                        <span>(256 reviews)</span>
                    </div>
                </div>
                <div class="action-buttons">
                    <a href="<?php echo get_url('pages/main/video.php'); ?>" class="btn btn-primary">Start Learning</a>
                    <button class="btn btn-outline">Save Course</button>
                </div>
            </div>
        </div>

        <div class="course-content">
            <div class="course-main">
                <h2 class="section-title">About This Course</h2>
                <div class="course-description">
                    <p>This comprehensive course is designed for beginners who want to learn web development from the ground up. You'll start with the basics of HTML to structure web content, move on to CSS for styling, and finish with JavaScript for adding interactivity to your websites.</p>
                    <p>By the end of this course, you'll have the skills to create modern, responsive websites and a solid foundation for further learning in web development frameworks and libraries.</p>
                </div>

                <h2 class="section-title">Course Curriculum</h2>
                <div class="course-curriculum">
                    <div class="curriculum-section">
                        <div class="section-header">
                            <h3>Section 1: Introduction to HTML</h3>
                            <div class="section-meta">4 videos • 1.5 hours</div>
                        </div>
                        <div class="video-list">
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>1.1 What is HTML?</span>
                                </a>
                                <span class="video-duration">15:30</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>1.2 HTML Document Structure</span>
                                </a>
                                <span class="video-duration">22:45</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>1.3 HTML Elements and Attributes</span>
                                </a>
                                <span class="video-duration">28:15</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>1.4 HTML Forms and Input Elements</span>
                                </a>
                                <span class="video-duration">24:20</span>
                            </div>
                        </div>
                    </div>

                    <div class="curriculum-section">
                        <div class="section-header">
                            <h3>Section 2: CSS Fundamentals</h3>
                            <div class="section-meta">4 videos • 2 hours</div>
                        </div>
                        <div class="video-list">
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>2.1 Introduction to CSS</span>
                                </a>
                                <span class="video-duration">18:45</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>2.2 CSS Selectors and Properties</span>
                                </a>
                                <span class="video-duration">32:10</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>2.3 CSS Box Model and Layout</span>
                                </a>
                                <span class="video-duration">35:20</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>2.4 Responsive Design with CSS</span>
                                </a>
                                <span class="video-duration">33:45</span>
                            </div>
                        </div>
                    </div>

                    <div class="curriculum-section">
                        <div class="section-header">
                            <h3>Section 3: JavaScript Basics</h3>
                            <div class="section-meta">4 videos • 2.5 hours</div>
                        </div>
                        <div class="video-list">
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>3.1 Introduction to JavaScript</span>
                                </a>
                                <span class="video-duration">25:15</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>3.2 JavaScript Variables and Data Types</span>
                                </a>
                                <span class="video-duration">30:40</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>3.3 JavaScript Functions and Events</span>
                                </a>
                                <span class="video-duration">42:20</span>
                            </div>
                            <div class="video-item">
                                <a href="<?php echo get_url('pages/main/video.php'); ?>" class="video-title">
                                    <span class="video-icon">▶</span>
                                    <span>3.4 DOM Manipulation with JavaScript</span>
                                </a>
                                <span class="video-duration">38:15</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="course-sidebar">
                <div class="instructor">
                    <img src="https://via.placeholder.com/60" alt="Instructor" class="instructor-image">
                    <div class="instructor-info">
                        <h3>Sarah Johnson</h3>
                        <p>Web Development Instructor</p>
                    </div>
                </div>

                <div class="course-stats">
                    <div class="stat-item">
                        <span class="stat-label">Total Videos</span>
                        <span class="stat-value">12</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Total Duration</span>
                        <span class="stat-value">8 hours</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Skill Level</span>
                        <span class="stat-value">Beginner</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Students Enrolled</span>
                        <span class="stat-value">1,245</span>
                    </div>
                    <div class="stat-item">
                        <span class="stat-label">Last Updated</span>
                        <span class="stat-value">March 2025</span>
                    </div>
                </div>

                <div class="share-course">
                    <h3>Share This Course</h3>
                    <div class="share-buttons">
                        <a href="#" class="share-btn facebook">f</a>
                        <a href="#" class="share-btn twitter">t</a>
                        <a href="#" class="share-btn linkedin">in</a>
                        <a href="#" class="share-btn whatsapp">w</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Toggle curriculum sections
        document.querySelectorAll('.section-header').forEach(header => {
            header.addEventListener('click', function() {
                const videoList = this.nextElementSibling;
                videoList.style.display = videoList.style.display === 'none' ? 'block' : 'none';
            });
        });
    </script>
</body>
</html>
