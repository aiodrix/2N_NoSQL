<?php
// Database connection
$servername = "localhost";
$username = "root";  // Change as required
$password = "";      // Change as required
$dbname = "file_counts";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define the directory path
$usersDir = 'users';

// Clear the existing data in the table
$conn->query("TRUNCATE TABLE folders");

// Check if the directory exists
if (is_dir($usersDir)) {
    // Open the users directory
    if ($handle = opendir($usersDir)) {
        // Loop through each folder in the users directory
        while (false !== ($entry = readdir($handle))) {
            // Skip the current and parent directory entries
            if ($entry != '.' && $entry != '..' && is_dir("$usersDir/$entry")) {
                // Count the files in the current folder
                $fileCount = count(glob("$usersDir/$entry/*"));
                // Insert the folder name and file count into the database
                $stmt = $conn->prepare("INSERT INTO folders (folder_name, file_count) VALUES (?, ?)");
                $stmt->bind_param("si", $entry, $fileCount);
                $stmt->execute();
                $stmt->close();
            }
        }
        // Close the directory handle
        closedir($handle);
    }
}

// Query to get the total file count and the folders in descending order
$query = "
    SELECT folder_name, file_count, (file_count / (SELECT SUM(file_count) FROM folders) * 100) AS percentage
    FROM folders
    ORDER BY file_count DESC
";

$result = $conn->query($query);

// Display the results
if ($result->num_rows > 0) {
    echo "<h1>Folders in '$usersDir' Directory</h1>";
    $totalFileCount = 0;

    while ($row = $result->fetch_assoc()) {
        $totalFileCount += $row['file_count'];
    }

    echo "<p>Total number of files: $totalFileCount</p>";
    echo "<ul>";
    $result->data_seek(0); // Reset pointer to the start of the result set

    while ($row = $result->fetch_assoc()) {
        echo "<li><strong>{$row['folder_name']}</strong>: {$row['file_count']} files (" . number_format($row['percentage'], 2) . "%)</li>";
    }
    echo "</ul>";
} else {
    echo "No folders found.";
}

$conn->close();
?>
