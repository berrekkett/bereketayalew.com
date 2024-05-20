    <?php
    include('db.php');
    include('header.php');

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];

        // Password strength validation
        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password) || !preg_match('/[\W]/', $password)) {
            echo "Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, one number, and one special character.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Prepared statement to prevent SQL injection
            $stmt = $conn->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hashed_password);

            if ($stmt->execute()) {
                echo "Registration successful!";
                header("Location: login.php");
                exit(); // Ensure the script stops executing after redirect
            } else {
                echo "Error: " . $stmt->error;
            }

            $stmt->close();
        }
    }
    ?>

    <h2>Register</h2>
    <form method="POST" action="">
        Username: <input type="text" name="username" required><br>
        Password: <input type="password" name="password" required><br>
        <button type="submit">Register</button>
    </form>

    <?php include('footer.php'); ?>
