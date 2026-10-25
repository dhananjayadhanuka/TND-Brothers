<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Project | TND Brothers</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <div class="login-container">
        <h2>Add New Project</h2>

        <form action="save_project.php" method="POST"
              enctype="multipart/form-data">

            <label for="title">Project Title</label>
            <input type="text" id="title" name="title" required>

            <label for="description">Description</label>
            <textarea id="description" name="description" rows="4"></textarea>

            <label for="project_type">Project Type</label>
            <select id="project_type" name="project_type" required>
                <option value="">Select Project Type</option>
                <option value="house_plan">House Plan</option>
                <option value="3d_design">3D Design</option>
            </select>

            <label for="project_image">Project Image</label>
            <input type="file" id="project_image" name="project_image"
                   accept=".jpg,.jpeg,.png,.webp" required>

            <button type="submit">Save Project</button>
        </form>

        <p><a href="dashboard.php">Back to Dashboard</a></p>
    </div>

</body>
</html>