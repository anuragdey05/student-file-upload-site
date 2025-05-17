<?php require_once __DIR__ . '/../config.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Portal</title>
    <link rel="stylesheet" href="css/style.css"> </head>
<body>
    <header>
        <h1>Student Portal</h1>
        <nav>
            <?php if (isset($_SESSION['student_id'])): ?>
                <a href="dashboard.php">Dashboard</a>
                <a href="logout.php">Logout (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>