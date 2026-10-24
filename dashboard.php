<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

$username = htmlspecialchars(
    $_SESSION['admin_username'],
    ENT_QUOTES,
    'UTF-8'
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TND Brothers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <h2>Admin Dashboard</h2>
        <p>Welcome, <?php echo $username; ?>!</p>
        <p>TND Brothers Portfolio System</p>

        <a href="logout.php">Logout</a>
    </div>
</body>
</html>
