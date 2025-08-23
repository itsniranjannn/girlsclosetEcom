<?php
// db_connection.php
$host = "localhost";
$user = "root";
$password = ""; // XAMPP default password is empty
$database = "girls_clothing_db";

// Create connection
$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
