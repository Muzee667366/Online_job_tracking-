<?php
session_start();
require_once '../../config/db_connect.php';

// Security check
if (!isset($_SESSION['user_id']) || $_SESSION['role_id'] != 2) {
    $_SESSION['error_message'] = "Unauthorized access";
    header("Location: ../../view/auth/login.php");
    exit();
}

$employer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // Get form data
    $company_name = trim($_POST['company_name'] ?? '');
    $company_website = trim($_POST['company_website'] ?? '');
    $industry = trim($_POST['industry'] ?? '');
    $company_size = $_POST['company_size'] ?? '';
    $founded_year = !empty($_POST['founded_year']) ? (int)$_POST['founded_year'] : null;
    $company_description = trim($_POST['company_description'] ?? '');
    $contact_person = trim($_POST['contact_person'] ?? '');
    $contact_phone = trim($_POST['contact_phone'] ?? '');
    $contact_email = trim($_POST['contact_email'] ?? '');
    $address = trim($_POST['address'] ?? '');
    
    // Validate required fields
    $errors = [];
    if (empty($company_name)) $errors[] = "Company name is required";
    if (empty($industry)) $errors[] = "Industry is required";
    if (empty($company_description)) $errors[] = "Company description is required";
    if (empty($contact_person)) $errors[] = "Contact person is required";
    if (empty($contact_phone)) $errors[] = "Contact phone is required";
    if (empty($contact_email)) $errors[] = "Contact email is required";
    
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: ../../view/employer/employer_dashboard.php");
        exit();
    }
    
    // Handle logo upload
    $logo_path = null;
    if (isset($_FILES['company_logo']) && $_FILES['company_logo']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $file_type = $_FILES['company_logo']['type'];
        $file_size = $_FILES['company_logo']['size'];
        $file_tmp = $_FILES['company_logo']['tmp_name'];
        $file_name = $_FILES['company_logo']['name'];
        
        // Get file extension
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        if (in_array($file_type, $allowed_types) && $file_size <= 2 * 1024 * 1024) {
            $upload_dir = '../../uploads/company_logos/';
            
            // Create directory if it doesn't exist
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            // Generate unique filename
            $new_filename = $employer_id . '_' . time() . '.' . $file_extension;
            $logo_path = 'uploads/company_logos/' . $new_filename;
            $full_path = '../../' . $logo_path;
            
            if (move_uploaded_file($file_tmp, $full_path)) {
                // Success - logo uploaded
                error_log("Logo uploaded successfully: " . $logo_path);
            } else {
                $logo_path = null;
                error_log("Failed to upload logo. Error: " . error_get_last()['message']);
                $_SESSION['error_message'] = "Failed to upload logo. Please try again.";
                header("Location: ../../view/employer/employer_dashboard.php");
                exit();
            }
        } else {
            $_SESSION['error_message'] = "Invalid file type or size. Please upload an image under 2MB.";
            header("Location: ../../view/employer/employer_dashboard.php");
            exit();
        }
    }
    
    try {
        // Check if profile exists
        $check = $conn->prepare("SELECT * FROM tbl_employer_profiles WHERE Employer_ID = ?");
        $check->execute([$employer_id]);
        $profile_exists = $check->rowCount() > 0;
        
        if ($profile_exists) {
            // Update existing profile
            if ($logo_path) {
                // Get old logo to delete
                $old = $conn->prepare("SELECT Company_Logo FROM tbl_employer_profiles WHERE Employer_ID = ?");
                $old->execute([$employer_id]);
                $old_logo = $old->fetchColumn();
                
                if ($old_logo && file_exists('../../' . $old_logo)) {
                    unlink('../../' . $old_logo); // Delete old logo
                }
                
                $sql = "UPDATE tbl_employer_profiles SET 
                        Company_Name = ?, 
                        Company_Website = ?, 
                        Industry = ?, 
                        Company_Size = ?, 
                        Founded_Year = ?, 
                        Company_Description = ?,
                        Contact_Person = ?, 
                        Contact_Phone = ?, 
                        Contact_Email = ?,
                        Address = ?, 
                        Company_Logo = ? 
                        WHERE Employer_ID = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $company_name, 
                    $company_website, 
                    $industry, 
                    $company_size, 
                    $founded_year, 
                    $company_description,
                    $contact_person, 
                    $contact_phone, 
                    $contact_email,
                    $address, 
                    $logo_path, 
                    $employer_id
                ]);
            } else {
                $sql = "UPDATE tbl_employer_profiles SET 
                        Company_Name = ?, 
                        Company_Website = ?, 
                        Industry = ?, 
                        Company_Size = ?, 
                        Founded_Year = ?, 
                        Company_Description = ?,
                        Contact_Person = ?, 
                        Contact_Phone = ?, 
                        Contact_Email = ?,
                        Address = ? 
                        WHERE Employer_ID = ?";
                $stmt = $conn->prepare($sql);
                $result = $stmt->execute([
                    $company_name, 
                    $company_website, 
                    $industry, 
                    $company_size, 
                    $founded_year, 
                    $company_description,
                    $contact_person, 
                    $contact_phone, 
                    $contact_email,
                    $address, 
                    $employer_id
                ]);
            }
            
            if ($result) {
                $_SESSION['success_message'] = "Company profile updated successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to update profile. Please try again.";
            }
            
        } else {
            // Insert new profile
            $sql = "INSERT INTO tbl_employer_profiles 
                    (Employer_ID, Company_Name, Company_Website, Industry, Company_Size, 
                     Founded_Year, Company_Description, Contact_Person, Contact_Phone, 
                     Contact_Email, Address, Company_Logo, Verified) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)";
            $stmt = $conn->prepare($sql);
            $result = $stmt->execute([
                $employer_id, 
                $company_name, 
                $company_website, 
                $industry, 
                $company_size, 
                $founded_year, 
                $company_description,
                $contact_person, 
                $contact_phone, 
                $contact_email,
                $address, 
                $logo_path
            ]);
            
            if ($result) {
                $_SESSION['success_message'] = "Company profile created successfully!";
            } else {
                $_SESSION['error_message'] = "Failed to create profile. Please try again.";
            }
        }
        
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database error: " . $e->getMessage();
        error_log("Employer profile error: " . $e->getMessage());
    }
    
    header("Location: ../../view/employer/employer_dashboard.php");
    exit();
} else {
    // Not a POST request
    header("Location: ../../view/employer/employer_dashboard.php");
    exit();
}
?>