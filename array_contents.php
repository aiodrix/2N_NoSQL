<?php
function listServerFilesContents($directory = 'servers/') {
    $fileContents = []; // Initialize an array to store the contents

    // Check if the directory exists
    if (is_dir($directory)) {
        $files = scandir($directory); // Get all files from the directory

        foreach ($files as $fileName) {
            $filePath = $directory . $fileName;

            // Skip directories and non-files
            if (is_file($filePath)) {
                // Get the content of the file
                $content = file_get_contents($filePath);

                // Store the file's content in the array, using the file name as the key
                $fileContents[$fileName] = $content;
            }
        }
    } else {
        echo "Directory $directory does not exist.";
    }

    return $fileContents; // Return the array with the contents of each file
}

// Example usage
$contents = listServerFilesContents();
print_r($contents); // This will print the array with file names and their contents
?>
