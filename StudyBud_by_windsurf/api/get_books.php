<?php
header('Content-Type: application/json');
require_once '../config.php';

// Get query parameters
$category_id = isset($_GET['category_id']) ? $conn->real_escape_string($_GET['category_id']) : null;
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : null;

// Build the SQL query
$sql = "SELECT b.*, cat.name as category_name 
        FROM books b
        LEFT JOIN categories cat ON b.category_id = cat.id
        WHERE 1=1";

// Add filters if provided
if ($category_id) {
    $sql .= " AND b.category_id = $category_id";
}

if ($search) {
    $sql .= " AND (b.title LIKE '%$search%' OR b.author LIKE '%$search%' OR b.description LIKE '%$search%')";
}

$sql .= " ORDER BY b.created_at DESC";

$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $books = [];
    while($row = $result->fetch_assoc()) {
        $books[] = $row;
    }
    echo json_encode(["status" => "success", "data" => $books]);
} else {
    echo json_encode(["status" => "error", "message" => "No books found"]);
}

$conn->close();
?>
