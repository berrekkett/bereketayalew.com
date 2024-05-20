<?php
include('db.php');
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $description = $_POST['description'];
    $created_by = $_SESSION['user_id'];

    $sql = "INSERT INTO issues (title, description, created_by) VALUES ('$title', '$description', '$created_by')";

    if ($conn->query($sql) === TRUE) {
        echo "Issue created successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<h2>Create Issue</h2>
<form method="POST" action="">
    Title: <input type="text" name="title" required><br>
    Description: <textarea name="description" required></textarea><br>
    <button type="submit">Create Issue</button>
</form>

<?php include('footer.php'); ?>
