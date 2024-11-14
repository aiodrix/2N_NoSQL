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
    weight INT DEFAULT 0,
    source_url VARCHAR(255)
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

// Function to measure latency of a URL
function measure_latency($url) {
    $start = microtime(true);
    $headers = @get_headers($url);
    $end = microtime(true);
    
    if ($headers === false) {
        return null;
    }
    
    return $end - $start;
}

// Function to get filesize from headers
function get_filesize_from_headers($url) {
    $headers = @get_headers($url, 1);
    if ($headers === false) {
        return null;
    }
    
    if (isset($headers['Content-Length'])) {
        return (int)$headers['Content-Length'];
    }
    
    return null;
}

function formatFileSize($bytes) {
    if ($bytes <= 0) return '0 bytes';

    $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    
    // Calculate the size with 2 decimal points
    $size = $bytes / pow(1024, $factor);
    
    // Format the size:
    // - If it's bytes, show no decimal points
    // - If it's KB or higher, show up to 2 decimal points, removing trailing zeros
    if ($factor == 0) {
        // For bytes, show no decimals
        return sprintf("%.0f %s", $size, $units[$factor]);
    } else {
        // For KB and above, show up to 2 decimals
        return sprintf("%.2f %s", $size, $units[$factor]);
    }
}

// Check if 'url' parameter is set in GET request
if (isset($_GET['url']) && filter_var($_GET['url'], FILTER_VALIDATE_URL)) {
    $source_url = $_GET['url'];
    
    // First measure latency for the source URL
    echo "Measuring latency for source URL...<br>";
    $source_latency = measure_latency($source_url);
    
    if ($source_latency === null) {
        die("Failed to access the source URL. Please check if the URL is accessible.");
    }
    
    echo "Source URL latency: " . number_format($source_latency, 4) . " seconds<br><br>";
    
    // Set timeout for file_get_contents
    $ctx = stream_context_create([
        'http' => [
            'timeout' => 30,
            'ignore_errors' => true
        ]
    ]);
    
    // Fetch content of the URL
    $content = @file_get_contents($source_url, false, $ctx);
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
            $absolute_url = resolve_url($source_url, $href);
            if (filter_var($absolute_url, FILTER_VALIDATE_URL)) {
                $links[] = $absolute_url;
            }
        }
    }
    $links = array_unique($links);
    
    // Prepare the statement outside the loop
    $stmt = $conn->prepare("INSERT IGNORE INTO files (url, filename, latency, filesize, source_url) VALUES (?, ?, ?, ?, ?)");
    if ($stmt === false) {
        die("Prepared statement failed: " . $conn->error);
    }
    
    $inserted_count = 0;
    $error_count = 0;
    $duplicate_count = 0;
    
    echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h3>Processing Links:</h3>";
    
    foreach ($links as $link) {
        // Get filesize if possible
        $filesize = get_filesize_from_headers($link);
        
        // Extract filename from link
        $filename = basename(parse_url($link, PHP_URL_PATH));
        if (empty($filename)) {
            $filename = 'index.html';
        }
        
        // Bind parameters and execute
        $stmt->bind_param("ssdis", $link, $filename, $source_latency, $filesize, $source_url);
        
        echo "<div style='margin: 5px 0;'>";
        if (!$stmt->execute()) {
            if ($stmt->errno == 1062) {  // Duplicate entry error
                echo "?? Duplicate link (already in database): $link";
                $duplicate_count++;
            } else {
                echo "? Failed to insert $link: " . $stmt->error;
                $error_count++;
            }
        } else {
            if ($stmt->affected_rows > 0) {
                echo "? Inserted: $link";
                echo "<br>?? Filename: $filename";
                echo "<br>?? Latency: " . number_format($source_latency, 4) . "s";
                if ($filesize !== null) {
                    echo "<br>?? Size: " . number_format($filesize) . " bytes";
                }
                $inserted_count++;
            }
        }
        echo "</div><hr>";
    }
    
    $stmt->close();
    
    echo "</div>";
    echo "<div style='background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin-top: 10px;'>";
    echo "<h3>Summary:</h3>";
    echo "?? Total links found: " . count($links) . "<br>";
    echo "? Successfully inserted: $inserted_count<br>";
    echo "?? Duplicates found: $duplicate_count<br>";
    echo "? Errors encountered: $error_count<br>";
    echo "?? Source URL latency: " . number_format($source_latency, 4) . " seconds";
    echo "</div>";
} else {
    echo "Invalid or missing 'url' parameter. Please provide a valid URL.";
}

$conn->close();
?>

<style>
body {
    font-family: Arial, sans-serif;
    line-height: 1.6;
    margin: 20px;
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

hr {
    border: 0;
    border-top: 1px solid #eee;
    margin: 10px 0;
}

h3 {
    color: #333;
    margin-bottom: 10px;
}
</style>