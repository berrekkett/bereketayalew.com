<?php
include('db.php');
include('header.php'); // Ensure session_start is at the beginning

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Initialize variables for date range
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-1 week'));
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// SQL query to select closed issues within the specified date range, ordered by closed_at DESC
$sql = "SELECT issues.*, users.username AS created_by_username 
        FROM issues 
        LEFT JOIN users ON issues.created_by = users.id 
        WHERE issues.status = 'closed'
        AND DATE(issues.closed_at) >= ? 
        AND DATE(issues.closed_at) <= ? 
        ORDER BY issues.closed_at DESC"; // Order by closed_at in descending order

$stmt = $conn->prepare($sql);
$stmt->bind_param('ss', $startDate, $endDate);
$stmt->execute();
$result = $stmt->get_result();

// Check if the form is submitted and generate CSV download
if (isset($_GET['download_csv']) && $_GET['download_csv'] == '1') {
    // Filename for the downloaded file
    $filename = 'closed_issues_report_' . date('Ymd') . '.csv';

    // Output headers to trigger file download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Create a file pointer connected to the output stream
    $output = fopen('php://output', 'w');

    // CSV header row
    fputcsv($output, [
        'S.No',
        'Branch Name',
        'IP Address',
        'Service Number',
        'Trouble Ticket Number',
        'Closed At',
        'Closed By'
    ]);

    // Fetch and output each row of data
    $result->data_seek(0); // Reset result pointer to start
    $sno = 1; // Reset serial number
    while ($row = $result->fetch_assoc()) {
        fputcsv($output, [
            $sno++,
            $row['title'],
            $row['ip_address'],
            $row['service_number'],
            $row['description'],
            $row['closed_at'],
            $row['created_by_username']
        ]);
    }

    // Close the file pointer
    fclose($output);

    // Exit script to prevent HTML output
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Closed Issues Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .form-wrapper {
            width: 45%;
            margin-top: 10px; 
        }

        .form-wrapper h2 {
            margin-bottom: 10px;
        }

        .form-wrapper form {
            border: 1px solid #ccc;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 5px;
        }

        .form-wrapper label {
            display: block;
            margin-bottom: 10px;
        }

        .form-wrapper input[type=date] {
            width: 100%;
            padding: 8px;
            margin-bottom: 10px;
            box-sizing: border-box;
        }

        .form-wrapper button {
            background-color: #4CAF50;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }

        .container {
            width: 80%;
            margin: 20px auto;
        }

        .container form {
            margin-bottom: 20px;
        }

        .container table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .container th, .container td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .container th {
            background-color: #f2f2f2;
        }

        .table-container {
            overflow-x: auto;
        }

        footer {
            text-align: center;
            padding: 10px 0;
            background-color: #333;
            color: white;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h2>Closed Issues Report</h2>
            <form action="" method="GET">
                <label for="start_date">Start Date:</label>
                <input type="date" id="start_date" name="start_date" value="<?php echo $startDate; ?>">
                <label for="end_date">End Date:</label>
                <input type="date" id="end_date" name="end_date" value="<?php echo $endDate; ?>">
                <button type="submit">Generate Report</button>

                <!-- Hidden input to trigger CSV download -->
                <input type="hidden" name="download_csv" value="1">
            </form>
        </div>
        <div class="table-container">
            <table>
                <tr>
                    <th>S.No</th>
                    <th>Branch Name</th>
                    <th>IP Address</th>
                    <th>Service Number</th>
                    <th>Trouble Ticket Number</th>
                    <th>Closed At</th>
                    <th>Closed By</th>
                </tr>
                <?php
                // Check if there are closed issues
                if ($result->num_rows > 0) {
                    $sno = 1;
                    // Loop through each row in the result set
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $sno++ . "</td>";
                        echo "<td>" . $row['title'] . "</td>";
                        echo "<td>" . $row['ip_address'] . "</td>";
                        echo "<td>" . $row['service_number'] . "</td>";
                        echo "<td>" . $row['description'] . "</td>";
                        echo "<td>" . $row['closed_at'] . "</td>";
                        echo "<td>" . $row['created_by_username'] . "</td>";
                        echo "</tr>";
                    }
                } else {
                    // Display message if no closed issues found
                    echo "<tr><td colspan='7'>No closed issues found within the selected date range</td></tr>";
                }
                ?>
            </table>
        </div>
    </div>

    <footer>
        <?php include('footer.php'); ?>
    </footer>
</body>
</html>
