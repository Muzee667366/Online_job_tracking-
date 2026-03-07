<?php
// File: modules/auth/login_action.php
session_start();
require_once '../../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Retrieve user by email
    $sql = "SELECT * FROM tbl_users WHERE Email = :email";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verify password and initiate session
    if ($user && password_verify($password, $user['Password'])) {
        $_SESSION['user_id'] = $user['User_ID'];
        $_SESSION['role_id'] = $user['Role_ID'];
        $_SESSION['full_name'] = $user['Full_Name'];

        // Role-based redirection
        if ($user['Role_ID'] == 1) header("Location: ../../public/manager_dashboard.php");
        elseif ($user['Role_ID'] == 2) header("Location: ../../public/employer_dashboard.php");
        else header("Location: ../../public/jobseeker_dashboard.php");
        exit();
    } else {
        echo "Invalid login credentials. <a href='../../public/login.php'>Try again</a>";
    }
}
?>