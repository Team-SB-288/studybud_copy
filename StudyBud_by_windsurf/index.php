<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');  
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$sql = "SELECT name, profile_picture FROM users WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Get recent courses
$sql = "SELECT c.*, COUNT(v.id) as video_count 
        FROM courses c 
        LEFT JOIN videos v ON c.id = v.course_id 
        GROUP BY c.id 
        ORDER BY c.created_at DESC 
        LIMIT 6";
$stmt = $conn->prepare($sql);
$stmt->execute();
$courses_result = $stmt->get_result();

// Get user's enrolled courses
$sql = "SELECT c.*, COUNT(v.id) as video_count 
        FROM courses c 
        JOIN user_course_enrollment uce ON c.id = uce.course_id 
        LEFT JOIN videos v ON c.id = v.course_id 
        WHERE uce.user_id = ?
        GROUP BY c.id 
        ORDER BY c.created_at DESC 
        LIMIT 3";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Study Bud - Your Learning Platform</title>
    <link rel="stylesheet" href="assets/css/modern-style.css">
    <link rel="stylesheet" href="assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="assets/js/modern-script.js" defer></script>
    <script src="assets/js/index.js" defer></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="logo">
            <a href="index.php">
                <img src="assets/images/logo.png" alt="Study Bud">
            </a>
        </div>
        <div class="nav-links">
            <a href="index.php" class="active">Home</a>
            <a href="pages/courses.php">Courses</a>
            <a href="pages/library.php">Library</a>
            <a href="pages/profile.php">Profile</a>
        </div>
        <div class="user-info">
            <div class="notifications">
                <i class="fas fa-bell"></i>
                <span class="notification-count">3</span>
            </div>
            <div class="profile-dropdown">
                <img src="<?php echo htmlspecialchars($user['profile_picture'] ?? 'assets/images/default-profile.png'); ?>" alt="Profile" class="profile-pic">
                <span><?php echo htmlspecialchars($user['name']); ?></span>
                <div class="dropdown-content">
                    <a href="pages/profile.php"><i class="fas fa-user"></i> Profile</a>
                    <a href="pages/settings.php"><i class="fas fa-cog"></i> Settings</a>
                    <a href="pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="container">
        <!-- Welcome Section -->
        <section class="welcome-section">
            <div class="welcome-content">
                <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
                <p>Continue your learning journey with our premium courses and resources.</p>
                <a href="pages/courses.php" class="cta-button">Explore Courses</a>
            </div>
        </section>

        <!-- Featured Courses -->
        <section class="featured-courses">
            <h2>Featured Courses</h2>
            <div class="course-grid">
                <?php while ($course = $courses_result->fetch_assoc()): ?>
                <div class="course-card">
                    <img src="<?php echo htmlspecialchars($course['thumbnail'] ?? 'assets/images/default-course.png'); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                    <div class="course-info">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <p><?php echo htmlspecialchars($course['description']); ?></p>
                        <div class="course-stats">
                            <span><i class="fas fa-video"></i> <?php echo $course['video_count']; ?> Videos</span>
                            <span><i class="fas fa-clock"></i> <?php echo floor($course['video_count'] * 10 / 60); ?>h <?php echo ($course['video_count'] * 10) % 60; ?>m</span>
                        </div>
                        <a href="pages/course.php?id=<?php echo $course['id']; ?>" class="enroll-button">Enroll Now</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>

        <!-- Enrolled Courses -->
        <section class="enrolled-courses">
            <h2>Your Enrolled Courses</h2>
            <div class="course-grid">
                <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                <div class="course-card enrolled">
                    <img src="<?php echo htmlspecialchars($course['thumbnail'] ?? 'assets/images/default-course.png'); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>" class="course-image">
                    <div class="course-info">
                        <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                        <div class="course-progress">
                            <div class="progress-bar">
                                <div class="progress-fill" style="width: <?php echo $course['completion_percentage']; ?>%"></div>
                            </div>
                            <span><?php echo $course['completion_percentage']; ?>% Complete</span>
                        </div>
                        <a href="pages/course.php?id=<?php echo $course['id']; ?>" class="continue-button">Continue Learning</a>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer>
        <div class="footer-content">
            <div class="footer-section">
                <h3>About StudyBud</h3>
                <p>Your premier learning platform for modern education.</p>
            </div>
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="pages/courses.php">Courses</a></li>
                    <li><a href="pages/library.php">Library</a></li>
                    <li><a href="pages/faq.php">FAQ</a></li>
                    <li><a href="pages/contact.php">Contact</a></li>
                </ul>
            </div>
            <div class="footer-section">
                <h3>Follow Us</h3>
                <div class="social-links">
                    <a href="#"><i class="fab fa-facebook"></i></a>
                    <a href="#"><i class="fab fa-twitter"></i></a>
                    <a href="#"><i class="fab fa-instagram"></i></a>
                    <a href="#"><i class="fab fa-linkedin"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> StudyBud. All rights reserved.</p>
        </div>
    </footer>
</body>
</html>
