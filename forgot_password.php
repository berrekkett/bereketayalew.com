<?php
include('db.php');
include('header.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "<div class='alert alert-danger'>Passwords do not match.</div>";
    } elseif (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[\W]/', $new_password)) {
        echo "<div class='alert alert-danger'>Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.</div>";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password = ?, reset_code = NULL WHERE reset_code = ?");
        $stmt->bind_param('ss', $hashed_password, $otp);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo "<div class='alert alert-success'>Password reset successfully. You can now <a href='login.php'>login</a>.</div>";
        } else {
            echo "<div class='alert alert-danger'>Invalid OTP.</div>";
        }

        $stmt->close();
    }
}
?>

<div class="container">
    <h2>Reset Password</h2>
    <form method="POST" action="">
        <div class="form-group">
            <input type="text" class="form-control" name="otp" placeholder="OTP" required>
        </div>
        <div class="form-group">
            <input type="password" class="form-control" name="new_password" placeholder="New Password" required>
        </div>
        <div class="form-group">
            <input type="password" class="form-control" name="confirm_password" placeholder="Confirm Password" required>
        </div>
        <button type="submit" class="btn btn-primary">Reset Password</button>
    </form>
</div>

<?php include('footer.php'); ?>
