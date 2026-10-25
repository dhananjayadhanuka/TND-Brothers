<?php
require_once "db.php";

$id = filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    http_response_code(400);
    exit("Invalid project ID.");
}

$stmt = $conn->prepare(
    "SELECT id, title, description, project_type, image_path
     FROM projects
     WHERE id = ?"
);

$stmt->bind_param("i", $id);
$stmt->execute();

$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    http_response_code(404);
    exit("Project not found.");
}

function escapeHtml($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        'UTF-8'
    );
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo escapeHtml($project['title']); ?> | TND Brothers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container">

    <h2><?php echo escapeHtml($project['title']); ?></h2>

    <img
        src="<?php echo escapeHtml($project['image_path']); ?>"
        alt="Project Image"
        style="width:100%; border-radius:8px;"
    >

    <p>
        <strong>Project Type:</strong>
        <?php
        echo $project['project_type'] === 'house_plan'
            ? 'House Plan'
            : '3D Design';
        ?>
    </p>

    <h3>Project Description</h3>

    <p>
        <?php
        echo nl2br(escapeHtml($project['description'] ?? ''));
        ?>
    </p>

    <p>
        <a href="index.html#contact" class="add-project-btn">
            Contact Designer
        </a>
    </p>

    <p>
        <a href="projects.php">Back to Projects</a>
    </p>

</div>

</body>
</html>