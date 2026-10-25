<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin.php");
    exit;
}

require_once "db.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    exit("Method not allowed.");
}

$token = $_POST["csrf_token"] ?? "";

if (
    !is_string($token) ||
    !isset($_SESSION["csrf_token"]) ||
    !hash_equals($_SESSION["csrf_token"], $token)
) {
    http_response_code(403);
    exit("Invalid security token.");
}

$id = filter_input(INPUT_POST, "project_id", FILTER_VALIDATE_INT);

if (!$id || $id < 1) {
    exit("Invalid project ID.");
}

$stmt = $conn->prepare(
    "SELECT image_path FROM projects WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->bind_result($imagePath);

if (!$stmt->fetch()) {
    $stmt->close();
    exit("Project not found.");
}
$stmt->close();

$stmt = $conn->prepare(
    "DELETE FROM projects WHERE id = ?"
);
$stmt->bind_param("i", $id);

if (!$stmt->execute()) {
    exit("Could not delete project.");
}
$stmt->close();

// Delete only images stored in our uploads folder.
if (
    is_string($imagePath) &&
    preg_match(
        '~^uploads/[a-f0-9]{32}\.(jpg|png|webp)$~',
        $imagePath
    )
) {
    $filePath = __DIR__ . "/" . $imagePath;

    if (is_file($filePath)) {
        unlink($filePath);
    }
}

header("Location: dashboard.php?deleted=1");
exit;