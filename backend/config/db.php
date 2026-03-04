<?php
$servername = "localhost"; // XAMPP default
$username = "root";        // XAMPP default
$password = "";            // blank unless you set a password
$dbname = "nsbm_campus_ecommerce";
$port = 3307;              // <-- change this to match your XAMPP MySQL port

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
