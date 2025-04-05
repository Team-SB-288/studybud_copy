<?php
/**
 * Database connection function
 * Returns a mysqli connection object
 */
function db_connect() {
    $host = "localhost";
    $username = "root";
    $password = "";
    $database = "studybud_windsurf";
    
    // Create connection
    $conn = new mysqli($host, $username, $password, $database);
    
    // Check connection
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}
?>
