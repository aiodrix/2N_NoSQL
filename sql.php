<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Create a new connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the 'files' table if it doesn't exist
$tableQuery = "
    CREATE TABLE IF NOT EXISTS files (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filehash VARCHAR(64) UNIQUE NOT NULL,
        weight FLOAT NOT NULL,
        user VARCHAR(50) NOT NULL
    )
";
if ($conn->query($tableQuery) === TRUE) {
    echo "Table 'files' created or already exists.<br>";
} else {
    die("Error creating table: " . $conn->error);
}

// Insert sample data (optional)
$sampleData = [
    ['filehash' => hash('sha256', 'file1'), 'weight' => 1.2, 'user' => 'Alice'],
    ['filehash' => hash('sha256', 'file2'), 'weight' => 0.8, 'user' => 'Bob'],
    ['filehash' => hash('sha256', 'file3'), 'weight' => 1.5, 'user' => 'Alice'],
    ['filehash' => hash('sha256', 'file4'), 'weight' => 2.3, 'user' => 'Charlie']
];

foreach ($sampleData as $data) {
    $insertQuery = $conn->prepare("INSERT IGNORE INTO files (filehash, weight, user) VALUES (?, ?, ?)");
    $insertQuery->bind_param("sds", $data['filehash'], $data['weight'], $data['user']);
    $insertQuery->execute();
}

// Function to calculate the user percentages
function getUserFilePercentage($conn) {
    // Query to count the total number of files
    $totalQuery = "SELECT COUNT(*) AS total_files FROM files";
    $result = $conn->query($totalQuery);
    $totalFiles = $result->fetch_assoc()['total_files'];

    // Query to count files for each user
    $userQuery = "SELECT user, COUNT(*) AS user_files FROM files GROUP BY user";
    $result = $conn->query($userQuery);

    // Calculate and display the percentage of files for each user
    echo "<table border='1'>";
    echo "<tr><th>User</th><th>File Count</th><th>Percentage of Total Files</th></tr>";

    while ($row = $result->fetch_assoc()) {
        $user = $row['user'];
        $userFiles = $row['user_files'];
        $percentage = ($userFiles / $totalFiles) * 100;

        echo "<tr>";
        echo "<td>" . htmlspecialchars($user) . "</td>";
        echo "<td>" . htmlspecialchars($userFiles) . "</td>";
        echo "<td>" . number_format($percentage, 2) . "%</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Display user file percentages
getUserFilePercentage($conn);

// Close the database connection
$conn->close();
?>
