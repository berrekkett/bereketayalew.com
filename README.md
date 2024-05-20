# Issue Tracking System

A simple PHP-based issue tracking system that allows users to register, login, create issues, view issues, and update the status of issues. This project demonstrates basic CRUD operations and user authentication.

## Table of Contents

- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Usage](#usage)
- [Project Structure](#project-structure)
- [Database Schema](#database-schema)
- [Screenshots](#screenshots)

## Features

- User registration and login.
- Create, view, and update issues.
- Status management for issues (open, in progress, closed).
- Simple and clean UI.

## Requirements

- Web server with PHP 7.x or higher (e.g., Apache, Nginx).
- MySQL database server.
- A web browser.

## Installation

1. **Clone the Repository**
    ```bash
    git clone https://github.com/yourusername/issue-tracking-system.git
    cd issue-tracking-system
    ```

2. **Setup Database**
    - Create a database named `issue_tracking`.
    - Run the following SQL commands to create the necessary tables:

    ```sql
    CREATE DATABASE issue_tracking;

    USE issue_tracking;

    CREATE TABLE users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    );

    CREATE TABLE issues (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        description TEXT NOT NULL,
        status ENUM('open', 'in_progress', 'closed') DEFAULT 'open',
        created_by INT,
        assigned_to INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (created_by) REFERENCES users(id),
        FOREIGN KEY (assigned_to) REFERENCES users(id)
    );
    ```

3. **Configure Database Connection**
    - Update `db.php` with your database credentials.

    ```php
    <?php
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "issue_tracking";

    $conn = new mysqli($servername, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    ?>
    ```

4. **Start the Server**
    - Place the project folder in your web server's root directory (e.g., `htdocs` for XAMPP).
    - Start your web server and navigate to `http://localhost/issue-tracking-system`.

## Usage

1. **Register a New User**
    - Go to `http://localhost/issue-tracking-system/register.php`.
    - Fill in the registration form and submit.

2. **Login**
    - Go to `http://localhost/issue-tracking-system/login.php`.
    - Fill in your credentials and submit.

3. **Create an Issue**
    - After logging in, navigate to `Create Issue`.
    - Fill in the issue details and submit.

4. **View Issues**
    - Navigate to `View Issues` to see the list of all issues.
    - Click on `Update` to change the status of an issue.

5. **Logout**
    - Use the `Logout` link in the navigation menu to end your session.

## Project Structure

```
issue-tracking/
├── db.php             // Database connection file
├── footer.php         // Common footer file
├── header.php         // Common header file with navigation
├── index.php          // Main landing page after login
├── login.php          // User login page
├── register.php       // User registration page
├── create_issue.php   // Page to create a new issue
├── view_issues.php    // Page to view all issues
├── update_issue.php   // Page to update issue status
├── logout.php         // User logout script
├── styles.css         // Stylesheet for the project
```

## Database Schema

### users

| Field    | Type         | Description                      |
|----------|--------------|----------------------------------|
| id       | INT          | Primary key, auto-increment      |
| username | VARCHAR(50)  | Username of the user             |
| password | VARCHAR(255) | Password of the user (hashed)    |

### issues

| Field       | Type                           | Description                         |
|-------------|--------------------------------|-------------------------------------|
| id          | INT                            | Primary key, auto-increment         |
| title       | VARCHAR(255)                   | Title of the issue                  |
| description | TEXT                           | Description of the issue            |
| status      | ENUM('open', 'in_progress', 'closed') | Status of the issue                |
| created_by  | INT                            | Foreign key, references `users(id)` |
| assigned_to | INT                            | Foreign key, references `users(id)` |
| created_at  | TIMESTAMP                      | Timestamp when the issue was created|

## Screenshots

[Login Page]![image](https://github.com/alvin0999/issue-tracking-system/assets/141323723/6d1897c8-1851-4726-94c9-20e75cd5cb3e)


[Register Page]![image](https://github.com/alvin0999/issue-tracking-system/assets/141323723/6d0c65c8-8322-4ce7-86cd-04b4577c3e52)


[Create Issue]![image](https://github.com/alvin0999/issue_tracker/assets/141323723/62ad3b1e-ef01-40ca-a453-89b437269b2c)

[View Issues]![image](https://github.com/alvin0999/issue_tracker/assets/141323723/2021fb0f-d3ca-49ad-bb64-d06dbe0d050b)

[Update Issue]![image](https://github.com/alvin0999/issue_tracker/assets/141323723/2c0e7963-ccba-4367-b902-84ee083626fd)

[Updated Issue]![image](https://github.com/alvin0999/issue-tracking-system/assets/141323723/abcb0808-c28b-4260-bb10-b8796d369d43)

