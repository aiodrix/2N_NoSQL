<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to generate a random date within the past 30 days
function generateRandomDate() {
    $startDate = strtotime("-30 days");
    $endDate = time();
    return date("Y-m-d H:i:s", mt_rand($startDate, $endDate));
}

// Prepare the SQL insert statement for 10 entries
for ($i = 0; $i < 10; $i++) {
    $file_id = rand(1, 5); // Random file_id, assuming file_id ranges from 1 to 5
    $user_ip = "192.168.1." . rand(1, 255); // Random IP address
    $username = "user" . rand(1, 10); // Random username
    $action = rand(0, 1) == 0 ? 'like' : 'dislike'; // Random like or dislike action
    $timestamp = generateRandomDate(); // Generate a random timestamp

    // Insert data into the table
    $sql = "INSERT INTO `likes` (`interaction_hash`, `file_id`, `user_ip`, `username`, `action`, `timestamp`)
            VALUES (MD5(RAND()), $file_id, '$user_ip', '$username', '$action', '$timestamp')";

    if ($conn->query($sql) === TRUE) {
        echo "New record created successfully<br>";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error . "<br>";
    }
}

// Close connection
$conn->close();
?>
