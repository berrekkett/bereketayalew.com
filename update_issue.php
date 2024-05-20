<?php
include('db.php');
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM issues WHERE id='$id'";
    $result = $conn->query($sql);
    $issue = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    $sql = "UPDATE issues SET status='$status' WHERE id='$id'";

    if ($conn->query($sql) === TRUE) {
        echo "Issue updated successfully!";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
?>

<h2>Update Issue</h2>
<form method="POST" action="">
    <input type="hidden" name="id" value="<?php echo $issue['id']; ?>">
    Status: 
    <select name="status">
        <option value="open" <?php if ($issue['status'] == 'open') echo 'selected'; ?>>Open</option>
        <option value="in_progress" <?php if ($issue['status'] == 'in_progress') echo 'selected'; ?>>In Progress</option>
        <option value="closed" <?php if ($issue['status'] == 'closed') echo 'selected'; ?>>Closed</option>
    </select><br>
    <button type="submit">Update Issue</button>
</form>

<?php include('footer.php'); ?>
