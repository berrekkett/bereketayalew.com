<?php
require 'vendor/autoload.php'; // Add this line to include the Composer autoloader

include('db.php');
include('header.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$sql1 = "SELECT DISTINCT Branch_Name FROM branch_info";
$result1 = $conn->query($sql1);

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
        $Region = $data[0];
        $Branch_Name = $data[1];
        $Account_No = $data[2];
        $Service_No = $data[3];
        $LAN_IP = $data[4];
        $WAN_IP_1 = $data[5]; // Assuming 'wan_ip' is in column D
        $WAN_IP_2 = $data[6];

        // Check if a record with the same service number already exists
        $checkSql = "SELECT COUNT(*) as count FROM branch_info WHERE Service_No = '$Service_No'";
        $checkResult = $conn->query($checkSql);
        $checkRow = $checkResult->fetch_assoc();
        if ($checkRow['count'] > 0) {
            $errors[] = "Skipped: Record with Service Number '$Service_No' already exists.";
            continue; // Skip insertion
        }

        // Example SQL query to insert into your branches table
        $insertSql = "INSERT INTO branch_info (Region, Branch_Name, Account_No, Service_No, LAN_IP, WAN_IP_1, WAN_IP_2)
                      VALUES ('$Region', '$Branch_Name' , '$Account_No', '$Service_No', '$LAN_IP', '$WAN_IP_1', '$WAN_IP_2')";

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
    $uploadOk = 1;

    // Check if the fileToUpload key exists in the $_FILES array
    if (isset($_FILES["fileToUpload"])) {
        $targetFile = $targetDir . basename($_FILES["fileToUpload"]["name"]);

        // Check if the uploads directory exists, if not, create it
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0777, true);
        }

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
    } else {
        echo "No file was uploaded.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Filter Items by Branch</title>
    <!-- Include Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <!-- Include jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Include Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <style>
        /* Custom styles for Select2 */
        .select2-container--default .select2-selection--single {
            background-color: #f0f0f0;
            border: 1px solid #ccc;
            border-radius: 4px;
            height: 38px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #333;
            line-height: 36px;
        }
        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 36px;
        }
        .select2-dropdown {
            border-radius: 4px;
            border: 1px solid #ccc;
        }
        .select2-results__option {
            padding: 8px 16px;
        }
        .select2-results__option--highlighted {
            background-color: #f8f8f8;
            color: #333;
        }
    </style>
</head>
<body>
    <form method="POST" action="">
        <h2>Search Branch Information</h2>
        <select name="Branch_Name" id="Branch_Name">
            <option value="">--Select Branch--</option>
            <?php while ($row = $result1->fetch_assoc()): ?>
                <option value="<?php echo $row['Branch_Name']; ?>"><?php echo $row['Branch_Name']; ?></option>
            <?php endwhile; ?>
        </select>
        <button type="submit">Filter</button>
    </form>
    <!-- Upload and Search Forms in a Flex Container -->
    <div class="container">
        <!-- Upload Form -->
        <div class="form-wrapper">
            <h2>Upload Excel File</h2>
            <form action="search_branch_info.php" method="post" enctype="multipart/form-data">
                Select Excel file to upload:
                <input type="file" name="fileToUpload" id="fileToUpload">
                <input type="submit" value="Upload File" name="submit">
            </form>
        </div>
    </div>

    <?php
    if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_POST['Branch_Name'])) {
        $selected_branch = $_POST['Branch_Name'];

        // Fetch items based on selected branch
        $stmt = $conn->prepare("SELECT * FROM branch_info WHERE Branch_Name = ?");
        $stmt->bind_param("s", $selected_branch);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {
            echo "<table border='1'>
                    <tr>
                        <th>Region</th>
                        <th>Branch Name</th>
                        <th>Account No</th>
                        <th>Service No</th>
                        <th>LAN IP</th>
                        <th>WAN IP1</th>
                        <th>WAN IP2</th>
                    </tr>";
            while ($row = $result->fetch_assoc()) {
                echo "<tr>
                        <td>" . $row['Region'] . "</td>
                        <td>" . $row['Branch_Name'] . "</td>
                        <td>" . $row['Account_No'] . "</td>
                        <td>" . $row['Service_No'] . "</td>
                        <td>" . $row['LAN_IP'] . "</td>
                        <td>" . $row['WAN_IP_1'] . "</td>
                        <td>" . $row['WAN_IP_2'] . "</td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "No items found in the selected branch.";
        }
    }
    ?>
</body>
</html>
