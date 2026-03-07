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
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role_id = (int)$_POST['role_id'];
    
    // Validate inputs
    $errors = [];
    
    if (empty($full_name)) {
        $errors[] = "Full name is required";
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Valid email is required";
    }
    
    if (empty($password) || strlen($password) < 8) {
        $errors[] = "Password must be at least 8 characters";
    }
    
    if ($role_id < 1 || $role_id > 3) {
        $errors[] = "Valid role is required";
    }
    
    if (empty($errors)) {
        try {
            // Check if email already exists
            $check = $conn->prepare("SELECT User_ID FROM tbl_users WHERE Email = ?");
            $check->execute([$email]);
            
            if ($check->rowCount() > 0) {
                $_SESSION['error_message'] = "Email already exists";
            } else {
                // Hash password
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                
                // Insert new user
                $stmt = $conn->prepare("INSERT INTO tbl_users (Full_Name, Email, Password, Role_ID, Status) VALUES (?, ?, ?, ?, 'Active')");
                $stmt->execute([$full_name, $email, $hashed_password, $role_id]);
                
                $new_user_id = $conn->lastInsertId();
                
                // If employer, create employer profile
                if ($role_id == 2) {
                    $stmt2 = $conn->prepare("INSERT INTO tbl_employer_profiles (Employer_ID, Verified) VALUES (?, 0)");
                    $stmt2->execute([$new_user_id]);
                }
                
                $_SESSION['success_message'] = "User added successfully!";
            }
        } catch (PDOException $e) {
            $_SESSION['error_message'] = "Database error: " . $e->getMessage();
            error_log("Add user error: " . $e->getMessage());
        }
    } else {
        $_SESSION['error_message'] = implode(", ", $errors);
    }
    
    header("Location: manager_dashboard.php");
    exit();
}
?>