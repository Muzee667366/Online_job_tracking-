<?php
// File: modules/auth/register_action.php
require_once '../../config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $role = $_POST['role_id'];
    $pass = password_hash($_POST['password'], PASSWORD_BCRYPT); // Secure encryption

    $sql = "INSERT INTO tbl_users (Full_Name, Email, Password, Role_ID) VALUES (:name, :email, :pass, :role)";
    $stmt = $conn->prepare($sql);
    
    if($stmt->execute(['name' => $name, 'email' => $email, 'pass' => $pass, 'role' => $role])) {
        header("Location: ../../public/login.php?msg=success");
    } else {
        echo "Registration Failed.";
    }
}
?>