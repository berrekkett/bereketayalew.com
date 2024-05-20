<?php
include('header.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<h2>Welcome to the Issue Tracking System</h2>
<p>Select an option from the navigation menu.</p>

<?php include('footer.php'); ?>
