<?php

// Load the sitenames from the external PHP file
$sitename = ['paste'];

// Array of TLDs
$tlds = require 'tlds.php';

// Directories for storing URLs
$directories = ['online' => 'online', 'offline' => 'offline'];
foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true); // Ensure permission and recursive creation
    }
}

// Function to handle file creation
function saveToFile($filename) {
    $file = fopen($filename, 'w');
    if ($file) {
        fwrite($file, ""); // Empty content, just ensuring file creation
        fclose($file);
    } else {
        echo "Error: Unable to create file $filename.<br>";
    }
}

// Iterate over sitenames and TLDs
foreach ($sitename as $name) {
    foreach ($tlds as $tld) {
        $filenameOnline = $directories['online'] . '/' . $name . '.' . $tld . '.txt';
        $filenameOffline = $directories['offline'] . '/' . $name . '.' . $tld . '.txt';

        // Skip if either online or offline file already exists
        if (file_exists($filenameOnline) || file_exists($filenameOffline)) {
            continue; // Skip to the next URL check
        }

        $url = 'http://' . $name . '.' . $tld;
        $headers = @get_headers($url);

        // Check if the URL is reachable
        if ($headers && strpos($headers[0], '200') !== false) {
            echo "$name.$tld is online<br>";
            saveToFile($filenameOnline);
        } else {
            echo "$name.$tld is offline or unreachable<br>";
            saveToFile($filenameOffline);
        }
    }
}

echo "URL check completed.";

?>
