<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

$job_id = $_GET['id'];
include '../includes/header.php'; 
?>

<div class="card">
    <h3>Applicants for Job ID: #<?php echo $job_id; ?></h3>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background: #f8fafc; text-align: left;">
            <th style="padding:12px;">Applicant Name</th>
            <th style="padding:12px;">Email</th>
            <th style="padding:12px;">Applied Date</th>
            <th style="padding:12px;">Status</th>
        </tr>
        <?php
        $stmt = $conn->prepare("
            SELECT a.*, u.Full_Name, u.Email 
            FROM tbl_applications a 
            JOIN tbl_users u ON a.User_ID = u.User_ID 
            WHERE a.Job_ID = ?
        ");
        $stmt->execute([$job_id]);
        while($app = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td style='padding:10px; border-bottom:1px solid #eee;'>{$app['Full_Name']}</td>
                <td style='padding:10px; border-bottom:1px solid #eee;'>{$app['Email']}</td>
                <td style='padding:10px; border-bottom:1px solid #eee;'>{$app['Application_Date']}</td>
                <td style='padding:10px; border-bottom:1px solid #eee;'><strong>{$app['Application_Status']}</strong></td>
            </tr>";
        }
        ?>
    </table>
    <br>
    <a href="employer_dashboard.php" class="btn" style="background:var(--secondary);">Back to Dashboard</a>
</div>

<?php include '../includes/footer.php'; ?>