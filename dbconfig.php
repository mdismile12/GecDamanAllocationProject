<?php
// Replace these with your actual DB credentials
$servername = "localhost";  // e.g. sqlXXX.epizy.com
$username = "root";    // e.g. epiz_12345678
$password = "";    // your DB password
$dbname = "desem5";       // e.g. epiz_12345678_college_seats

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>