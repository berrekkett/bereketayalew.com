<?php
include('db.php');
include('header.php');

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch issue details based on ID from GET parameter
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $sql = "SELECT * FROM issues WHERE id='$id'";
    $result = $conn->query($sql);
    $issue = $result->fetch_assoc();
}

// Handle form submission for updating status
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Update status in issues table
    $updateStatusSql = "UPDATE issues SET status='$status' WHERE id='$id'";

    if ($conn->query($updateStatusSql) === TRUE) {
        echo "Issue status updated successfully!";
    } else {
        echo "Error updating status: " . $conn->error;
    }

    // Check if status is being updated to 'closed' and update closed_at timestamp
    if ($status == 'closed') {
        $updateClosedAtSql = "UPDATE issues SET closed_at=NOW() WHERE id='$id'";

        if ($conn->query($updateClosedAtSql) === TRUE) {
            // Move closed case to closed_cases table
            $title = $issue['title'];
            $serviceNumber = $issue['service_number'];
            $description = $issue['description']; // Adjust this according to your issue table structure
            $closedDate = date('Y-m-d H:i:s'); // Adjust this if necessary

            $insertClosedCaseSql = "INSERT INTO closed_cases (case_id, title, service_number, description, closed_date)
                                    VALUES ('$id', '$title', '$serviceNumber', '$description', '$closedDate')";

            if ($conn->query($insertClosedCaseSql) === TRUE) {
                echo "Case moved to closed cases successfully.";
            } else {
                echo "Error moving case to closed cases: " . $conn->error;
            }
            echo " Closed at timestamp recorded successfully.";
        } else {
            echo "Error updating closed_at timestamp: " . $conn->error;
        }
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
