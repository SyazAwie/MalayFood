<?php
$servername = "localhost";
$username = "root"; // Default for XAMPP
$password = "";     // Default password is empty
$dbname = "malay_traditional_food_heritage_system"; // Your correct database name

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
