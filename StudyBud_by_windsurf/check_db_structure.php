<?php
require_once 'config.php';

// Check users table structure
echo "<h2>Users Table Structure:</h2>";
$result = $conn->query("DESCRIBE users");
echo "<table border='1'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>";
    echo "<td>" . $row['Field'] . "</td>";
    echo "<td>" . $row['Type'] . "</td>";
    echo "<td>" . $row['Null'] . "</td>";
    echo "<td>" . $row['Key'] . "</td>";
    echo "<td>" . ($row['Default'] === NULL ? 'NULL' : $row['Default']) . "</td>";
    echo "<td>" . $row['Extra'] . "</td>";
    echo "</tr>";
}
echo "</table>";

// Add missing columns if they don't exist
echo "<h2>Adding Missing Columns:</h2>";

// Check and add password_hash column
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'password_hash'");
if ($result->num_rows == 0) {
    echo "<p>Adding password_hash column...</p>";
    $sql = "ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NOT NULL";
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>Password_hash column added successfully.</p>";
    } else {
        echo "<p style='color:red;'>Error adding password_hash column: " . $conn->error . "</p>";
    }
}

// Check and add profile_picture column
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_picture'");
if ($result->num_rows == 0) {
    echo "<p>Adding profile_picture column...</p>";
    $sql = "ALTER TABLE users ADD COLUMN profile_picture VARCHAR(255)";
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>Profile_picture column added successfully.</p>";
    } else {
        echo "<p style='color:red;'>Error adding profile_picture column: " . $conn->error . "</p>";
    }
}

// Check and add bio column
$result = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
if ($result->num_rows == 0) {
    echo "<p>Adding bio column...</p>";
    $sql = "ALTER TABLE users ADD COLUMN bio TEXT";
    if ($conn->query($sql)) {
        echo "<p style='color:green;'>Bio column added successfully.</p>";
    } else {
        echo "<p style='color:red;'>Error adding bio column: " . $conn->error . "</p>";
    }
}

$conn->close();
?>
