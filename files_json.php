<?php
// Specify the directory
$directory = 'files';

// Check if the directory exists
if (!is_dir($directory)) {
    die(json_encode(["error" => "Directory '$directory' does not exist."]));
}

// Get the filename from the GET variable
$search_filename = isset($_GET['filename']) ? $_GET['filename'] : '';

// Get all files in the directory
$files = scandir($directory);

// Remove . and .. from the list
$files = array_diff($files, array('.', '..'));

// Prepare an array to store the results
$results = [];

// Loop through each file
foreach ($files as $file) {
    // Check if the file matches the search term (case-insensitive)
    if ($search_filename === '' || stripos($file, $search_filename) !== false) {
        $filepath = $directory . '/' . $file;
        $filesize = filesize($filepath);
        
        // Add the file information to the results
        $results[] = [
            'filename' => $file,
            'size' => $filesize
        ];
    }
}

// Return the results as JSON
header('Content-Type: application/json');
echo json_encode($results);
?>
