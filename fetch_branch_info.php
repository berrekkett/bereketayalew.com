<?php
include('db.php');

// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (isset($_GET['service_number'])) {
    $serviceNumber = $_GET['service_number'];

    // Log the received service number
    error_log("Received service number: " . $serviceNumber);

    $sql = "SELECT title, ip_address FROM branches WHERE service_number = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $serviceNumber);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'title' => $row['title'],
            'ip_address' => $row['ip_address']
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'No branch information found for this service number.'
        ]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Service number not provided.'
    ]);
}
?>
