<?php
session_start();
require_once '../../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['resume'])) {
    $target_dir = "../../uploads/resumes/";
    if (!file_exists($target_dir)) mkdir($target_dir, 0777, true);

    $file_extension = strtolower(pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION));
    $new_file_name = "resume_" . $_SESSION['user_id'] . "_" . time() . "." . $file_extension;
    $target_file = $target_dir . $new_file_name;

    if ($file_extension == "pdf") {
        if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
            $sql = "UPDATE tbl_users SET Resume_Path = ? WHERE User_ID = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$new_file_name, $_SESSION['user_id']]);
            header("Location: ../../public/profile.php?upload=success");
        }
    } else {
        echo "Only PDF files are allowed.";
    }
}
?>