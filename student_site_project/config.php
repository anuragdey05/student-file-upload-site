<?php
// config.php
define('DB_HOST', 'localhost');
define('DB_PORT', '5432'); // Default PostgreSQL port
define('DB_NAME', 'student_site_db');
define('DB_USER', 'student_site_admin');
define('DB_PASS', 'gtc4lusso');

// Base path for student file storage. Made sure the directory has read, write and execute permission.
// Using an absolute path is generally safer.
// Uncomment the following line to access path for different user
//define('STUDENT_UPLOADS_BASE_PATH', realpath($_SERVER['HOME'] . '/student_site_files')); // Base path defined
define('STUDENT_UPLOADS_BASE_PATH', realpath('/Users/sarahpeterpalal/student_site_files')); // this path is to local directory
define('MAX_STORAGE_BYTES', 5 * 1024 * 1024); // 5MB

session_start();

// Basic error reporting for development
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
?>
