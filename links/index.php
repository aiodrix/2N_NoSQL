<?php

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = $_POST['input']; // Assume the POST variable is named 'input'

    // Create the folder if it doesn't exist
    if (!file_exists("links")) {
        mkdir("links", 0777, true);
    }

    // Create the folder if it doesn't exist
    if (!file_exists("links_hash")) {
        mkdir("links_hash", 0777, true);
    }

    // Validate if the input is a URL
    if (filter_var($input, FILTER_VALIDATE_URL)) {
        // Replace all non-alphanumeric characters with a space
        $sanitizedInputSiteName = preg_replace('/[^a-zA-Z0-9]/', ' ', $input);
        
        // Explode the string into an array of words
        $wordsSiteName = explode(' ', $sanitizedInput);
      
        $siteName = $wordsSiteName[3];

        $filename = $siteName . basename($input);

        $decodedString = urldecode($filename);

        $sanitizedInput = preg_replace('/[^a-zA-Z0-9]/', ' ', $decodedString);
     
        $words = explode(' ', $sanitizedInput);         

        // Process each word
        foreach ($words as $word) {

            if (!empty($word) && !is_numeric($word)) {
                // Create the HTML content

                $linkHashValue = $input . $word;
                $linkHash = sha1($linkHashValue);

                if (!file_exists("links_hash/$linkHash")){

                    $htmlContent = "<a href='$input' target='_blank'>$input</a><br>";
                
                    // Define the filename
                    $filename = "links/" . $word . ".html";
                
                    // Write the content to the file
                    $file = fopen($filename, 'a'); // Open the file for writing

               
                    
fwrite($file, $htmlContent);
                    fclose($file);

                    // Write the content to the file
                    $file = fopen("links_hash/$linkHash", 'w'); // Open the file for writing

               
                    
fwrite($file, "");
                    fclose($file);
                } 
            }
        }

        // The input is not a link, redirect to the corresponding HTML page
        $redirectTo = "links/" . $word . ".html";
        header("Location: $redirectTo");
        exit();

    } else {
        // The input is not a link, redirect to the corresponding HTML page
        $redirectTo = "links/" . $input . ".html";
        header("Location: $redirectTo");
        exit();
    }
} else {
    // If the request is not POST, display a simple form
    echo '<form method="POST">
            <input type="text" name="input" placeholder="Enter a link or a word">
            <button type="submit">Submit</button>
          </form><br><i>Insert a text for search or a link to be inserted in various categories pages.</i>';
}
