<?php
// Define the directory where the server files are located
$serversDir = 'servers/';

// Function to get all files in the 'servers' folder
function getServerFiles($directory) {
    // Scan the directory for all files
    $files = array_diff(scandir($directory), array('..', '.'));
    return $files;
}

// Function to read the URL from each file
function getUrlFromFile($filePath) {
    // Read the first line of the file, assuming it contains the URL
    return trim(file_get_contents($filePath));
}

// Function to get the JSON content from a URL
function getJsonFromUrl($url) {
    // Get the content of the given URL
    $jsonContent = @file_get_contents($url);

    // Check if the content was fetched successfully
    if (!$jsonContent) {
        return null; // Return null if the content can't be fetched
    }

    // Decode the JSON content
    $jsonDecoded = json_decode($jsonContent, true);

    // Check if decoding was successful
    if (json_last_error() !== JSON_ERROR_NONE) {
        return null; // Return null if the content is not valid JSON
    }

    return $jsonDecoded;
}

// Function to filter JSON content based on user input
function filterJsonContent($jsonContent, $userInput) {
    // If no user input, return the entire JSON content
    if (empty($userInput)) {
        return $jsonContent;
    }

    // Search the JSON content for the user's input (case-insensitive)
    $filteredContent = [];
    foreach ($jsonContent as $key => $value) {
        if (stripos($key, $userInput) !== false || stripos(json_encode($value), $userInput) !== false) {
            $filteredContent[$key] = $value;
        }
    }

    return $filteredContent;
}

// Main execution
if (isset($_GET['search'])) {
    $userInput = $_GET['search'];

    // Get all server files from the 'servers' folder
    $serverFiles = getServerFiles($serversDir);

    // Initialize an array to store all matching JSON content
    $allMatchingJson = [];

    // Loop through each file, treating the content as a URL
    foreach ($serverFiles as $file) {
        $filePath = $serversDir . $file;
        $url = getUrlFromFile($filePath);

        // Get the JSON content from the URL
        $jsonContent = getJsonFromUrl($url);

        // If valid JSON content was retrieved, filter it
        if ($jsonContent !== null) {
            $matchingJson = filterJsonContent($jsonContent, $userInput);

            // If there are any matching JSON fields, store them along with the file name (URL)
            if (!empty($matchingJson)) {
                $allMatchingJson[$url] = $matchingJson;
            }
        }
    }

    // Display the filtered JSON content
    if (!empty($allMatchingJson)) {
        echo "<h3>JSON content that matches '{$userInput}':</h3>";
        foreach ($allMatchingJson as $url => $jsonData) {
            echo "<h4>From URL: $url</h4>";
            echo "<pre>" . json_encode($jsonData, JSON_PRETTY_PRINT) . "</pre>";
        }
    } else {
        echo "No JSON content matches your search term.";
    }
} else {
    // Show the form for input if it's a GET request
    echo "<form method='get'>
            <label for='search'>Search term:</label>
            <input type='text' id='search' name='search' placeholder='Enter search term' required><br><br>
            <input type='submit' value='Search JSON Content'>
          </form>";
}
?>
