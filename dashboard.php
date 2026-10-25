<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: admin.php');
    exit;
}

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

require_once "db.php";

$result = $conn->query(
    "SELECT id, title, description, project_type, image_path
     FROM projects
     ORDER BY created_at DESC, id DESC"
);

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

        <a href="add_project.php" class="add-project-btn">
         + Add New Project
         </a>
        <a href="logout.php">Logout</a>

                <h3>Uploaded Projects</h3>

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($project = $result->fetch_assoc()): ?>

                <div class="project-card">

                    <img
                        src="<?php echo htmlspecialchars($project['image_path'], ENT_QUOTES, 'UTF-8'); ?>"
                        alt="Project Image"
                    >

                    <h3>
                        <?php echo htmlspecialchars($project['title'], ENT_QUOTES, 'UTF-8'); ?>
                    </h3>

                    <p>
                        <?php echo htmlspecialchars($project['project_type'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>

                    
                    <p class="project-description">
                    <?php echo nl2br(htmlspecialchars(
                        $project['description'] ?? '',
                        ENT_QUOTES,
                        'UTF-8'
                      )); ?>
                    </p>

                    <a href="edit_project.php?id=<?php echo (int)$project['id']; ?>"
                     class="add-project-btn">
                     Edit Project </a>

                    <form action="delete_project.php" method="POST"
                    onsubmit="return confirm('Delete this project permanently?');">

                        <input type="hidden" name="project_id"
                            value="<?php echo (int)$project['id']; ?>">

                        <input type="hidden" name="csrf_token"
                            value="<?php echo htmlspecialchars(
                                $_SESSION['csrf_token'],
                                ENT_QUOTES,
                                'UTF-8'
                            ); ?>">

                        <button type="submit">Delete Project</button>
                  </form>

                </div>

            <?php endwhile; ?>

        <?php else: ?>

            <p>No projects uploaded yet.</p>

        <?php endif; ?>
    </div>
</body>
</html>
