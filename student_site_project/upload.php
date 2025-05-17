<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';
$student_id = $_SESSION['student_id'];
$student_dir_slug = $_SESSION['directory_slug'];
$student_physical_path = STUDENT_UPLOADS_BASE_PATH . '/' . $student_dir_slug;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['fileToUpload'])) {
    $file = $_FILES['fileToUpload'];

    if ($file['error'] === UPLOAD_ERR_OK) {
        $file_size = $file['size'];
        $file_name = basename($file['name']); // Sanitize filename
        $target_file_path = $student_physical_path . '/' . $file_name;

        // Check for malicious filenames (e.g., trying to go up directories)
        if ($file_name !== $file['name'] || strpos($file_name, '..') !== false) {
             $message = "Invalid filename.";
        } elseif (empty($file_name)) {
            $message = "Filename cannot be empty.";
        }
        else {
            $pdo = get_db_connection();
            try {
                // Get current storage (lock row for update if possible, or rely on transaction)
                // For simplicity, we'll just fetch and update. Concurrent uploads could be an issue.
                $stmt = $pdo->prepare("SELECT current_storage_bytes FROM students WHERE student_id = ?");
                $stmt->execute([$student_id]);
                $student_data = $stmt->fetch();
                $current_storage_bytes = $student_data['current_storage_bytes'];

                if (($current_storage_bytes + $file_size) > MAX_STORAGE_BYTES) {
                    $message = "Upload failed: Exceeds your " . (MAX_STORAGE_BYTES / (1024*1024)) . "MB storage limit.";
                } elseif (file_exists($target_file_path)) {
                    // Simple check, could offer overwrite option or rename
                    $message = "File already exists. Please delete the existing file or rename your upload.";
                }
                else {
                    if (move_uploaded_file($file['tmp_name'], $target_file_path)) {
                        // Update storage used in DB
                        $new_storage_bytes = $current_storage_bytes + $file_size;
                        $update_stmt = $pdo->prepare("UPDATE students SET current_storage_bytes = ? WHERE student_id = ?");
                        $update_stmt->execute([$new_storage_bytes, $student_id]);
                        $_SESSION['current_storage_bytes'] = $new_storage_bytes; // Update session
                        $message = "File '". htmlspecialchars($file_name) ."' uploaded successfully.";
                    } else {
                        $message = "Sorry, there was an error uploading your file to the server.";
                    }
                }
            } catch (PDOException $e) {
                $message = "Database error: " . $e->getMessage();
            } catch (Exception $e) {
                $message = "Error: " . $e->getMessage();
            }
        }
    } elseif ($file['error'] === UPLOAD_ERR_INI_SIZE || $file['error'] === UPLOAD_ERR_FORM_SIZE) {
        $message = "File is too large (exceeds server or form limit).";
    } elseif ($file['error'] === UPLOAD_ERR_NO_FILE) {
        $message = "No file was selected for upload.";
    }else {
        $message = "Unknown upload error. Code: " . $file['error'];
    }
}

// Store message in session and redirect back to dashboard to show it
$_SESSION['upload_message'] = $message;
header("Location: dashboard.php");
exit;
?>