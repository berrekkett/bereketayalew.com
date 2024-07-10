<?php
// Function to check if a host is reachable via a specific port
function isHostReachable($host, $port, $timeout = 3) {
    $fp = @fsockopen($host, $port, $errno, $errstr, $timeout);
    if ($fp) {
        fclose($fp);
        return true;
    } else {
        return false;
    }
}

// Function to check if a host is reachable via ICMP (ping)
function isHostPingable($host, $timeout = 3) {
    $output = [];
    $result = 1;
    
    // Execute the ping command
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        // For Windows
        exec("ping -n 1 -w " . ($timeout * 1000) . " " . escapeshellarg($host), $output, $result);
    } else {
        // For Linux and Mac
        exec("ping -c 1 -W " . escapeshellarg($timeout) . " " . escapeshellarg($host), $output, $result);
    }

    return $result === 0;
}

// Assuming you retrieve the IP address from your form input
$ipAddress = $_GET['ip'];

// Array to store ports and their corresponding names
// $portsToCheck = [
//     80 => 'HTTP',
//     443 => 'HTTPS',
//     22 => 'SSH'
// ];

// // Check ICMP (ping) reachability
if (isHostPingable($ipAddress)) {
    echo "The network device ( $ipAddress ) is reachable via ICMP (ping).<br>";
} else {
    echo "The network device ( $ipAddress ) is not reachable via ICMP (ping).<br>";
}

// // Loop through each port and check reachability
// foreach ($portsToCheck as $port => $serviceName) {
//     if (isHostReachable($ipAddress, $port)) {
//         echo "Server at $ipAddress is reachable on port $port ($serviceName).<br>";
//     } else {
//         echo "Server at $ipAddress is not reachable or port $port ($serviceName) is closed.<br>";
//     }
// }
?>
