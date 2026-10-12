<?php
// Database connection settings change these to match your MySQL setup.
$DB_HOST = "localhost";
$DB_USER = "root";
$DB_PASS = "";
$DB_NAME = "dr_system";
 
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
 
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
 
session_start();