<?php

$host     = "127.0.0.1";
$username = "root";
$password = "";
$database = "studenthub_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    error_log("MySQLi Connection Error: " . $conn->connect_error);
    die("<div class='alert alert-danger'>Database connection failed. Please try again later.</div>");
}

$conn->set_charset("utf8mb4");
?>
