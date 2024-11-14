<?php
// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create the 'files' table if it doesn't exist
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
    weight INT DEFAULT 0
)";
if (!$conn->query($table_sql)) {
    die("Table creation failed: " . $conn->error);
}

// Function to resolve relative URL to absolute URL
function resolve_url($base, $relative) {
    // If it's already an absolute URL, return it
    if (preg_match('/^(http|https):\/\//i', $relative)) {
        return $relative;
    }

    // Parse the base URL
    $base_parts = parse_url($base);
    
    // Get the path up to the last directory
    $base_path = isset($base_parts['path']) ? dirname($base_parts['path']) : '';
    if ($base_path !== '/') {
        $base_path .= '/';
    }

    // Handle different types of relative paths
    if (strpos($relative, '//') === 0) {
        // Protocol-relative URL
        return $base_parts['scheme'] . ':' . $relative;
    } elseif (strpos($relative, '/') === 0) {
        // Root-relative URL
        return $base_parts['scheme'] . '://' . $base_parts['host'] . $relative;
    } else {
        // Relative to current path
        // Remove any ../ and ./ from the path
        $relative = ltrim($relative, './');
        $path = $base_path . $relative;
        
        // Handle ../
        while (strpos($path, '../') !== false) {
            $path = preg_replace('/\/[^\/]+\/\.\.\//', '/', $path);
        }
        
        return $base_parts['scheme'] . '://' . $base_parts['host'] . $path;
    }
}

// Get the actual script path from the server
$script_path = dirname($_SERVER['SCRIPT_NAME']);
$host = $_SERVER['HTTP_HOST'];
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$base_script_url = $protocol . '://' . $host . $script_path;

// Check if 'url' parameter is set in GET request
if (isset($_GET['url']) && filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    $url = $_GET['url'];
    
    // Set timeout for file_get_contents
    $ctx = stream_context_create(['http' => ['timeout' => 30]]);
    
    // Fetch content of the URL
    $content = @file_get_contents($url, false, $ctx);
    if ($content === FALSE) {
        die("Failed to fetch content from the provided URL: " . error_get_last()['message']);
    }
    
    // Create a DOM object
    $dom = new DOMDocument();
    @$dom->loadHTML($content, LIBXML_NOERROR);
    $links = [];
    
    // Extract all href links using DOMDocument
    foreach($dom->getElementsByTagName('a') as $link) {
        if ($href = $link->getAttribute('href')) {
            // Resolve the URL properly
            $absolute_url = resolve_url($url, $href);
            if (filter_var($absolute_url, FILTER_VALIDATE_URL)) {
                $links[] = $absolute_url;
            }
        }
    }
    $links = array_unique($links);
    
    // Prepare the statement outside the loop
    $stmt = $conn->prepare("INSERT IGNORE INTO files (url, filename, latency) VALUES (?, ?, ?)");
    if ($stmt === false) {
        die("Prepared statement failed: " . $conn->error);
    }
    
    $inserted_count = 0;
    $error_count = 0;
    
    foreach ($links as $link) {
        // Calculate latency for each link
        $latency_start = microtime(true);
        $headers = @get_headers($link);
        $latency = microtime(true) - $latency_start;
        
        if ($headers === false) {
            echo "Failed to get headers for: $link <br>";
            $error_count++;
            continue;
        }
        
        // Extract filename from link
        $filename = basename(parse_url($link, PHP_URL_PATH));
        if (empty($filename)) {
            $filename = 'index.html';
        }
        
        // Bind parameters and execute
        $stmt->bind_param("ssd", $link, $filename, $latency);
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {  // Duplicate entry error
                echo "Duplicate link (already in database): $link <br>";
            } else {
                echo "Failed to insert $link: " . $stmt->error . "<br>";
            }
            $error_count++;
        } else {
            if ($stmt->affected_rows > 0) {
                echo "Inserted link: $link with filename: $filename and latency: $latency <br>";
                $inserted_count++;
            }
        }
    }
    
    $stmt->close();
    echo "<br>Summary:<br>";
    echo "Total links found: " . count($links) . "<br>";
    echo "Successfully inserted: $inserted_count<br>";
    echo "Errors encountered: $error_count<br>";
} else {
    echo "Invalid or missing 'url' parameter.";
}

$conn->close();
?>