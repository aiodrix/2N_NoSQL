<?php

function listFilesAndSaveToJson() {
    $serversDir = 'servers';
    $outputFile = 'servers.json';
    $data = [];

    // Check if the 'servers' directory exists
    if (!is_dir($serversDir)) {
        die("Directory '$serversDir' does not exist.");
    }

    // Scan the directory for files
    $files = scandir($serversDir);

    foreach ($files as $file) {
        if ($file === '.' || $file === '..') {
            continue;
        }

        // Get the full path to the file
        $filePath = $serversDir . DIRECTORY_SEPARATOR . $file;

        // Ensure it's a file before processing
        if (is_file($filePath)) {
            // Get the content of the file
            $content = file_get_contents($filePath);
            $data[$file] = $content;
        }
    }

    // Write the data to 'servers.json'
    file_put_contents($outputFile, json_encode($data, JSON_PRETTY_PRINT));
    echo "Content of each file saved successfully to 'servers.json'.";
}

// Run the function
listFilesAndSaveToJson();
?>
