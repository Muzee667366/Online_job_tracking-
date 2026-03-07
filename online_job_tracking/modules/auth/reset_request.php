<?php
// File: modules/auth/reset_request.php
require_once '../../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    
    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE Email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user) {
        // In production: send email with token. Here: redirect for demonstration.
        header("Location: ../../public/new_password.php?email=" . urlencode($email));
    } else {
        echo "Email not found. <a href='../../public/forgot_password.php'>Try again</a>";
    }
}
?>