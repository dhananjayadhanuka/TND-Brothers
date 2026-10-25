<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: add_project.php");
    exit;
}

$title = trim($_POST["title"] ?? "");
$description = trim($_POST["description"] ?? "");
$projectType = $_POST["project_type"] ?? "";

if (
    $title === "" ||
    strlen($title) > 150 ||
    !in_array($projectType, ["house_plan", "3d_design"], true)
) {
    exit("Invalid project details.");
}

if (
    !isset($_FILES["project_image"]) ||
    $_FILES["project_image"]["error"] !== UPLOAD_ERR_OK
) {
    exit("Image upload failed.");
}

$file = $_FILES["project_image"];

if ($file["size"] > 5 * 1024 * 1024) {
    exit("Image must be smaller than 5 MB.");
}

$finfo = new finfo(FILEINFO_MIME_TYPE);
$mime = $finfo->file($file["tmp_name"]);

$allowed = [
    "image/jpeg" => "jpg",
    "image/png" => "png",
    "image/webp" => "webp"
];

if (!isset($allowed[$mime])) {
    exit("Only JPG, PNG and WebP images are allowed.");
}

$uploadDir = __DIR__ . "/uploads/";

if (!is_dir($uploadDir)) {
    exit("Uploads folder not found.");
}

$fileName = bin2hex(random_bytes(16)) . "." . $allowed[$mime];
$destination = $uploadDir . $fileName;
$imagePath = "uploads/" . $fileName;

if (!move_uploaded_file($file["tmp_name"], $destination)) {
    exit("Could not save uploaded image.");
}

$stmt = $conn->prepare(
    "INSERT INTO projects
    (title, description, project_type, image_path)
    VALUES (?, ?, ?, ?)"
);

if (!$stmt) {
    unlink($destination);
    exit("Database error.");
}

$stmt->bind_param(
    "ssss",
    $title,
    $description,
    $projectType,
    $imagePath
);

if (!$stmt->execute()) {
    unlink($destination);
    exit("Could not save project.");
}

$stmt->close();

header("Location: dashboard.php?project_added=1");
exit;
?>