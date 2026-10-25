<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: dashboard.php");
    exit;
}

// Check security token
$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

// Get form details
$id = filter_input(INPUT_POST, "project_id", FILTER_VALIDATE_INT);
$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$projectType = $_POST["project_type"] ?? "";

// Validate details
if (
    !$id ||
    $id < 1 ||
    $title === "" ||
    mb_strlen($title, "UTF-8") > 150 ||
    !in_array($projectType, ["house_plan", "3d_design"], true)
) {
    exit("Invalid project details.");
}

// Update database
$stmt = $conn->prepare(
    "UPDATE projects
     SET title = ?, description = ?, project_type = ?
     WHERE id = ?"
);

$stmt->bind_param(
    "sssi",
    $title,
    $description,
    $projectType,
    $id
);

if (!$stmt->execute()) {
    exit("Could not update project.");
}

$stmt->close();

header("Location: dashboard.php?updated=1");
exit;
?>