<?php
$servername = "localhost";
$username = "root";
$password = "";

// Create connection to MySQL (without database selected yet)
$conn = new mysqli($servername, $username, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create Database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS Cafe";
if ($conn->query($sql) === TRUE) {
    echo "Database 'Cafe' created successfully or already exists.<br>";
} else {
    die("Error creating database: " . $conn->error);
}

// Select the database
$conn->select_db("Cafe");

// Read schema.sql file
$schema_sql = file_get_contents('schema.sql');

// Execute multi query for schema
if ($conn->multi_query($schema_sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Check if there are more result sets
    } while ($conn->next_result());
    echo "Tables created successfully.<br>";
} else {
    echo "Error creating tables: " . $conn->error . "<br>";
}

// Insert Default Users (Admin and Receptionists)
// Check if users exist first to avoid simple duplicate errors if run multiple times
$check_users = "SELECT * FROM users";
$result = $conn->query($check_users);

if ($result->num_rows == 0) {
    // Basic password hashing logic can be added here, but keeping it simple for now as requested
    $sql_users = "INSERT INTO users (username, password, role) VALUES 
    ('admin', 'admin123', 'admin'),
    ('reception1', 'pass1', 'receptionist'),
    ('reception2', 'pass2', 'receptionist')";

    if ($conn->query($sql_users) === TRUE) {
        echo "Default users inserted successfully.<br>";
    } else {
        echo "Error inserting default users: " . $conn->error . "<br>";
    }
} else {
    echo "Users already exist. Skipping default user insertion.<br>";
}

$conn->close();
?>
