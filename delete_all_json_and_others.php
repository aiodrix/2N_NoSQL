<?php
// Directory to check
$directory = 'files/';

if (!is_dir($directory)) {
    die("Directory does not exist.");
}

// Function to get the SHA-256 hash of a file
function getFileSHA256($filePath) {
    return hash_file('sha256', $filePath);
}

// Scan the directory for files
$files = scandir($directory);

foreach ($files as $fileName) {
    // Skip directories
    if (is_dir($directory . $fileName)) {
        continue;
    }

    $filePath = $directory . $fileName;
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION); // Get file extension
    $fileBaseName = pathinfo($fileName, PATHINFO_FILENAME); // Get file name without extension

    // Get the SHA-256 hash of the file
    $fileHash = getFileSHA256($filePath);

    // Check if the file name (without extension) matches the SHA-256 hash
    if ($fileBaseName !== $fileHash) {
        // If it doesn't match, delete the file
        if (unlink($filePath)) {
            echo "Deleted: $fileName (did not match hash)<br>";
        } else {
            echo "Error deleting: $fileName<br>";
        }
    } else {
        echo "File $fileName matches its SHA-256 hash.<br>";
    }
}
?>
