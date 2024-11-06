<?php
// Function to get all links from a URL
function getLinksFromUrl($url) {
    // Get the HTML content of the given URL
    $html = file_get_contents($url);

    // Check if the URL was fetched successfully
    if (!$html) {
        die("Could not retrieve content from URL: $url");
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
if (isset($_GET['url']) && isset($_GET['search'])) {
    $url = $_GET['url'];
    $userInput = $_GET['search'];

    // Get all links from the provided URL
    $allLinks = getLinksFromUrl($url);

    // Filter the links based on the user's search term
    $matchingLinks = filterLinks($allLinks, $userInput);

    // Display the filtered links
    if (!empty($matchingLinks)) {
        echo "<h3>Links that match '{$userInput}' from '{$url}':</h3>";
        echo "<ul>";
        foreach ($matchingLinks as $link) {
            echo "<li><a href='$link' target='_blank'>$link</a></li>";
        }
        echo "</ul>";
    } else {
        echo "No links match your search term.";
    }
} else {
    echo "<form method='get'>
            <label for='url'>URL:</label>
            <input type='text' id='url' name='url' placeholder='https://example.com' required><br><br>
            <label for='search'>Search term:</label>
            <input type='text' id='search' name='search' placeholder='Enter search term' required><br><br>
            <input type='submit' value='Search Links'>
          </form>";
}
?>
