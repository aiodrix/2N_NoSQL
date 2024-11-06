<?php

// Path to the 'servers' folder
$serversDir = 'servers';

// Check if the 'servers' directory exists
if (!is_dir($serversDir)) {
    die("Directory '$serversDir' does not exist.");
}

// Function to check the URL using cURL and return if it's reachable
function isUrlOnline($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // Timeout after 10 seconds
    curl_exec($ch);
    
    // Check if there was no response or the HTTP status code is an error
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return $httpCode >= 200 && $httpCode < 400;
}

echo "<h2>Checking URLs from files in the 'servers' folder:</h2>";

// Open the directory and scan files
$files = scandir($serversDir);

foreach ($files as $file) {
    if ($file === '.' || $file === '..') {
        continue;
    }

    // Get the full path to the file
    $filePath = $serversDir . DIRECTORY_SEPARATOR . $file;

    if (is_file($filePath)) {
        echo "<p><strong>File:</strong> $file</p>";

        // Get the file content
        $fileContent = file_get_contents($filePath);

        // Check if the content is a valid URL
        if (filter_var($fileContent, FILTER_VALIDATE_URL)) {
            echo "<p>Attempting connection to: <a href='$fileContent' target='_blank'>$fileContent</a></p>";

            // Check if the URL is online
            if (isUrlOnline($fileContent)) {
                echo "<p style='color: green;'>Successfully connected to $fileContent</p>";
            } else {
                echo "<p style='color: red;'>Failed to connect to $fileContent. Deleting file: $file</p>";
                unlink($filePath);  // Delete the file if URL is offline
            }
        } else {
            echo "<p style='color: orange;'>Invalid URL or empty file content.</p>";
        }
    }
}
?>
