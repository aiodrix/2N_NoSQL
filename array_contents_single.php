<?php 
function listServerFilesContents($directory = 'servers/') {
    $allContents = ""; // Initialize a string to store the contents of all files

    // Check if the directory exists
    if (is_dir($directory)) {
        $files = scandir($directory); // Get all files from the directory

        foreach ($files as $fileName) {
            $filePath = $directory . $fileName;

            // Skip directories and non-files
            if (is_file($filePath)) {
                // Get the content of the file
                $content = file_get_contents($filePath);

                // Concatenate the content of each file with a newline
                $allContents .= $content . "\n";
            }
        }
    } else {
        echo "Directory $directory does not exist.";
    }

    return $allContents; // Return the concatenated contents of all files
}

// Example usage
$contents = listServerFilesContents();
echo $contents; // This will output the concatenated contents of all files
?>
