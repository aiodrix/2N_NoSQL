<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuration
$directory = 'files'; // Directory to search in
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page);
$search_term = '';
$total_items = 0;
$files = [];

// Function to format file size
function formatFileSize($bytes) {
    if ($bytes <= 0) return '0 bytes';
    
    $units = ['bytes', 'KB', 'MB', 'GB', 'TB'];
    $factor = floor((strlen($bytes) - 1) / 3);
    $size = $bytes / pow(1024, $factor);
    
    return $factor == 0 
        ? sprintf("%.0f %s", $size, $units[$factor])
        : sprintf("%.2f %s", $size, $units[$factor]);
}

// Function to get file hash
function getFileHash($filepath) {
    return hash_file('sha256', $filepath);
}

// Function to get all files from directory
function getFiles($dir, $search = '') {
    $files = [];
    $search = strtolower($search);
    
    try {
        // Create directory if it doesn't exist
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile()) {
                $filename = $file->getFilename();
                $filepath = $file->getPathname();
                
                // If there's a search term, check if filename matches
                if (empty($search) || stripos($filename, $search) !== false) {
                    $files[] = [
                        'filename' => $filename,
                        'filepath' => $filepath,
                        'date' => date('Y-m-d H:i:s', $file->getMTime()),
                        'size' => $file->getSize(),
                        'hash' => getFileHash($filepath),
                        'extension' => strtolower(pathinfo($filename, PATHINFO_EXTENSION))
                    ];
                }
            }
        }
    } catch (Exception $e) {
        error_log("Error scanning directory: " . $e->getMessage());
    }
    
    // Sort files by date modified (newest first)
    usort($files, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    return $files;
}

// Get search term from either POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_term = isset($_POST['search']) ? trim($_POST['search']) : '';
} else {
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
}

// Get all matching files
$all_files = getFiles($directory, $search_term);
$total_items = count($all_files);

// Calculate pagination
$total_pages = ceil($total_items / $items_per_page);
$current_page = min($current_page, max(1, $total_pages));
$offset = ($current_page - 1) * $items_per_page;

// Get files for current page
$files = array_slice($all_files, $offset, $items_per_page);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Search</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .main-container {
            background-color: #f8f9fa;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            padding: 2rem;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }
        .file-link {
            text-decoration: none;
            color: inherit;
        }
        .file-link:hover {
            color: #0d6efd;
        }
        .file-row:hover {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <h1 class="text-center mb-4">File Search</h1>
            
            <!-- Search Form -->
            <form method="POST" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search files..." 
                           value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
            
            <?php if ($total_items > 0): ?>
                <div class="table-responsive">
                    <table class="table">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Filename</th>
                                <th>Date Modified</th>
                                <th>Size</th>
                                <th>Hash</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($files as $file): ?>
                                <?php
                                // Set icon class based on file extension
                                switch ($file['extension']) {
                                    case 'pdf':
                                        $icon = 'bi-file-earmark-pdf-fill';
                                        break;
                                    case 'jpg':
                                    case 'jpeg':
                                    case 'png':
                                    case 'gif':
                                        $icon = 'bi-file-earmark-image-fill';
                                        break;
                                    case 'doc':
                                    case 'docx':
                                        $icon = 'bi-file-earmark-word-fill';
                                        break;
                                    case 'txt':
                                        $icon = 'bi-file-earmark-text-fill';
                                        break;
                                    case 'mp3':
                                        $icon = 'bi-file-earmark-music-fill';
                                        break;
                                    case 'mp4':
                                        $icon = 'bi-file-earmark-play-fill';
                                        break;
                                    case 'zip':
                                        $icon = 'bi-file-earmark-zip-fill';
                                        break;
                                    default:
                                        $icon = 'bi-file-earmark-fill';
                                }
                                ?>
                                <tr class="file-row">
                                    <td><i class="bi <?php echo $icon; ?>" style="font-size: 1.5em;"></i></td>
                                    <td>
                                        <a href="<?php echo htmlspecialchars($file['filepath']); ?>" 
                                           class="file-link" target="_blank">
                                            <?php echo htmlspecialchars($file['filename']); ?>
                                        </a>
                                    </td>
                                    <td><?php echo $file['date']; ?></td>
                                    <td><?php echo formatFileSize($file['size']); ?></td>
                                    <td class="text-muted" style="font-size: 0.9em;">
                                        <?php echo substr($file['hash'], 0, 16) . '...'; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                    <nav aria-label="Search results pages">
                        <ul class="pagination justify-content-center">
                            <?php
                            $prev_page = max(1, $current_page - 1);
                            $next_page = min($total_pages, $current_page + 1);
                            ?>
                            
                            <li class="page-item <?php echo $current_page == 1 ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $prev_page; ?>">Previous</a>
                            </li>
                            
                            <?php
                            $start_page = max(1, $current_page - 2);
                            $end_page = min($total_pages, $start_page + 4);
                            $start_page = max(1, $end_page - 4);
                            
                            for ($i = $start_page; $i <= $end_page; $i++): ?>
                                <li class="page-item <?php echo $i == $current_page ? 'active' : ''; ?>">
                                    <a class="page-link" href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $i; ?>">
                                        <?php echo $i; ?>
                                    </a>
                                </li>
                            <?php endfor; ?>
                            
                            <li class="page-item <?php echo $current_page == $total_pages ? 'disabled' : ''; ?>">
                                <a class="page-link" href="?search=<?php echo urlencode($search_term); ?>&page=<?php echo $next_page; ?>">Next</a>
                            </li>
                        </ul>
                    </nav>
                <?php endif; ?>
                
                <p class="text-center">
                    Showing <?php echo ($offset + 1); ?> to 
                    <?php echo min($offset + $items_per_page, $total_items); ?> 
                    of <?php echo $total_items; ?> results
                </p>
            <?php else: ?>
                <div class="alert alert-info">
                    <?php echo empty($search_term) 
                        ? 'No files found in the directory.' 
                        : 'No files found matching "' . htmlspecialchars($search_term) . '"'; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>