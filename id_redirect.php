<?php
// Database configuration
$host = 'localhost';
$dbname = 'decenphp';
$user = 'root';
$password = '';

// Connect to the MySQL database
$pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// SQL to create the 'files' table if it doesn't exist
$table_sql = "CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    url VARCHAR(255) NOT NULL UNIQUE,
    filename VARCHAR(255),
    hash VARCHAR(64),
    filesize BIGINT,
    latency FLOAT,
    user VARCHAR(255),
    likes INT DEFAULT 0,
    deslikes INT DEFAULT 0,
    weight INT DEFAULT 0,
    source_url VARCHAR(255)
)";

// Execute the table creation query
$pdo->exec($table_sql);

// Check if 'id' parameter is set in the GET request
if (isset($_GET['id'])) {
    // Retrieve the 'id' from the GET parameter and sanitize it
    $id = intval($_GET['id']);

    // Prepare the query to get the URL for the given ID
    $query = $pdo->prepare("SELECT url FROM files WHERE id = :id LIMIT 1");
    $query->bindParam(':id', $id, PDO::PARAM_INT);
    $query->execute();

    // Check if a record was found
    if ($query->rowCount() > 0) {
        // Fetch the URL and redirect
        $file = $query->fetch(PDO::FETCH_ASSOC);
        header("Location: " . $file['url']);
        exit;
    } else {
        // Handle the case where no file is found for the given ID
        echo "File not found.";
    }
} else {
    echo "No ID specified.";
}
?>
