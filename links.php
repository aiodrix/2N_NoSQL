<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Share servers</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f2f2f2;
            color: #333;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }

        .container {
            background-color: #fff;
            padding: 30px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.1);
            border-radius: 0px;
            text-align: center;
            width: 500px; /* Fixed width for better alignment */
        }

        .form-group {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
        }

        input[type="text"] {
            flex: 1;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            margin-right: 10px; /* Space between input and button */
        }

        button {
            background-color: #555;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 0px;
            font-size: 16px;
            cursor: pointer;
        }

        button:hover {
            background-color: #333;
        }

        i {
            color: #666;
            font-size: 14px;
            margin-top: 10px;
            display: block;
        }

        a {
            text-decoration: none;
            color: #333;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php

function saveContentToFile($input) {
    // Path to the 'servers' directory
    $serversDir = 'servers';

    // Check if the 'servers' directory exists, if not create it
    if (!is_dir($serversDir)) {
        mkdir($serversDir, 0755, true);
    }

    // Generate a SHA-256 hash based on the content of $input
    $filename = hash('sha256', $input) . '.txt';

    // Full path to the file in 'servers' directory
    $filePath = $serversDir . DIRECTORY_SEPARATOR . $filename;

    // Open the file for writing
    $file = fopen($filePath, 'w');

    // Check if file opened successfully
    if ($file) {
        // Write the content of $input to the file
        fwrite($file, $input);
        // Close the file
        fclose($file);
        echo "<Content saved successfully to file: $filename.<br>";
    } else {
        echo "Failed to open file for writing.<br>";
    }
}

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input = $_POST['input'];                    

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
                saveContentToFile($input);

                $parsedUrl = parse_url($input, PHP_URL_HOST);

                $filename = $parsedUrl . "+" . basename($input);
                $decodedString = urldecode($filename);
                $sanitizedInput = preg_replace('/[^a-zA-Z0-9]/', ' ', $decodedString);
                $words = explode(' ', $sanitizedInput);

                // Process each word
                foreach ($words as $word) {
                    if (!empty($word) && !is_numeric($word)) {
                        $linkHashValue = $input . $word;
                        $linkHash = sha1($linkHashValue);

                        if (!file_exists("links_hash/$linkHash")) {
                            $htmlContent = "<a href='$input' target='_blank'>$input</a><br>";
                            $filename = "links/" . $word . ".html";

                            // Write content to the file
                            $file = fopen($filename, 'a');
                            fwrite($file, $htmlContent);
                            fclose($file);

                            // Write content to the links_hash folder
                            $file = fopen("links_hash/$linkHash", 'w');
                            fwrite($file, "");
                            fclose($file);
                        }
                    }
                }

                $redirectTo = "links/" . $word . ".html";
                header("Location: $redirectTo");
                exit();
            } else {
                if (!file_exists("links/$input.html")) {
                    echo "Page not found.";
                    die;
                }

                // Redirect to the corresponding HTML page
                $redirectTo = "links/" . $input . ".html";
                header("Location: $redirectTo");
                exit();
            }
        } else {
            // Display the form and message
            echo '<form method="POST">
                    <div class="form-group">
                        <input type="text" name="input" placeholder="Insert the URL of a server or a category">
                        <button type="submit">Submit</button>
                    </div>
                  </form><br><a href="servers_all.php">All servers list</a>
                  ';
        }
        ?>
    </div>
</body>
</html>
