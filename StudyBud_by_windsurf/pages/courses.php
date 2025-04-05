<?php
/**
 * StudyBud Courses Page
 */

// Include configuration and utilities
require_once __DIR__ . '/../../config.php';
require_once __DIR__ . '/../../includes/session.php';

// Check if user is logged in
if (!is_logged_in()) {
    header('Location: ' . get_url('pages/auth/login.php'));
    exit;
}

// Get database connection
$conn = db_connect();

// Get all courses with category information
$sql = "SELECT c.*, ca.name as category_name, 
        COUNT(v.id) as video_count 
        FROM courses c 
        LEFT JOIN videos v ON c.id = v.course_id 
        LEFT JOIN categories ca ON c.category_id = ca.id
        GROUP BY c.id 
        ORDER BY c.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="StudyBud - Explore our course catalog">
    <title>Courses - StudyBud</title>
    <link rel="stylesheet" href="../../assets/css/modern-style.css">
    <link rel="stylesheet" href="../../assets/css/courses.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="../../assets/js/modern-script.js" defer></script>
    <script src="../../assets/js/courses.js" defer></script>
</head>
<body>
    <?php include __DIR__ . '/../../includes/header.php'; ?>

    <main class="container">
        <section class="courses-header">
            <h1>Available Courses</h1>
            <div class="search-filter">
                <div class="search-bar">
                    <input type="text" id="searchInput" placeholder="Search courses...">
                    <i class="fas fa-search"></i>
                </div>
                <div class="filter-dropdown">
                    <button class="filter-btn">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                    <div class="filter-options">
                        <div class="filter-option">
                            <label>Category:</label>
                            <select id="categoryFilter">
                                <option value="">All Categories</option>
                                <?php
                                $sql = "SELECT id, name FROM categories ORDER BY name";
                                $categories = $conn->query($sql);
                                while ($category = $categories->fetch_assoc()) {
                                    echo "<option value='" . $category['id'] . "'>" . htmlspecialchars($category['name']) . "</option>";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="filter-option">
                            <label>Difficulty:</label>
                            <select id="difficultyFilter">
                                <option value="">All Levels</option>
                                <option value="beginner">Beginner</option>
                                <option value="intermediate">Intermediate</option>
                                <option value="advanced">Advanced</option>
                            </select>
                        </div>
                        <div class="filter-option">
                            <label>Price:</label>
                            <select id="priceFilter">
                                <option value="">All Prices</option>
                                <option value="free">Free</option>
                                <option value="paid">Paid</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <div class="courses-grid" id="coursesGrid">
            <?php while ($course = $result->fetch_assoc()): ?>
            <div class="course-card">
                <img src="<?php echo htmlspecialchars($course['thumbnail'] ?? '../../assets/images/default-course.png'); ?>" alt="<?php echo htmlspecialchars($course['title']); ?>">
                <div class="course-info">
                    <h3><?php echo htmlspecialchars($course['title']); ?></h3>
                    <p><?php echo htmlspecialchars($course['description']); ?></p>
                    <div class="course-stats">
                        <span><i class="fas fa-video"></i> <?php echo $course['video_count']; ?> Videos</span>
                        <span><i class="fas fa-clock"></i> <?php echo floor($course['video_count'] * 10 / 60); ?>h <?php echo ($course['video_count'] * 10) % 60; ?>m</span>
                    </div>
                    <div class="course-meta">
                        <span class="category"><?php echo htmlspecialchars($course['category_name'] ?? 'Uncategorized'); ?></span>
                        <span class="difficulty"><?php echo htmlspecialchars($course['difficulty'] ?? 'All Levels'); ?></span>
                        <span class="price"><?php echo $course['price'] ? '$' . number_format($course['price'], 2) : 'Free'; ?></span>
                    </div>
                    <a href="course.php?id=<?php echo $course['id']; ?>" class="enroll-button">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <?php
                            $sql = "SELECT id FROM user_course_enrollment WHERE user_id = ? AND course_id = ?";
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("ii", $_SESSION['user_id'], $course['id']);
                            $stmt->execute();
                            $enrolled = $stmt->get_result()->num_rows > 0;
                            ?>
                            <?php if ($enrolled): ?>
                                Continue Learning
                            <?php else: ?>
                                Enroll Now
                            <?php endif; ?>
                        <?php else: ?>
                            Enroll Now
                        <?php endif; ?>
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
    </main>

    <?php include __DIR__ . '/../../includes/footer.php'; ?>
</body>
</html>
