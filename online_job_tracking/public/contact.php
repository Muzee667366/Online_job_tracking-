<?php 
require_once '../config/db_connect.php';
include '../includes/header.php'; 
?>

<div style="max-width: 800px; margin: 40px auto; display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
    <div>
        <h2 style="color: var(--dark);">Get in Touch</h2>
        <p style="color: var(--secondary);">Have questions about job postings or technical issues? Our support team is here to help.</p>
        
        <div style="margin-top: 20px;">
            <p><strong>📍 Address:</strong> Asella, Ethiopia</p>
            <p><strong>📧 Email:</strong> support@jobtracker.com</p>
            <p><strong>📞 Phone:</strong> +251 900 000 000</p>
        </div>
    </div>

    <div class="card">
        <form action="contact.php" method="POST">
            <label>Your Name</label>
            <input type="text" name="name" required>
            
            <label>Email Address</label>
            <input type="email" name="email" required>
            
            <label>Message</label>
            <textarea name="message" rows="5" style="width:100%; border:1px solid #ddd; border-radius:5px; padding:10px;" required></textarea>
            
            <button type="submit" class="btn" style="margin-top:15px; width:100%;">Send Message</button>
        </form>
        
        <?php
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            // Logic to save to a 'tbl_messages' or send an email
            echo "<p style='color:green; margin-top:10px;'>Thank you! Your message has been sent.</p>";
        }
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
