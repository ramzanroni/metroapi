<?php
$servername = "eon_bazar";
$username = "metrosoft";
$password = "metrosoft";

// Create connection
$conn = new mysqli("localhost", "metrosoft", "metrosoft", "eon_bazar");
// $conn = new mysqli($servername, $username, $password);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
// echo "Connected successfully";
