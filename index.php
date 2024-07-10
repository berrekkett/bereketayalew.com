<?php
include('db.php');
include('header.php');

// Check if session is not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the search form is submitted
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search = $_GET['search'];
    // SQL query to search for issues by description
    $sql = "SELECT issues.*, users.username AS created_by_username 
            FROM issues 
            LEFT JOIN users ON issues.created_by = users.id 
            WHERE issues.description LIKE '%$search%'
            ORDER BY issues.created_by, issues.created_at DESC";
} else {
    // Default SQL query to fetch all issues
    $sql = "SELECT issues.*, users.username AS created_by_username 
            FROM issues 
            LEFT JOIN users ON issues.created_by = users.id
            ORDER BY issues.created_at DESC";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Issue Tracker</title>
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

        /* Modal styles */
        .modal {
            display: none; /* Hidden by default */
            position: fixed;
            z-index: 1001; /* On top of other elements */
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto; /* Enable scroll if needed */
            background-color: rgba(0,0,0,0.4); /* Black with opacity */
        }

        .modal-content {
            background-color: #fefefe;
            margin: 15% auto; /* 15% from the top and centered */
            padding: 20px;
            border: 1px solid #888;
            width: 80%; /* Could be more or less, depending on screen size */
            max-width: 500px;
            text-align: center;
        }

        .close {
            color: #aaa;
            float: right;
            font-size: 28px;
            font-weight: bold;
        }

        .close:hover,
        .close:focus {
            color: black;
            text-decoration: none;
            cursor: pointer;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#pingForm').submit(function(event) {
                // Prevent default form submission
                event.preventDefault();

                // Get IP address from input field
                var ipAddress = $('#ip_address').val();

                // AJAX request to ping_check.php
                $.ajax({
                    url: 'ping_checker.php',
                    method: 'GET',
                    data: { ip: ipAddress },
                    success: function(response) {
                        // Show the modal with the response
                        $('#modalContent').html(response);
                        $('#myModal').show();
                    },
                    error: function(xhr, status, error) {
                        // Handle errors (if any)
                        console.error('Error:', error);
                    }
                });

            });

            // Close the modal when the user clicks on <span> (x) or "OK" button
            $('.close, #okButton').click(function() {
                $('#myModal').hide();
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <div class="form-wrapper">
            <h2>Search Issues</h2>
            <form action="" method="GET">
                <label for="search">Search by TT no:</label>
                <input type="text" id="search" name="search" value="<?php echo isset($_GET['search']) ? $_GET['search'] : ''; ?>" placeholder="Enter TT">
                <button type="submit">Search</button>
            </form>
        </div>

        <div class="form-wrapper">
            <h2>Ping Check</h2>
            <form id="pingForm" action="" method="GET">
                <label for="ip_address">Insert IP Address:</label>
                <input type="text" id="ip_address" name="ip" value="<?php echo isset($_GET['ip']) ? $_GET['ip'] : ''; ?>" placeholder="IP Address">
                <button type="submit">Ping</button>
            </form>
        </div>
    </div>

    <!-- Modal -->
    <div id="myModal" class="modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modalContent"></div>
            <button id="okButton">OK</button>
        </div>
    </div>

    <h2>Awash TT Cases</h2>

    <div class="table-container">
        <?php
        if ($result->num_rows > 0) {
            $currentDate = null;
            $sno = 1;

            while ($row = $result->fetch_assoc()) {
                $entryDate = date('Y-m-d', strtotime($row['created_at']));

                if ($currentDate !== $entryDate) {
                    if ($currentDate !== null) {
                        echo "</table><br>"; // Close the previous table
                    }
                    $currentDate = $entryDate;
                    $sno = 1; // Reset serial number for each new date
                    echo "<h3>Entries for " . date('F j, Y', strtotime($entryDate)) . "</h3>";
                    echo "<table border='1'>";
                    echo "<tr>
                            <th>S.No</th>
                            <th>Branch Name</th>
                            <th>IP Address</th>
                            <th>Service Number</th>
                            <th>Trouble Ticket Number</th>
                            <th>Time and Date</th>
                            <th>Status</th>
                            <th>Created By</th>
                            <th>Actions</th>
                          </tr>";
                }

                echo "<tr>
                        <td>" . ($sno++) . "</td>
                        <td>{$row['title']}</td>
                        <td>{$row['ip_address']}</td>
                        <td>{$row['service_number']}</td>
                        <td>{$row['description']}</td>
                        <td>{$row['created_at']}</td>
                        <td>{$row['status']}</td>
                        <td>{$row['created_by_username']}</td>
                        <td><a href='update_issue.php?id={$row['id']}'>Update</a> | <a href='delete_issue.php?id={$row['id']}'>Delete</a></td>
                      </tr>";
            }
            echo "</table>";
        } else {
            echo "<tr><td colspan='9'>No issues found</td></tr>";
        }
        ?>
    </div>

    <div class="footer">
        <?php include('footer.php'); ?>
    </div>

</body>
</html>
