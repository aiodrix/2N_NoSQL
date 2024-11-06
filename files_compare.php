<?php

// Path to the 'servers' folder
$serversDir = 'servers';

// Check if the 'servers' directory exists
if (!is_dir($serversDir)) {
    die("Directory '$serversDir' does not exist.");
}

// Function to check the URL using cURL
function checkUrl($url) {
    // Initialize a cURL session
    $ch = curl_init($url);

    // Set cURL options
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // Timeout after 10 seconds

    // Execute the cURL request
    $response = curl_exec($ch);

    // Check for cURL errors
    if ($response === false) {
        echo "<p style='color: red;'>Failed to connect to $url: " . curl_error($ch) . "</p>";
    } else {
        echo "<p style='color: green;'>Successfully connected to $url</p>";
    }

    // Close the cURL session
    curl_close($ch);
}

// Function to list all files in 'files' directory and check if they are equal to the files at the given URL
function checkFilesAtUrlAndCompare($url) {
    // Path to the 'files' directory
    $filesDir = 'files';
    $maxFileSize = 10 * 1024 * 1024;  // 10 MB in bytes

    // Check if the 'files' directory exists
    if (!is_dir($filesDir)) {
        die("Directory '$filesDir' does not exist.");
    }

    // Open the directory
    $files = scandir($filesDir);

    echo "<h2>Checking if files from 'files' directory exist and are identical at the URL: $url</h2>";

    foreach ($files as $file) {
        // Skip '.' and '..'
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Get the full path to the local file
        $filePath = $filesDir . DIRECTORY_SEPARATOR . $file;

        // Check if it's a regular file
        if (is_file($filePath)) {
            // Check the file size, abort if it's larger than 10 MB
            $fileSize = filesize($filePath);
            if ($fileSize > $maxFileSize) {
                echo "<p style='color: orange;'>File $file is larger than 10MB and will not be checked.</p>";
                continue;
            }

            // Construct the full URL for the file
            $fileUrl = rtrim($url, '/') . '/' . urlencode($file);
            echo "<p><strong>Checking file:</strong> $file at URL: $fileUrl</p>";

            // Use cURL to check if the file exists and download its content
            $ch = curl_init($fileUrl);

            // Set cURL options
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);  // Timeout after 10 seconds
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Follow redirects

            // Execute the cURL request
            $remoteFileContent = curl_exec($ch);

            // Check the HTTP response code
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            // Close the cURL session
            curl_close($ch);

            // Check if the file exists (HTTP 200 OK)
            if ($httpCode == 200) {
                // Read the local file content
                $localFileContent = file_get_contents($filePath);

                // Compare the contents of the local file and the remote file
                if ($remoteFileContent === $localFileContent) {
                    echo "<p style='color: green;'>File $file is identical to the file at $fileUrl</p>";
                } else {
                    echo "<p style='color: red;'>File $file is different from the file at $fileUrl</p>";
                }
            } else {
                echo "<p style='color: red;'>File does not exist at $fileUrl (HTTP Code: $httpCode)</p>";
            }
        }
    }
}

echo "<h2>Attempting to connect to URLs from files in the 'servers' folder:</h2>";

// Open the directory
$files = scandir($serversDir);

foreach ($files as $file) {
    // Skip '.' and '..'
    if ($file === '.' || $file === '..') {
        continue;
    }

    // Get the full path to the file
    $filePath = $serversDir . DIRECTORY_SEPARATOR . $file;

    // Check if it's a regular file
    if (is_file($filePath)) {
        echo "<p><strong>File:</strong> $file</p>";

        // Get the file content
        $fileContent = file_get_contents($filePath);

        // Attempt to connect to the URL (assuming the file contains a URL)
        if (filter_var($fileContent, FILTER_VALIDATE_URL)) {
            echo "<p>Attempting connection to: <a href='$fileContent' target='_blank'>$fileContent</a></p>";
            
            // Use the function to check the URL
            checkUrl($fileContent);
            checkFilesAtUrlAndCompare($fileContent);
        } else {
            echo "<p style='color: orange;'>Invalid URL or empty file content.</p>";
        }
    }
}

?>
