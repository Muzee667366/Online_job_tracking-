<?php
session_start();
// Redirect to login if user is not authenticated
if (!isset($_SESSION['user_id'])) {
    header("Location: ../public/login.php");
    exit();
}
?>