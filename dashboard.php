<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

require_once "db.php";

if (empty($_SESSION["csrf_token"])) {
    $_SESSION["csrf_token"] = bin2hex(random_bytes(32));
}

$result = $conn->query(
    "SELECT id, title, description, project_type, image_path
     FROM projects
     ORDER BY created_at DESC, id DESC"
);

$projects = [];
$housePlans = 0;
$designs = 0;

if ($result) {
    while ($project = $result->fetch_assoc()) {
        $projects[] = $project;

        if ($project["project_type"] === "house_plan") {
            $housePlans++;
        } elseif ($project["project_type"] === "3d_design") {
            $designs++;
        }
    }
}

$totalProjects = count($projects);

function escapeHtml($value) {
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

$username = escapeHtml(
    $_SESSION["admin_username"] ?? "Admin"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | TND Brothers</title>
    <link rel="stylesheet" href="dashboard.css?v=3">
</head>
<body>

<header class="dashboard-header">
    <div class="header-inner">
        <a href="dashboard.php" class="dashboard-logo">
            TND <span>Brothers</span>
            <small>ADMIN STUDIO</small>
        </a>

        <nav class="dashboard-nav">
            <a href="index.html">View Website</a>
            <a href="projects.php">Public Portfolio</a>
            <a href="logout.php" class="logout-btn">Log Out</a>
        </nav>
    </div>
</header>

<main class="dashboard-container">

    <section class="dashboard-welcome">
        <div class="welcome-text">
            <span class="section-label">PORTFOLIO MANAGEMENT</span>
            <h1>Admin Dashboard<span class="gold-dot">.</span></h1>
            <p>
                Welcome back, <strong><?php echo $username; ?></strong>.
                Manage your house plans and 3D designs in one place.
            </p>
        </div>

        <a href="add_project.php" class="primary-btn">
            + Add New Project
        </a>
    </section>

    <?php if (isset($_GET["project_added"])): ?>
        <div class="success-message">Project added successfully!</div>
    <?php elseif (isset($_GET["updated"])): ?>
        <div class="success-message">Project updated successfully!</div>
    <?php elseif (isset($_GET["deleted"])): ?>
        <div class="success-message">Project deleted successfully!</div>
    <?php endif; ?>

    <section class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon">▦</div>
            <p>Total Projects</p>
            <h2><?php echo $totalProjects; ?></h2>
            <span>Portfolio entries</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">⌂</div>
            <p>House Plans</p>
            <h2><?php echo $housePlans; ?></h2>
            <span>2D floor plans</span>
        </div>

        <div class="stat-card">
            <div class="stat-icon">◇</div>
            <p>3D Designs</p>
            <h2><?php echo $designs; ?></h2>
            <span>Visual concepts</span>
        </div>
    </section>

    <section class="projects-section">
        <div class="projects-heading">
            <div>
                <span class="section-label">YOUR WORK</span>
                <h2>Uploaded Projects</h2>
                <p>Update project information, replace images or manage your portfolio.</p>
            </div>

            <span class="project-count">
                <?php echo $totalProjects; ?> Projects
            </span>
        </div>

        <?php if ($totalProjects > 0): ?>

            <div class="projects-grid">

                <?php foreach ($projects as $project): ?>

                    <article class="project-card">
                        <div class="project-image">
                            <img
                                src="<?php echo escapeHtml($project['image_path']); ?>"
                                alt="<?php echo escapeHtml($project['title']); ?>"
                            >

                            <span class="project-type">
                                <?php
                                echo $project["project_type"] === "house_plan"
                                    ? "House Plan"
                                    : "3D Design";
                                ?>
                            </span>
                        </div>

                        <div class="project-content">
                            <h3>
                                <?php echo escapeHtml($project["title"]); ?>
                            </h3>

                            <p class="project-description">
                                <?php
                                echo nl2br(
                                    escapeHtml($project["description"] ?? "")
                                );
                                ?>
                            </p>

                            <div class="project-actions">
                                <a
                                    href="edit_project.php?id=<?php echo (int)$project['id']; ?>"
                                    class="edit-btn"
                                >
                                    Edit Project
                                </a>

                                <form
                                    action="delete_project.php"
                                    method="POST"
                                    onsubmit="return confirm('Delete this project permanently?');"
                                >
                                    <input
                                        type="hidden"
                                        name="project_id"
                                        value="<?php echo (int)$project['id']; ?>"
                                    >

                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php echo escapeHtml($_SESSION['csrf_token']); ?>"
                                    >

                                    <button type="submit" class="delete-btn">
                                        Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </article>

                <?php endforeach; ?>

            </div>

        <?php else: ?>

            <div class="empty-state">
                <h3>No Projects Yet</h3>
                <p>Start by uploading your first house plan or 3D design.</p>
                <a href="add_project.php" class="primary-btn">
                    + Add Your First Project
                </a>
            </div>

        <?php endif; ?>
    </section>

</main>

<footer class="dashboard-footer">
    © 2026 TND Brothers. All rights reserved.
</footer>

</body>
</html>
