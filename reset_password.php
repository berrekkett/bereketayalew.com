<?php
include('db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        echo "Passwords do not match.";
    } elseif (strlen($new_password) < 8 || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[a-z]/', $new_password) || !preg_match('/[0-9]/', $new_password) || !preg_match('/[\W]/', $new_password)) {
        echo "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

        $stmt = $conn->prepare("UPDATE users SET password=?, verification_code=NULL WHERE verification_code=?");
        $stmt->bind_param('ss', $hashed_password, $otp);
        if ($stmt->execute() && $stmt->affected_rows > 0) {
            echo "Password reset successfully. You can now login.";
        } else {
            echo "Invalid OTP.";
        }

        $stmt->close();
    }
}
?>

<h2>Reset Password</h2>
<form method="POST" action="">
    OTP: <input type="text" name="otp" required><br>
    New Password: <input type="password" name="new_password" required><br>
    Confirm Password: <input type="password" name="confirm_password" required><br>
    <button type="submit">Reset Password</button>
</form>

<?php include('footer.php'); ?>
