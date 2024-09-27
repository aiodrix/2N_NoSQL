<?php
// Set up directory path
$directory = 'links/';

// Set up pagination variables
$filesPerPage = 10; // Change this to set how many files per page
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $filesPerPage;

// Get files from the directory
$files = array_diff(scandir($directory), array('.', '..'));
$files = array_values($files); // Reindex array

// Search functionality
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';
if ($searchTerm) {
    $files = array_filter($files, function ($file) use ($searchTerm) {
        return stripos($file, $searchTerm) !== false;
    });
    $files = array_values($files); // Reindex after filtering
}

// Pagination logic
$totalFiles = count($files);
$totalPages = ceil($totalFiles / $filesPerPage);
$filesToShow = array_slice($files, $start, $filesPerPage);

// Handle autocomplete requests
if (isset($_GET['autocomplete'])) {
    $term = $_GET['term'];
    $suggestions = array_filter($files, function($file) use ($term) {
        return stripos($file, $term) !== false;
    });
    echo json_encode(array_values($suggestions));
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Listing</title>
    <style>
        /* Global Styles */
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7f7f7;
            color: #333;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .container {
            max-width: 900px;
            width: 100%;
            padding: 30px;
            background-color: #fff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            text-align: center;
        }

        h1 {
            font-size: 28px;
            font-weight: 600;
            color: #4a4a4a;
            margin-bottom: 30px;
            text-transform: uppercase;
            letter-spacing: 1.2px;
        }

        /* Search Bar */
        form {
            display: flex;
            justify-content: center;
            position: relative;
            margin-bottom: 30px;
        }

        input[type="text"] {
            width: 350px;
            padding: 12px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background-color: #f7f7f7;
            color: #4a4a4a;
            outline: none;
            transition: border-color 0.3s;
        }

        input[type="text"]:focus {
            border-color: #999;
        }

        button {
            padding: 12px 18px;
            background-color: #666;
            color: white;
            border: none;
            border-radius: 5px;
            margin-left: 10px;
            cursor: pointer;
            font-size: 16px;
            transition: background-color 0.3s;
        }

        button:hover {
            background-color: #555;
        }

        /* File List */
        ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        li {
            padding: 12px 0;
            border-bottom: 1px solid #e0e0e0;
        }

        a {
            color: #666;
            text-decoration: none;
            font-size: 18px;
            font-weight: 500;
            transition: color 0.3s;
        }

        a:hover {
            color: #333;
        }

        /* Pagination */
        .pagination {
            margin-top: 20px;
        }

        .pagination a {
            padding: 8px 16px;
            margin: 0 5px;
            background-color: #666;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s;
        }

        .pagination a:hover {
            background-color: #555;
        }

        /* Autocomplete Suggestions */
        #suggestions {
            position: absolute;
            top: 48px;
            left: 0;
            width: 100%;
            background-color: #fff;
            border: 1px solid #ccc;
            border-radius: 5px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            z-index: 1000;
            max-height: 200px;
            overflow-y: auto;
        }

        .suggestion {
            padding: 10px;
            cursor: pointer;
            font-size: 16px;
            color: #555;
            border-bottom: 1px solid #eee;
        }

        .suggestion:hover {
            background-color: #f0f0f0;
        }
    </style>
    <script>
        // JavaScript for Autocomplete suggestions
        function fetchSuggestions() {
            let searchTerm = document.getElementById('search').value;
            if (searchTerm.length > 1) {
                fetch('?autocomplete=true&term=' + searchTerm)
                    .then(response => response.json())
                    .then(data => {
                        let suggestions = document.getElementById('suggestions');
                        suggestions.innerHTML = '';
                        data.forEach(file => {
                            let option = document.createElement('div');
                            option.classList.add('suggestion');
                            option.innerHTML = file.replace('.html', '');
                            option.onclick = function() {
                                document.getElementById('search').value = file.replace('.html', '');
                                suggestions.innerHTML = '';
                            };
                            suggestions.appendChild(option);
                        });
                    });
            } else {
                document.getElementById('suggestions').innerHTML = '';
            }
        }
    </script>
</head>
<body>

<div class="container">
    <h1>File Listing</h1>

    <form method="GET">
        <input type="text" id="search" name="search" placeholder="Search files..." onkeyup="fetchSuggestions()" autocomplete="off" value="<?= htmlspecialchars($searchTerm) ?>">
        <div id="suggestions"></div>
        <button type="submit">Search</button>
    </form>

    <ul>
        <?php if (empty($filesToShow)): ?>
            <li>No files found.</li>
        <?php else: ?>
            <?php foreach ($filesToShow as $file): ?>
                <?php
                // Remove '.html' extension from filenames
                $displayFileName = preg_replace('/\.html$/', '', $file);
                ?>
                <li>
                    <a href="<?= $directory . $file ?>" target="_blank"><?= htmlspecialchars($displayFileName) ?></a>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <div class="pagination">
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?= $i ?>&search=<?= htmlspecialchars($searchTerm) ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
</div>

</body>
</html>