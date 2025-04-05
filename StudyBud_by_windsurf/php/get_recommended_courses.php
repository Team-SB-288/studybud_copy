<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'User not logged in'
    ]);
    exit;
}

$user_id = $_SESSION['user_id'];

// Get recommended courses using prepared statement
try {
    // First get categories of courses the user is already enrolled in
    $stmt = $conn->prepare("SELECT DISTINCT c.category_id
                           FROM user_course_enrollment uce
                           JOIN courses c ON uce.course_id = c.id
                           WHERE uce.user_id = ? AND c.category_id IS NOT NULL");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $user_categories = [];
    while ($row = $result->fetch_assoc()) {
        if ($row['category_id']) {
            $user_categories[] = $row['category_id'];
        }
    }
    
    // Get courses the user is already enrolled in
    $stmt = $conn->prepare("SELECT course_id FROM user_course_enrollment WHERE user_id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $enrolled_courses = [];
    while ($row = $result->fetch_assoc()) {
        $enrolled_courses[] = $row['course_id'];
    }
    
    // Build query for recommended courses
    if (!empty($user_categories) && !empty($enrolled_courses)) {
        // Recommend courses from same categories but not enrolled in
        $placeholders = str_repeat('?,', count($enrolled_courses) - 1) . '?';
        $category_placeholders = str_repeat('?,', count($user_categories) - 1) . '?';
        
        $sql = "SELECT 
                c.id, 
                c.title, 
                c.description, 
                c.thumbnail as image,
                (SELECT COUNT(*) FROM videos WHERE course_id = c.id) as video_count
            FROM courses c
            WHERE c.category_id IN ($category_placeholders) 
            AND c.id NOT IN ($placeholders)
            ORDER BY c.created_at DESC
            LIMIT 3";
        
        $stmt = $conn->prepare($sql);
        
        // Bind all parameters
        $param_types = str_repeat('i', count($user_categories) + count($enrolled_courses));
        $params = array_merge($user_categories, $enrolled_courses);
        
        // Create dynamic binding
        $bind_params = array($param_types);
        foreach ($params as $key => $value) {
            $bind_params[] = &$params[$key];
        }
        
        call_user_func_array(array($stmt, 'bind_param'), $bind_params);
    } else {
        // If no categories or enrolled courses, just get the newest courses
        $sql = "SELECT 
                c.id, 
                c.title, 
                c.description, 
                c.thumbnail as image,
                (SELECT COUNT(*) FROM videos WHERE course_id = c.id) as video_count
            FROM courses c
            ORDER BY c.created_at DESC
            LIMIT 3";
        
        $stmt = $conn->prepare($sql);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    $courses = [];
    while ($row = $result->fetch_assoc()) {
        // Sanitize output data
        $courses[] = [
            'id' => $row['id'],
            'title' => htmlspecialchars($row['title']),
            'description' => htmlspecialchars($row['description']),
            'image' => htmlspecialchars($row['image'] ?: 'images/course-default.jpg'),
            'video_count' => (int)$row['video_count']
        ];
    }
    
    echo json_encode([
        'success' => true,
        'courses' => $courses
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error retrieving recommended courses: ' . $e->getMessage()
    ]);
}

$conn->close();
?>
