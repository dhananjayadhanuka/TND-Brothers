<?php
require_once "db.php";

$type = $_GET['type'] ?? 'all';

if (!in_array($type, ['all', 'house_plan', '3d_design'], true)) {
    $type = 'all';
}

if ($type === 'all') {
    $stmt = $conn->prepare(
        "SELECT id, title, description, project_type, image_path
         FROM projects
         ORDER BY created_at DESC, id DESC"
    );
} else {
    $stmt = $conn->prepare(
        "SELECT id, title, description, project_type, image_path
         FROM projects
         WHERE project_type = ?
         ORDER BY created_at DESC, id DESC"
    );

    $stmt->bind_param("s", $type);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Projects | TND Brothers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

<div class="login-container dashboard-container">

    <h2>Our House Plans & 3D Designs</h2>

    <div class="project-filters">
        <a href="projects.php?type=all">All Projects</a>
        <a href="projects.php?type=house_plan">House Plans</a>
        <a href="projects.php?type=3d_design">3D Designs</a>
    </div>

    <div class="projects-grid">

        <?php if ($result && $result->num_rows > 0): ?>

            <?php while ($project = $result->fetch_assoc()): ?>

                <div class="project-card">

                    <img
                        src="<?php echo htmlspecialchars(
                            $project['image_path'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>"
                        alt="Project Image"
                    >

                    <h3>
                        <?php echo htmlspecialchars(
                            $project['title'],
                            ENT_QUOTES,
                            'UTF-8'
                        ); ?>
                    </h3>

                    <p>
                        <?php echo $project['project_type'] === 'house_plan'
                            ? 'House Plan' : '3D Design'; ?>
                    </p>

                    <p class="project-description">
                        <?php echo nl2br(htmlspecialchars(
                            $project['description'] ?? '',
                            ENT_QUOTES,
                            'UTF-8'
                        )); ?>
                    </p>

                    <a href="project_details.php?id=<?php echo (int)$project['id']; ?>"
                         class="add-project-btn">
                         View Details
                    </a>

                </div>

            <?php endwhile; ?>

        <?php else: ?>
            <p>No projects available yet.</p>
        <?php endif; ?>

    </div>

    <p><a href="index.html">Back to Home</a></p>

</div>

</body>
</html>