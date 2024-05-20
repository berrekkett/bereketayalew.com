<?php
include('db.php');
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql = "SELECT issues.*, users.username AS created_by_username FROM issues LEFT JOIN users ON issues.created_by = users.id";
$result = $conn->query($sql);
?>

<h2>Issues</h2>
<table>
    <tr>
        <th>Title</th>
        <th>Description</th>
        <th>Status</th>
        <th>Created By</th>
        <th>Actions</th>
    </tr>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . $row['title'] . "</td>";
            echo "<td>" . $row['description'] . "</td>";
            echo "<td>" . $row['status'] . "</td>";
            echo "<td>" . $row['created_by_username'] . "</td>";
            echo "<td><a href='update_issue.php?id=" . $row['id'] . "'>Update</a></td>";
            echo "</tr>";
        }
    } else {
        echo "<tr><td colspan='5'>No issues found</td></tr>";
    }
    ?>
</table>

<?php include('footer.php'); ?>
