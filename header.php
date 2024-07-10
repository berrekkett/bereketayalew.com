<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Awash Bank TT Tracking System</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>
    <header>
        <h1>Awash Bank's Ethio Telecom TT Tracking System</h1>
        <nav>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="index.php">Home</a>
                <a href="create_issue.php">Create Issue</a>
                <a href="view_issues.php">History/Records</a>
                <a href="branches.php">Branch Information</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>
    <main>
