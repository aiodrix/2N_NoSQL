<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['url'])) {
    $submittedUrl = filter_var($_POST['url'], FILTER_VALIDATE_URL);
    if ($submittedUrl) {
        $content = file_get_contents($submittedUrl);
        if ($content) {
            $lines = explode("\n", $content);
            foreach ($lines as $line) {
                $line = trim($line);
                if (!empty($line) && filter_var($line, FILTER_VALIDATE_URL)) {
                    saveUrlContent($line);
                }
            }
            echo "<p class='success'>Files have been downloaded and saved.</p>";
        } else {
            echo "<p class='error'>Could not retrieve content from the provided URL.</p>";
        }
    } else {
        echo "<p class='error'>Invalid URL.</p>";
    }
}

function saveUrlContent($url) {
    $parsedUrl = parse_url($url);
    $pathSegments = explode('/', trim($parsedUrl['path'], '/'));
    if (count($pathSegments) < 2) {
        return;
    }
    $penultimateSegment = $pathSegments[count($pathSegments) - 2];
    $folderPath = __DIR__ . "/categories/$penultimateSegment";
    if (!is_dir($folderPath)) {
        mkdir($folderPath, 0777, true);
    }
    $fileName = end($pathSegments);
    $fileContent = file_get_contents($url);

    if (file_exists("$folderPath/$fileName")) {
      return;
    }

    if ($fileContent) {
       file_put_contents("$folderPath/$fileName", $fileContent); 

       if (!file_exists("$folderPath/$fileName")) {

           $file = fopen("$folderPath/$fileName", 'w');
           fwrite($file, $fileContent);
           fclose($file);
        }
    }

}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URL Content Downloader</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: bold;
        }
        input[type="url"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        button {
            background-color: #5cb85c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            width: 100%;
        }
        button:hover {
            background-color: #4cae4c;
        }
        .success {
            color: #5cb85c;
            text-align: center;
            margin-top: 20px;
        }
        .error {
            color: #d9534f;
            text-align: center;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <h1>Submit a URL</h1>
    <form method="post" action="">
        <label for="url">URL:</label>
        <input type="url" id="url" name="url" required>
        <button type="submit">Submit</button>
    </form>
</body>
</html>