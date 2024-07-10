<?php
include('db.php'); // Include your database connection file here

// Check if ID is provided in the URL
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prepare SQL statement to delete the issue
    $sql = "DELETE FROM issues WHERE id=?";

    // Prepare statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id); // 'id' is integer type

    // Execute statement
    if ($stmt->execute()) {
        echo "Issue with ID $id deleted successfully.";
    } else {
        echo "Error deleting issue: " . $conn->error;
    }
} else {
    echo "Issue ID not provided.";
}

// Redirect to index.php after deletion
header("Location: index.php");
exit();
?>
