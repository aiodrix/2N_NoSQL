<?php

// Load the sitenames from the external PHP file
$sitename = ['pastebin'];

// Array of TLDs
//$tlds = ['org', 'xyz'];
$tlds = require 'tlds.php';

// Directory for storing URLs
$directory = 'online';
if (!is_dir($directory)) {
    mkdir($directory);
}

$directoryOff = 'offline';
if (!is_dir($directoryOff)) {
    mkdir($directoryOff);
}

foreach ($sitename as $name) {
    foreach ($tlds as $tld) {
        $filename = $directory . '/' . $name . '.' . $tld . '.txt';
        $filenameOff = $directoryOff . '/' . $name . '.' . $tld . '.txt';
        
        // Check if the file already exists
        if (file_exists($filename)) {
            continue; // Skip to the next URL check
        }

        // Check if the file already exists
        if (file_exists($filenameOff)) {
            continue; // Skip to the next URL check
        }

        $url = 'http://' . $name . '.' . $tld;
        $headers = @get_headers($url);
        
        // Check if the URL is reachable
        if ($headers && strpos($headers[0], '200') !== false) {

            echo "$name . $tld is online<br>";
 
            // Save to a text file using fopen
            $file = fopen($filename, 'w');
            if ($file) {
                fwrite($file, "");
                fclose($file);
            }

        } else {

            // Save to a text file using fopen
            $file = fopen($filenameOff, 'w');
            if ($file) {
                fwrite($file, "");
                fclose($file);
            }

        }
    }
}

echo "URL check completed.";
?>
