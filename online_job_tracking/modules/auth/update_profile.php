<?php
session_start();
require_once '../../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['full_name'];
    $email = $_POST['email'];
    $user_id = $_SESSION['user_id'];

    if (!empty($_POST['new_password'])) {
        $pass = password_hash($_POST['new_password'], PASSWORD_BCRYPT);
        $sql = "UPDATE tbl_users SET Full_Name = ?, Email = ?, Password = ? WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $email, $pass, $user_id]);
    } else {
        $sql = "UPDATE tbl_users SET Full_Name = ?, Email = ? WHERE User_ID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$name, $email, $user_id]);
    }
    
    $_SESSION['full_name'] = $name; // Update session name
    header("Location: ../../public/profile.php?updated=1");
}
?>