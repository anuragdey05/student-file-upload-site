<?php
require_once 'config.php'; // For STUDENT_UPLOADS_BASE_PATH, session, etc.
// db.php might not be needed here unless you validate the slug against the DB

if (!isset($_GET['slug']) || !isset($_GET['script'])) {
    http_response_code(400);
    die("Bad request: Missing slug or script parameter.");
}

$student_slug = basename($_GET['slug']); // Sanitize
$script_name = basename($_GET['script']);   // Sanitize

// Validate slug and script name characters (important!)
if (!preg_match('/^[a-zA-Z0-9_-]+$/', $student_slug) || !preg_match('/^[a-zA-Z0-9_.-]+\.php$/', $script_name)) {
    http_response_code(400);
    die("Invalid slug or script name format.");
}

$student_script_path_relative_to_base = $student_slug . '/' . $script_name;
$student_script_full_path = STUDENT_UPLOADS_BASE_PATH . '/' . $student_script_path_relative_to_base;
$student_own_directory_full_path = STUDENT_UPLOADS_BASE_PATH . '/' . $student_slug;

if (!file_exists($student_script_full_path)) {
    http_response_code(404);
    die("Script not found: " . htmlspecialchars($student_script_path_relative_to_base));
}

// Define allowed paths for this specific script's execution
$allowed_paths = [
    $student_own_directory_full_path, // The student's own directory
    session_save_path(),             // Session directory
    sys_get_temp_dir(),              // System temp directory
    // You might need to add other essential paths if student scripts require them
    // e.g., PHP's include_path directories if they use PEAR without bundling
];
$open_basedir_value = implode(PATH_SEPARATOR, array_unique(array_filter($allowed_paths)));

if (_set('open_basedir', $open_basedir_value) === false) {
    // This should ideally not happen if no higher-level open_basedir is conflicting too much
    http_response_code(500);
    error_log("Failed to set open_basedir for: " . $student_script_full_path . " to " . $open_basedir_value);
    die("Server configuration error: Could not apply security restrictions.");
}

// To make relative includes/requires within the student's script work as expected from its own directory
chdir($student_own_directory_full_path);

// Execute the student's script
// All file operations inside the included script will now be subject to the open_basedir we just set.
include $student_script_full_path; // or require

?>