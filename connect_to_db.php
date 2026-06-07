<?php 

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "digimath_db";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
  die("Connection failed: " . $conn->connect_error);
}
echo '<script>console.log("Connected successfully to DB");</script>';
?>

<!-- $conn->close(); to close the connection -->