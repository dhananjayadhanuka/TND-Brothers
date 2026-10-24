
<?php
session_start();
require_once 'db.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare(
        "SELECT id, password_hash FROM admins WHERE username = ?"
    );

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->bind_result($adminId, $passwordHash);

    $validLogin = $stmt->fetch()
        && password_verify($password, $passwordHash);

    $stmt->close();

    if ($validLogin) {
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $adminId;
        $_SESSION['admin_username'] = $username;

        header('Location: dashboard.php');
        exit;
    }

    $error = 'Invalid username or password.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">
    <title>Admin Login | TND Brothers</title>

    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="login-container">

        <h2>Admin Login</h2>
        <p>TND Brothers Portfolio System</p>

        <form action="admin.php" method="post">

                    
            <?php if ($error !== ''): ?>
                <p style="color: #b42318;">
                    <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
                </p>
            <?php endif; ?>


            <label for="username">Username</label>
            <input type="text"
                   id="username"
                   name="username"
                   placeholder="Enter Username"
                   required>

            <label for="password">Password</label>
            <input type="password"
                   id="password"
                   name="password"
                   placeholder="Enter Password"
                   required>

            <button type="submit">Login</button>

        </form>

        <a href="index.html">Back to Home</a>

    </div>

</body>
</html>
