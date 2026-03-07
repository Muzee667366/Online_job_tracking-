<?php 
require_once '../includes/auth_check.php';
require_once '../config/db_connect.php';

if ($_SESSION['role_id'] != 1) exit("Unauthorized");

include '../includes/header.php'; 
?>

<div class="card">
    <div style="display:flex; justify-content: space-between; align-items: center;">
        <h2>System Activity Report</h2>
        <button onclick="window.print()" class="btn" style="background:#28a745;">🖨️ Print/Save as PDF</button>
    </div>
    <hr>
    
    <h4>Application Statistics</h4>
    <table style="width: 100%; border-collapse: collapse;">
        <tr style="background: #f1f5f9; text-align: left;">
            <th style="padding:10px;">Job Title</th>
            <th style="padding:10px;">Employer</th>
            <th style="padding:10px;">Total Applicants</th>
        </tr>
        <?php
        $report_query = "SELECT j.Job_Title, u.Full_Name as Employer, 
                        (SELECT COUNT(*) FROM tbl_applications WHERE Job_ID = j.Job_ID) as Total 
                        FROM tbl_jobs j 
                        JOIN tbl_users u ON j.Employer_ID = u.User_ID";
        $stmt = $conn->query($report_query);
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<tr>
                <td style='padding:10px; border-bottom:1px solid #ddd;'>{$row['Job_Title']}</td>
                <td style='padding:10px; border-bottom:1px solid #ddd;'>{$row['Employer']}</td>
                <td style='padding:10px; border-bottom:1px solid #ddd;'>{$row['Total']}</td>
            </tr>";
        }
        ?>
    </table>
</div>

<?php include '../includes/footer.php'; ?>