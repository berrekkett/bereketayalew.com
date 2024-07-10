<?php
include('header.php');
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Create Issue</title>
    <!-- <link rel="stylesheet" type="text/css" href="styles.css"> -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            background-color: #f9f9f9;
        }
        .container h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            grid-gap: 10px;
            align-items: center;
        }
        .form-grid label {
            text-align: right;
            padding-right: 10px;
        }
        .form-grid input {
            width: 100%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .form-grid button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            cursor: pointer;
            margin-left: 10px;
        }
        .form-grid button#findBranchInfo {
            background-color: #4CAF50; /* Green for the Find button */
        }
        .form-grid button#findBranchInfo:hover,
        .form-grid button[type="submit"]:hover {
            background-color: #45a049; /* Darker green on hover */
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
    <script>
        $(document).ready(function() {
            $('#findBranchInfo').click(function(event) {
                event.preventDefault();
                var serviceNumber = $('#service_number').val();

                if (serviceNumber === '') {
                    alert('Please enter a service number.');
                    return;
                }

                $.ajax({
                    url: 'fetch_branch_info.php',
                    method: 'GET',
                    data: { service_number: serviceNumber },
                    success: function(response) {
                        console.log('Response:', response);
                        var data = JSON.parse(response);
                        if (data.success) {
                            $('#title').val(data.title);
                            $('#ip_address').val(data.ip_address);
                        } else {
                            alert(data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                    }
                });
            });

            $('form').submit(function(event) {
                event.preventDefault(); // Prevent default form submission

                $.ajax({
                    url: 'save_issue.php',
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        var data = JSON.parse(response);
                        if (data.success) {
                            $("#dialog-message").html("Issue created successfully!").dialog({
                                modal: true,
                                buttons: {
                                    "OK": function() {
                                        $(this).dialog("close");
                                        window.location.reload(); // Reload the page to clear the form
                                    }
                                }
                            });
                        } else {
                            alert('Error: ' + data.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Error:', error);
                        alert('There was an error creating the issue. Please try again.');
                    }
                });
            });
        });
    </script>
</head>
<body>
    <div class="container">
        <h2>Create an Issue</h2>
        <form action="save_issue.php" method="post">
            <div class="form-grid">
                <label for="service_number">Service Number:</label>
                <div>
                    <input type="text" id="service_number" name="service_number" placeholder="Enter Service Number">
                    <button id="findBranchInfo">Find</button>
                </div>
                
                <label for="title">Branch Name:</label>
                <input type="text" id="title" name="title" readonly>
                
                <label for="ip_address">IP Address:</label>
                <input type="text" id="ip_address" name="ip_address" readonly>
                
                <label for="description">CCT Number:</label>
                <input type="text" id="description" name="description" placeholder="Enter CCT Number">
                
                <div></div> <!-- Placeholder for grid alignment -->
                <button type="submit">Create Issue</button>
            </div>
        </form>
    </div>

    <div id="dialog-message" title="Notification" style="display:none;"></div>

    <footer>
        <?php include('footer.php'); ?>
    </footer>
</body>
</html>
