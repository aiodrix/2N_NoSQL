<?php

// Define the array of folders to create
$folders = array(
    'clone',
    'downloads',
    'hashes',
    'html',
    'html_users',
    'ip_hash',
    'json_accounts',
    'json_files',
    'messages',
    'pages',
    'servers',
    'thumbs',
    'url_hash',
    'users'
);

// Loop through the array of folders
foreach ($folders as $folder) {
    // Split the folder path into parts
    $parts = explode('/', $folder);
    
    // Initialize the current folder path
    $currentPath = '';
    
    // Loop through the parts of the folder path
    foreach ($parts as $part) {
        // Add the current part to the current path
        $currentPath .= $part . '/';
        
        // Check if the folder exists, if not create it
        if (!file_exists($currentPath)) {
            mkdir($currentPath);
        }
    }
}

?>