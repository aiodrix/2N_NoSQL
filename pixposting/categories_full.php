<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Directory Listing</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f0f2f5;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 40px auto;
            background-color: #ffffff;
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
        }
        h1 {
            text-align: center;
            color: #333;
            font-size: 24px;
            margin-bottom: 20px;
        }
        .menu {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            margin-bottom: 20px;
        }
        .menu div {
            padding: 10px 15px;
            margin: 5px;
            background-color: #007bff;
            color: #ffffff;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
        }
        .menu div:hover {
            background-color: #0056b3;
        }
        .subdirectories {
            display: none;
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .subdirectories li {
            margin: 10px 0;
        }
        .subdirectories a {
            text-decoration: none;
            color: #007bff;
            font-weight: 600;
            font-size: 18px;
        }
        .subdirectories a:hover {
            color: #0056b3;
        }
        iframe {
            width: 100%;
            height: 600px;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-top: 20px;
        }
        .not-found {
            text-align: center;
            color: #ff6b6b;
            font-size: 18px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>Directory Listing</h1>
    <div class="menu">
        <?php
        foreach (range('a', 'z') as $letter) {
            echo '<div data-letter="' . $letter . '">' . strtoupper($letter) . '</div>';
        }
        ?>
    </div>
    <?php
    // Set the directory path
    $directoryPath = 'html';
    $directories = [];

    // Open the directory
    if ($handle = opendir($directoryPath)) {
        // Read each entry in the directory
        while (false !== ($entry = readdir($handle))) {
            // Skip the current and parent directory entries
            if ($entry != "." && $entry != "..") {
                // Check if the entry is a directory
                if (is_dir("$directoryPath/$entry")) {
                    $firstLetter = strtolower($entry[0]);
                    $directories[$firstLetter][] = $entry;
                }
            }
        }
        // Close the directory handle
        closedir($handle);
    }

    foreach (range('a', 'z') as $letter) {
        $letterLower = strtolower($letter);
        echo '<ul class="subdirectories" id="subdirs-' . $letterLower . '">';
        if (isset($directories[$letterLower])) {
            foreach ($directories[$letterLower] as $subdir) {
                echo '<li><a href="?folder=' . urlencode($subdir) . '">' . htmlspecialchars($subdir) . '</a></li>';
            }
        }
        echo '</ul>';
    }

    // Check if a folder is selected
    if (isset($_GET['folder'])) {
        $selectedFolder = $_GET['folder'];
        $filePath = "$directoryPath/$selectedFolder/1.html";
        // Check if the file exists
        if (file_exists($filePath)) {
            // Display the content of the file
            echo '<h2>Contents of ' . htmlspecialchars($selectedFolder) . '/1.html:</h2>';
            echo '<iframe src="' . htmlspecialchars($filePath) . '"></iframe>';
        } else {
            echo '<p class="not-found">File not found.</p>';
        }
    }
    ?>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const menuItems = document.querySelectorAll('.menu div');
        menuItems.forEach(item => {
            item.addEventListener('mouseover', function () {
                const letter = this.getAttribute('data-letter');
                const subdirs = document.querySelectorAll('.subdirectories');
                subdirs.forEach(subdir => subdir.style.display = 'none');
                const targetSubdir = document.getElementById('subdirs-' + letter);
                if (targetSubdir) {
                    targetSubdir.style.display = 'block';
                }
            });
        });
    });
</script>

</body>
</html>
