
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

// Check CSRF token
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

// Get current project image
$stmt = $conn->prepare(
    "SELECT image_path FROM projects WHERE id = ?"
);
$stmt->bind_param("i", $id);
$stmt->execute();
$project = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$project) {
    exit("Project not found.");
}

$oldImage = $project["image_path"];
$newImage = $oldImage;
$newFilePath = null;

// Check whether a new image was selected
if (isset($_FILES["project_image"])) {

    $file = $_FILES["project_image"];

    if (!is_array($file["error"])) {

        if ($file["error"] !== UPLOAD_ERR_NO_FILE) {

            if ($file["error"] !== UPLOAD_ERR_OK) {
                exit("Image upload failed.");
            }

            // Maximum image size: 5 MB
            if ($file["size"] > 5 * 1024 * 1024) {
                exit("Image must be smaller than 5 MB.");
            }

            if (!is_uploaded_file($file["tmp_name"])) {
                exit("Invalid uploaded file.");
            }

            // Validate actual image type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mimeType = $finfo->file($file["tmp_name"]);

            $allowedTypes = [
                "image/jpeg" => "jpg",
                "image/png" => "png",
                "image/webp" => "webp"
            ];

            if (!isset($allowedTypes[$mimeType])) {
                exit("Only JPG, PNG and WEBP images are allowed.");
            }

            // Check image content
            if (@getimagesize($file["tmp_name"]) === false) {
                exit("Invalid image content.");
            }

            // Prepare upload directory
            $uploadDir = __DIR__ . "/uploads/";

            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0755, true)) {
                    exit("Could not create uploads folder.");
                }
            }

            // Generate a unique filename
            $extension = $allowedTypes[$mimeType];
            $filename = bin2hex(random_bytes(16)) . "." . $extension;

            $newFilePath = $uploadDir . $filename;

            if (!move_uploaded_file($file["tmp_name"], $newFilePath)) {
                exit("Could not save new image.");
            }

            // Relative path stored in database
            $newImage = "uploads/" . $filename;
        }

    } else {
        exit("Invalid image upload.");
    }
}

// Update project details and image path
$stmt = $conn->prepare(
    "UPDATE projects
     SET title = ?,
         description = ?,
         project_type = ?,
         image_path = ?
     WHERE id = ?"
);

$stmt->bind_param(
    "ssssi",
    $title,
    $description,
    $projectType,
    $newImage,
    $id
);

if (!$stmt->execute()) {

    // Remove new image if database update failed
    if ($newFilePath !== null && is_file($newFilePath)) {
        unlink($newFilePath);
    }

    exit("Could not update project.");
}

$stmt->close();

// Delete old image only after successful update
if ($newFilePath !== null && $oldImage !== $newImage) {

    // Only delete files inside the uploads directory
    $oldFilename = basename($oldImage);
    $oldFilePath = __DIR__ . "/uploads/" . $oldFilename;

    if (
        str_replace("\\", "/", dirname($oldImage)) === "uploads" &&
        is_file($oldFilePath)
    ) {
        unlink($oldFilePath);
    }
}

header("Location: dashboard.php?updated=1");
exit;
?>
