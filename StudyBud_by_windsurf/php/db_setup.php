<?php
require_once 'db_connect.php';

/**
 * Database setup script
 * Creates password reset tokens table for the StudyBud application
 */

// Connect to the database
$conn = db_connect();

// Create password_reset_tokens table if it doesn't exist
$reset_tokens_table = "
CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `token` (`token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";



// Execute the query
if ($conn->query($reset_tokens_table) === TRUE) {
    echo "Password reset tokens table created successfully<br>";
} else {
    echo "Error creating password reset tokens table: " . $conn->error . "<br>";
}

// Close the connection
$conn->close();

echo "<p>Database setup completed!</p>";
echo "<p><a href='../login.php'>Go to Login Page</a></p>";
?>
