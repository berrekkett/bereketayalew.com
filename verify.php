<?php
include('db.php');

if (isset($_GET['code'])) {
    $verification_code = $_GET['code'];

    $stmt = $conn->prepare("SELECT id FROM users WHERE verification_code = ?");
    $stmt->bind_param("s", $verification_code);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->close();

        $stmt = $conn->prepare("UPDATE users SET is_verified = 1, verification_code = NULL WHERE verification_code = ?");
        $stmt->bind_param("s", $verification_code);
        if ($stmt->execute()) {
            echo "Email verified successfully. You can now login.";
        } else {
            echo "Error: " . $stmt->error;
        }
    } else {
        echo "Invalid verification code.";
    }

    $stmt->close();
}
?>
