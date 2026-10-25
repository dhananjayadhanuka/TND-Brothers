<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

require_once "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid project ID.");
}

$stmt = $conn->prepare(
    "SELECT id, title, description, project_type
     FROM projects WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    exit("Project not found.");
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project | TND Brothers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">
    <h2>Edit Project</h2>

    <form action="update_project.php" method="POST">

        <input type="hidden" name="project_id"
               value="<?php echo (int)$project['id']; ?>">

        <input type="hidden" name="csrf_token"
               value="<?php echo htmlspecialchars(
                   $_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'
               ); ?>">

        <label>Project Title</label>
        <input type="text" name="title" maxlength="150" required
               value="<?php echo htmlspecialchars(
                   $project['title'], ENT_QUOTES, 'UTF-8'
               ); ?>">

        <label>Description</label>
        <textarea name="description" rows="4"><?php
            echo htmlspecialchars(
                $project['description'] ?? '',
                ENT_QUOTES,
                'UTF-8'
            );
        ?></textarea>

        <label>Project Type</label>
        <select name="project_type" required>
            <option value="house_plan"
                <?php if ($project['project_type'] === 'house_plan') echo 'selected'; ?>>
                House Plan
            </option>
            <option value="3d_design"
                <?php if ($project['project_type'] === '3d_design') echo 'selected'; ?>>
                3D Design
            </option>
        </select>

        <button type="submit">Update Project</button>
    </form>

    <p><a href="dashboard.php">Back to Dashboard</a></p>
</div>

</body>
</html>