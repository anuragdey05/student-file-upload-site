<?php
require_once 'config.php';
require_once 'db.php'; // For potential future DB calls on dashboard

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$upload_message = '';
if (isset($_SESSION['upload_message'])) {
    $upload_message = $_SESSION['upload_message'];
    unset($_SESSION['upload_message']); // Clear message after displaying once
}

require_once 'templates/header.php';

$student_dir_slug = $_SESSION['directory_slug'];
$student_physical_path = STUDENT_UPLOADS_BASE_PATH . '/' . $student_dir_slug;
$current_storage_mb = number_format($_SESSION['current_storage_bytes'] / (1024 * 1024), 2);
$max_storage_mb = number_format(MAX_STORAGE_BYTES / (1024 * 1024), 2);
?>

<h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
<p>Your Directory: <?php echo htmlspecialchars($student_dir_slug); ?></p>
<p>Storage Used: <?php echo $current_storage_mb; ?> MB / <?php echo $max_storage_mb; ?> MB</p>

<h3>Your Files:</h3>
<?php
if (!is_dir($student_physical_path)) {
    echo "<p>Your directory does not exist. Please contact an administrator.</p>";
} else {
    $files = array_diff(scandir($student_physical_path), array('.', '..')); // Exclude . and ..
    if (empty($files)) {
        echo "<p>You have no files uploaded yet.</p>";
    } else {
        echo "<ul>";
        foreach ($files as $file) {
            $file_path_for_url = rawurlencode($student_dir_slug) . '/' . rawurlencode($file);
            // This URL assumes Apache is configured to serve/execute files from this path
            // We will configure an Alias in Apache for this
            echo "<li><a href='/student_files/" . htmlspecialchars($file_path_for_url, ENT_QUOTES, 'UTF-8') . "' target='_blank'>" . htmlspecialchars($file) . "</a></li>";
        }
        echo "</ul>";
    }
}

if ($upload_message):
?>
    <p class="message"><?php echo $upload_message; ?></p>
<?php
endif;
?>

<h3>Upload File:</h3>
<form action="upload.php" method="post" enctype="multipart/form-data">
    <div>
        <label for="fileToUpload">Select file to upload:</label>
        <input type="file" name="fileToUpload" id="fileToUpload" required>
    </div>
    <button type="submit">Upload File</button>
</form>

<?php require_once 'templates/footer.php'; ?>