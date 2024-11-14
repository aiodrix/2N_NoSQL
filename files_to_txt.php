<?php
// Specify the directory
$directory = 'files';

// Check if the directory exists
if (!is_dir($directory)) {
    die("Error: Directory '$directory' does not exist.");
}

// Get all files in the directory
$files = scandir($directory);

// Remove . and .. from the list
$files = array_diff($files, array('.', '..'));

// Start HTML output and save to a string
$htmlContent = "<!DOCTYPE html>\n";
$htmlContent .= "<html lang='en'>\n";
$htmlContent .= "<head>\n";
$htmlContent .= "    <meta charset='UTF-8'>\n";
$htmlContent .= "    <meta name='viewport' content='width=device-width, initial-scale=1.0'>\n";
$htmlContent .= "    <title>Files in '{$directory}' Directory</title>\n";
$htmlContent .= "</head>\n";
$htmlContent .= "<body>\n";
$htmlContent .= "    <h1>Files in '{$directory}' Directory</h1>\n";
$htmlContent .= "    <ul>\n";

// Loop through each file
foreach ($files as $file) {
    $filepath = $directory . '/' . $file;
    
    // Append each file as a link to the HTML content
    $htmlContent .= "<a href='{$filepath}' target='_blank'>{$file}</a>\n";
}

// Close the HTML tags
$htmlContent .= "    </ul>\n";
$htmlContent .= "</body>\n";
$htmlContent .= "</html>";

// Save the HTML content to "files.txt"
file_put_contents('files.txt', $htmlContent);

echo "HTML content has been saved to 'files.txt'.";
?>
