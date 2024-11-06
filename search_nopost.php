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

// Function to get all links from a URL
function getLinksFromUrl($url) {
    // Get the HTML content of the given URL
    $html = @file_get_contents($url);

    // Check if the URL was fetched successfully
    if (!$html) {
        return []; // Return an empty array if the content can't be fetched
    }

    // Create a DOMDocument and load the HTML
    $dom = new DOMDocument();
    // Suppress errors due to invalid HTML structure
    @$dom->loadHTML($html);

    // Get all the anchor tags from the DOM
    $links = $dom->getElementsByTagName('a');

    // Initialize an array to store the extracted links
    $linkList = [];

    // Loop through all the anchor tags and extract the href attribute
    foreach ($links as $link) {
        $href = $link->getAttribute('href');
        if (!empty($href)) {
            // Store the href link in the array
            $linkList[] = $href;
        }
    }

    return $linkList;
}

// Function to filter links based on user input
function filterLinks($links, $userInput) {
    // Array to store the filtered links
    $filteredLinks = [];

    // Loop through the links and check if they contain the user input
    foreach ($links as $link) {
        if (stripos($link, $userInput) !== false) {
            $filteredLinks[] = $link;
        }
    }

    return $filteredLinks;
}

// Main execution
if (isset($_GET['search'])) {
    $userInput = $_GET['search'];

    // Get all server files from the 'servers' folder
    $serverFiles = getServerFiles($serversDir);

    // Initialize an array to store all matching links
    $allMatchingLinks = [];

    // Loop through each file, treating the content as a URL
    foreach ($serverFiles as $file) {
        $filePath = $serversDir . $file;
        $url = getUrlFromFile($filePath);

        // Get all links from the URL
        $allLinks = getLinksFromUrl($url);

        // Filter the links based on the user's search term
        $matchingLinks = filterLinks($allLinks, $userInput);

        // If there are any matching links, store them along with the file name (URL)
        if (!empty($matchingLinks)) {
            $allMatchingLinks[$url] = $matchingLinks;
        }
    }

    // Display the filtered links
    if (!empty($allMatchingLinks)) {
        echo "<h3>Links that match '{$userInput}':</h3>";
        foreach ($allMatchingLinks as $url => $links) {
            echo "<h4>From URL: $url</h4>";
            echo "<ul>";                

            $directory = dirname($url);                  

            foreach ($links as $link) {
                $directoryPath = $directory . '/' . $link; 
                echo "<li><a href='$directoryPath' target='_blank'>$link</a></li>";
            }
            echo "</ul>";
        }
    } else {
        echo "No links match your search term.";
    }
} else {
    echo "<form method='get'>
            <label for='search'>Search term:</label>
            <input type='text' id='search' name='search' placeholder='Enter search term' required><br><br>
            <input type='submit' value='Search Links'>
          </form>";
}
?>
