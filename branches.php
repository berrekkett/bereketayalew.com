<?php
require 'vendor/autoload.php'; // Include Composer's autoloader
include('db.php'); // Assuming this file contains your database connection logic
include('header.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

// Function to insert data from Excel into database, preventing redundancy and resuming from last position
function insertExcelDataIntoDB($conn, $excelFile) {
    // Load the Excel file
    $spreadsheet = IOFactory::load($excelFile);

    // Select the first worksheet in the Excel file
    $worksheet = $spreadsheet->getActiveSheet();

    // Initialize last inserted row number from session or database
    $lastInsertedRow = isset($_SESSION['last_inserted_row']) ? $_SESSION['last_inserted_row'] : 1;

    // Prepare an array to collect errors, if any
    $errors = [];

    // Iterate through rows starting from the last successfully inserted row + 1
    foreach ($worksheet->getRowIterator($lastInsertedRow + 1) as $row) {
        $cellIterator = $row->getCellIterator();
        $cellIterator->setIterateOnlyExistingCells(false); // Iterate all cells, even if they are empty

        // Extract data from each cell
        $data = [];
        foreach ($cellIterator as $cell) {
            $data[] = $cell->getValue(); // Get cell value
        }

        // Process $data array to insert into your database
        // Example: Insert $data into MySQL database
        $branchName = $data[0];
        $ipAddress = $data[1];
        $serviceNumber = $data[2];
        $wanIp = $data[3]; // Assuming 'wan_ip' is in column D

        // Check if a record with the same service number already exists
        $checkSql = "SELECT COUNT(*) as count FROM branches WHERE service_number = '$serviceNumber'";
        $checkResult = $conn->query($checkSql);
        $checkRow = $checkResult->fetch_assoc();
        if ($checkRow['count'] > 0) {
            $errors[] = "Skipped: Record with Service Number '$serviceNumber' already exists.";
            continue; // Skip insertion
        }

        // Example SQL query to insert into your branches table
        $insertSql = "INSERT INTO branches (title, ip_address, service_number, wan_ip)
                      VALUES ('$branchName', '$ipAddress', '$serviceNumber', '$wanIp')";

        // Execute the SQL query (assuming $conn is your database connection)
        if ($conn->query($insertSql) !== TRUE) {
            $errors[] = "Error: " . $insertSql . "<br>" . $conn->error;
        } else {
            // Update last successfully inserted row
            $_SESSION['last_inserted_row'] = $row->getRowIndex(); // Store current row index in session
        }
    }

    // Return any errors encountered during insertion
    return $errors;
}

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $targetDir = "uploads/";
    $targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);
    
    // Check if the uploads directory exists, if not, create it
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    $uploadOk = 1;
    $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

    // Check if file is a valid Excel file
    if ($fileType != "xlsx" && $fileType != "xls") {
        echo "Sorry, only XLSX & XLS files are allowed.";
        $uploadOk = 0;
    }

    if ($uploadOk == 1) {
        if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $targetFile)) {
            echo "The file " . basename($_FILES["fileToUpload"]["name"]) . " has been uploaded.";
            
            // Insert data from Excel into database
            $insertErrors = insertExcelDataIntoDB($conn, $targetFile);

            // Check for insertion errors
            if (!empty($insertErrors)) {
                foreach ($insertErrors as $error) {
                    echo $error;
                }
            }
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    }
}

// Fetch data based on search criteria
$search = isset($_GET['search']) ? $_GET['search'] : '';
$sql = "SELECT * FROM branches";
if (!empty($search)) {
    $sql .= " WHERE title LIKE '%$search%' OR service_number LIKE '%$search%'";
}
$result = $conn->query($sql);

// Fetch closed cases based on search criteria
$closedCases = [];
if (!empty($search)) {
    $closedCasesSql = "SELECT description, closed_date FROM closed_cases WHERE title LIKE '%$search%' OR service_number LIKE '%$search%'";
    $closedCasesResult = $conn->query($closedCasesSql);
    if ($closedCasesResult->num_rows > 0) {
        while ($row = $closedCasesResult->fetch_assoc()) {
            $closedCases[] = ['description' => $row['description'], 'closed_date' => $row['closed_date']];
        }
    } else {
        echo ""; // Debug message
    }
} else {
    echo ""; // Debug message
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Branch Information</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        /* General styles */
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            padding-bottom: 60px; /* Ensure space for footer */
        }

        .container {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin: 20px;
        }

        .form-wrapper {
            width: 45%;
            margin-bottom: 20px;
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

        .form-wrapper input[type=text] {
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

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border: 1px solid #ddd;
        }

        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        /* Table container with fixed height and overflow scrolling */
        .table-container {
            max-height: 400px; /* Adjust height as needed */
            overflow-y: auto;
        }

        /* Footer styles */
        .footer {
            background-color: #333;
            color: white;
            text-align: center;
            padding: 10px;
            position: fixed;
            left: 0;
            bottom: 0;
            width: 100%;
            z-index: 1000; /* Ensure footer is on top */
        }
    </style>
</head>
<body>
    <h2>Branch Information</h2>

    <!-- Upload and Search Forms in a Flex Container -->
    <div class="container">
        <!-- Upload Form -->
        <div class="form-wrapper">
            <h2>Upload Excel File</h2>
            <form action="branches.php" method="post" enctype="multipart/form-data">
                Select Excel file to upload:
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Upload File" name="submit">
            </form>
        </div>

        <!-- Search Form -->
        <div class="form-wrapper">
            <h2>Search by Branch Name or Service Number</h2>
            <form action="branches.php" method="get">
                <label for="search">Search by Branch Name or Service Number:</label>
                <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Search...">
                <button type="submit">Search</button>
            </form>
        </div>
    </div>

    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>S.No</th>
                    <th>Branch Name</th>
                    <th>IP Address</th>
                    <th>Service Number</th>
                    <th>Wan IP</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($result->num_rows > 0) {
                    $sno = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>" . $sno++ . "</td>";
                        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['ip_address']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['service_number']) . "</td>";
                        echo "<td>" . htmlspecialchars($row['wan_ip']) . "</td>"; // Adjust column name if necessary
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>No branch information found</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($closedCases)): ?>
        <h2>Closed Cases for "<?php echo htmlspecialchars($search); ?>"</h2>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Closed TTs</th>
                        <th>Closed On</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($closedCases as $case): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($case['description']); ?></td>
                            <td><?php echo htmlspecialchars($case['closed_date']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <p>No closed cases found for the search query: "<?php echo htmlspecialchars($search); ?>"</p>
    <?php endif; ?>

    <div class="footer">
        <?php include('footer.php'); ?>
    </div>
</body>
</html>
