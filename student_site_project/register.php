<?php
require_once 'config.php';
require_once 'db.php';
require_once 'templates/header.php';

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($username) || empty($email) || empty($password)) {
        $message = "All fields are required.";
    } elseif ($password !== $confirm_password) {
        $message = "Passwords do not match.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email format.";
    } elseif (strlen($password) < 6) {
        $message = "Password must be at least 6 characters long.";
    } else {
        // Sanitize username to be used as a directory slug
        $directory_slug = preg_replace('/[^a-zA-Z0-9_-]/', '', $username);
        if (empty($directory_slug)) {
            $message = "Username contains invalid characters for directory creation.";
        } else {
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            $pdo = get_db_connection();

            try {
                // Check if username or email or slug already exists
                $stmt = $pdo->prepare("SELECT student_id FROM students WHERE username = ? OR email = ? OR assigned_directory_slug = ?");
                $stmt->execute([$username, $email, $directory_slug]);
                if ($stmt->fetch()) {
                    $message = "Username, email, or directory slug already taken.";
                } else {
                    // Create student directory
                    $student_dir_path = STUDENT_UPLOADS_BASE_PATH . '/' . $directory_slug;
                    if (!is_dir($student_dir_path)) {
                        if (!mkdir($student_dir_path, 0755, true)) { // 0755 for owner rwx, group rx, other rx
                            throw new Exception("Failed to create student directory. Check permissions for " . STUDENT_UPLOADS_BASE_PATH);
                        }
                    }

                    $stmt = $pdo->prepare("INSERT INTO students (username, email, password_hash, assigned_directory_slug) VALUES (?, ?, ?, ?)");
                    $stmt->execute([$username, $email, $password_hash, $directory_slug]);
                    $message = "Registration successful! You can now <a href='login.php'>login</a>.";
                    // Prevent form resubmission
                    $_POST = array();
                    $username = $email = '';
                }
            } catch (Exception $e) {
                $message = "Registration failed: " . $e->getMessage();
                 // Consider removing directory if DB insert fails and dir was created in this attempt
            }
        }
    }
}
?>

<h2>Register</h2>
<?php if ($message): ?>
    <p class="message"><?php echo $message; ?></p>
<?php endif; ?>
<form action="register.php" method="post">
    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username ?? ''); ?>" required>
        <small> (Used for your directory name. Only A-Z, a-z, 0-9, _, - allowed)</small>
    </div>
    <div>
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <div>
        <label for="confirm_password">Confirm Password:</label>
        <input type="password" id="confirm_password" name="confirm_password" required>
    </div>
    <button type="submit">Register</button>
</form>

<?php require_once 'templates/footer.php'; ?>