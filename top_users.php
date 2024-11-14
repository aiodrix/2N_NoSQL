<?php
// Database connection settings
$host = 'localhost';
$dbname = 'decenphp';
$username = 'root';
$password = '';

// Initialize the $conn variable for global use
try {
    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}

// Function to rank users based on like growth and total investment
function rankUsers() {
    global $conn;

    $sql = "
        SELECT
            l.username,
            SUM(i.amount) AS total_invested,
            (
                SELECT COUNT(*)
                FROM likes ll
                WHERE ll.file_id IN (SELECT id FROM files WHERE file_hash = i.file_hash)
                  AND ll.username = l.username
            ) AS total_likes_growth
        FROM invest i
        JOIN likes l ON l.user_ip = i.user_id
        GROUP BY l.username
        ORDER BY total_likes_growth DESC, total_invested DESC
    ";

    $result = $conn->query($sql);

    if (!$result) {
        die("Query failed: " . $conn->error);
    }

    $rankings = [];
    while ($row = $result->fetch_assoc()) {
        $rankings[] = [
            'username' => $row['username'],
            'total_invested' => $row['total_invested'],
            'total_likes_growth' => $row['total_likes_growth']
        ];
    }

    return $rankings;
}

// Example usage
$rankings = rankUsers();

foreach ($rankings as $rank) {
    echo "Username: " . $rank['username'] . "\n";
    echo "Total Invested: " . $rank['total_invested'] . "\n";
    echo "Total Likes Growth: " . $rank['total_likes_growth'] . "\n\n";
}

// Close the database connection
$conn->close();
