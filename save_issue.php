<?php
include('db.php');
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $serviceNumber = $_POST['service_number'];
    $title = $_POST['title'];
    $ipAddress = $_POST['ip_address'];
    $description = $_POST['description'];
    $createdBy = $_SESSION['user_id'];
    $status = 'open';
    $createdAt = date('Y-m-d H:i:s');

    $sql = "INSERT INTO issues (service_number, title, ip_address, description, status, created_by, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssssss", $serviceNumber, $title, $ipAddress, $description, $status, $createdBy, $createdAt);

    if ($stmt->execute()) {
        $response = array('success' => true, 'message' => 'Issue created successfully');
    } else {
        $response = array('success' => false, 'message' => 'Error: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    echo json_encode($response);
}
?>
