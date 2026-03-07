<?php include '../includes/header.php'; ?>

<div class="card" style="max-width: 400px; margin: 50px auto;">
    <h3>Reset Password</h3>
    <p style="font-size: 0.9rem; color: var(--secondary);">Enter your email and we'll send you a link to reset your password.</p>
    
    <form action="../modules/auth/reset_request.php" method="POST">
        <label>Email Address</label>
        <input type="email" name="email" required>
        <button type="submit" class="btn" style="width:100%;">Send Reset Link</button>
    </form>
    <p style="text-align:center;"><a href="login.php">Back to Login</a></p>
</div>

<?php include '../includes/footer.php'; ?>