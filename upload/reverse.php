<?php

// Folder where the files to be processed are located
//$folderName = "example_folder"; // Adjust to your folder name
//$destinationDir = 'users/' . $folderName;
$linksDir = 'links/';
$linksHashDir = 'links_hash/';

// Create directories if they don't exist
if (!is_dir($linksDir)) {
    mkdir($linksDir, 0777, true);
}
if (!is_dir($linksHashDir)) {
    mkdir($linksHashDir, 0777, true);
}

// List all files in the destination directory
$files = scandir($destinationDir);

// Process each filename as input
foreach ($files as $file) {
    if ($file === '..') {
        continue; // Skip current and parent directory links
    }

    // The filename is used as the input
    $input = $file;

    if ($file === '.') {
        $input = $folderName;        
    }

    // Extract the filename (without URL parsing)
    $filename = basename($input);

    // Decode the filename (in case of encoded characters)
    $decodedString = urldecode($filename);

    // Sanitize the input to keep only alphanumeric characters and spaces
    $sanitizedInput = preg_replace('/[^a-zA-Z0-9]/', ' ', $decodedString);

    // Split the sanitized input into words
    $words = explode(' ', $sanitizedInput);

    // Process each word
    foreach ($words as $word) {
        if (!empty($word) && !is_numeric($word)) {
            // Create the HTML content
            $linkHashValue = $input . $word;
            $linkHash = sha1($linkHashValue);

            // Check if the hash already exists in links_hash
            if (!file_exists($linksHashDir . $linkHash)) {
                // Create the HTML content with a dummy link (as $input is now a filename)
                $htmlContent = "<a href='../$destinationDir/$input' target='_blank'>$input</a><br>";

                $word = strtolower($word);

                // Define the filename for saving the content
                $filename = $linksDir . $word . ".html";

                // Write the HTML content to the file
                $fileHandle = fopen($filename, 'a'); // Open the file for appending
                fwrite($fileHandle, $htmlContent);
                fclose($fileHandle);

                // Write an empty file in links_hash to mark this hash as processed
                $hashFileHandle = fopen($linksHashDir . $linkHash, 'w');
                fwrite($hashFileHandle, "");
                fclose($hashFileHandle);

                echo "Processed and saved HTML for word: $word<br>";
            } else {
                echo "Hash already exists for word: $word. Skipping...<br>";
            }
        }
    }
}

?>
