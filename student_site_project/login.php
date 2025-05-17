<?php
require_once 'config.php';
require_once 'db.php';
require_once 'templates/header.php';

$message = '';

if (isset($_SESSION['student_id'])) {
    header("Location: dashboard.php"); // Already logged in
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $message = "Username and password are required.";
    } else {
        $pdo = get_db_connection();
        try {
            $stmt = $pdo->prepare("SELECT student_id, username, password_hash, assigned_directory_slug, current_storage_bytes FROM students WHERE username = ?");
            $stmt->execute([$username]);
            $student = $stmt->fetch();

            if ($student && password_verify($password, $student['password_hash'])) {
                $_SESSION['student_id'] = $student['student_id'];
                $_SESSION['username'] = $student['username'];
                $_SESSION['directory_slug'] = $student['assigned_directory_slug'];
                $_SESSION['current_storage_bytes'] = $student['current_storage_bytes'];
                header("Location: dashboard.php");
                exit;
            } else {
                $message = "Invalid username or password.";
            }
        } catch (PDOException $e) {
            $message = "Login failed: " . $e->getMessage();
        }
    }
}
?>

<h2>Login</h2>
<?php if ($message): ?>
    <p class="message"><?php echo $message; ?></p>
<?php endif; ?>
<form action="login.php" method="post">
    <div>
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required>
    </div>
    <div>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required>
    </div>
    <button type="submit">Login</button>
</form>

<?php require_once 'templates/footer.php'; ?>