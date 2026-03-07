<?php
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

// Security check: Only managers can access this
if ($_SESSION['role_id'] != 1) {
    $_SESSION['error_message'] = "Unauthorized access";
    header("Location: ../public/login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $role_id = (int)$_POST['role_id'];
    $status = $_POST['status'] ?? 'Active';
    $password = $_POST['password'] ?? '';
    
    // Validate inputs
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if ($role_id < 1 || $role_id > 3) {
        $errors[] = "Valid role is required";
    }
    
    if (empty($errors)) {
        try {
            // Check if email exists for other users
            $check = $conn->prepare("SELECT User_ID FROM tbl_users WHERE Email = ? AND User_ID != ?");
            $check->execute([$email, $user_id]);
            
            if ($check->rowCount() > 0) {
                $_SESSION['error_message'] = "Email already exists for another user";
            } else {
                // Update user
                if (!empty($password)) {
                    // Hash the new password
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $conn->prepare("UPDATE tbl_users SET Full_Name = ?, Email = ?, Role_ID = ?, Status = ?, Password = ? WHERE User_ID = ?");
                    $stmt->execute([$full_name, $email, $role_id, $status, $hashed_password, $user_id]);
                } else {
                    $stmt = $conn->prepare("UPDATE tbl_users SET Full_Name = ?, Email = ?, Role_ID = ?, Status = ? WHERE User_ID = ?");
                    $stmt->execute([$full_name, $email, $role_id, $status, $user_id]);
                }
                
                $_SESSION['success_message'] = "User updated successfully!";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            error_log("Edit user error: " . $e->getMessage());
        }
    } else {
        $_SESSION['error_message'] = implode(", ", $errors);
    }
    
    header("Location: manager_dashboard.php");
    exit();
}
?>