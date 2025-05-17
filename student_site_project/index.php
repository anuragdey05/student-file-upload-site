<?php
// index.php
// Ensuring config.php is loaded to start the session and access session variables.

if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
} else {
    // A basic fallback or error if config.php isn't found.
    session_start(); // Attempt to start session if config.php didn't load it.
    error_log("Warning: config.php not found by index.php. Session might not behave as expected.");
}


// Check if the student is logged in
if (isset($_SESSION['student_id'])) {
    // User is logged in, redirect to the dashboard
    header("Location: dashboard.php");
    exit; // to prevent further script execution
} else {
    // User is not logged in, redirect to the login page
    header("Location: login.php");
    exit;
}

?>