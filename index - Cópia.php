<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection settings
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "decenphp";

// Create database connection
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set default values
$items_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$current_page = max(1, $current_page); // Ensure page is at least 1
$search_term = '';
$total_items = 0;
$results = [];

// Handle like/dislike actions
if (isset($_GET['action']) && isset($_GET['id'])) {
    // Create likes table if it doesn't exist
    $create_table_sql = "CREATE TABLE IF NOT EXISTS `likes` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `interaction_hash` VARCHAR(64) NOT NULL,
        `file_id` INT NOT NULL,
        `user_ip` VARCHAR(45) NOT NULL,
        `username` VARCHAR(255) NOT NULL,
        `action` ENUM('like', 'dislike') NOT NULL,
        `timestamp` DATETIME NOT NULL,
        UNIQUE KEY `unique_interaction` (`interaction_hash`),
        FOREIGN KEY (`file_id`) REFERENCES `files`(`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    if (!$conn->query($create_table_sql)) {
        die("Error creating likes table: " . $conn->error);
    }
    
    $id = (int)$_GET['id'];
    $action = $_GET['action'];
    
    // Get user IP address
    $user_ip = $_SERVER['REMOTE_ADDR'];
    
    // Get current user from session (if logged in)
    $current_user = isset($_SESSION['user']) ? $_SESSION['user'] : 'guest';
    
    // First, get the filename for the given id
    $filename_sql = "SELECT filename FROM files WHERE id = ?";
    $stmt = $conn->prepare($filename_sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $filename_result = $stmt->get_result();
    $file_data = $filename_result->fetch_assoc();
    $stmt->close();
    
    if ($file_data) {
        // Create unique hash for this user+file combination
        $unique_hash = hash('sha256', $user_ip . $current_user . $file_data['filename']);
        
        // Check if this user has already liked/disliked this file
        $check_sql = "SELECT * FROM likes WHERE interaction_hash = ?";
        $stmt = $conn->prepare($check_sql);
        $stmt->bind_param("s", $unique_hash);
        $stmt->execute();
        $result = $stmt->get_result();
        $stmt->close();
        
        if ($result->num_rows == 0) {
            // Start transaction
            $conn->begin_transaction();
            
            try {
                // Insert the interaction record
                $insert_sql = "INSERT INTO likes (interaction_hash, file_id, user_ip, username, action, timestamp) 
                             VALUES (?, ?, ?, ?, ?, NOW())";
                $stmt = $conn->prepare($insert_sql);
                $stmt->bind_param("sisss", $unique_hash, $id, $user_ip, $current_user, $action);
                $stmt->execute();
                $stmt->close();
                
                // Update the files table
                if ($action === 'like') {
                    $update_sql = "UPDATE files SET likes = likes + 1 WHERE id = ?";
                } elseif ($action === 'dislike') {
                    $update_sql = "UPDATE files SET deslikes = deslikes + 1 WHERE id = ?";
                }
                
                $stmt = $conn->prepare($update_sql);
                $stmt->bind_param("i", $id);
                $stmt->execute();
                $stmt->close();
                
                // Commit transaction
                $conn->commit();
            } catch (Exception $e) {
                // Rollback transaction on error
                $conn->rollback();
                error_log("Error processing like/dislike: " . $e->getMessage());
            }
        }
        
        // Redirect back to remove the action from URL
        header("Location: " . strtok($_SERVER["REQUEST_URI"], '?') . 
               (isset($_GET['search']) ? "?search=" . urlencode($_GET['search']) : "") .
               (isset($_GET['page']) ? "&page=" . $_GET['page'] : ""));
        exit();
    }
}

// Get search term from either POST or GET
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $search_term = isset($_POST['search']) ? trim($_POST['search']) : '';
} else {
    $search_term = isset($_GET['search']) ? trim($_GET['search']) : '';
}

if (!empty($search_term)) {
    // First, get total count for pagination
    $count_sql = "SELECT COUNT(*) as total FROM files 
                  WHERE filename LIKE ? OR hash LIKE ?";
    $stmt = $conn->prepare($count_sql);
    if ($stmt === false) {
        die("Count prepare failed: " . $conn->error);
    }
    
    $search_pattern = "%{$search_term}%";
    $stmt->bind_param("ss", $search_pattern, $search_pattern);
    $stmt->execute();
    $count_result = $stmt->get_result();
    $total_items = $count_result->fetch_assoc()['total'];
    $stmt->close();
    
    // Calculate total pages and ensure current page is valid
    $total_pages = ceil($total_items / $items_per_page);
    $current_page = min($current_page, max(1, $total_pages));
    
    // Calculate offset for pagination
    $offset = ($current_page - 1) * $items_per_page;
    
    // Prepare and execute the main search query
    $search_sql = "SELECT * FROM files 
                   WHERE filename LIKE ? OR hash LIKE ? 
                   ORDER BY date DESC 
                   LIMIT ? OFFSET ?";
    
    $stmt = $conn->prepare($search_sql);
    if ($stmt === false) {
        die("Search prepare failed: " . $conn->error);
    }
    
    $stmt->bind_param("ssii", $search_pattern, $search_pattern, $items_per_page, $offset);
    $stmt->execute();
    $results = $stmt->get_result();
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="screens/logo.jpg" type="image/jpeg">
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
        .auth-links {
            margin-bottom: 1rem;
        }
        .like-btn, .dislike-btn {
            text-decoration: none;
            color: #6c757d;
            padding: 0.375rem 0.75rem;
            border-radius: 0.25rem;
            transition: all 0.2s;
        }
        .like-btn:hover {
            color: #198754;
            background-color: #e8f5e9;
        }
        .dislike-btn:hover {
            color: #dc3545;
            background-color: #fef1f2;
        }
        .count-badge {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="main-container">
            <h1 class="text-center mb-4"><a href='index.php' style='color: #333; text-decoration: none;'>File Search</a></h1>
            
            <!-- Auth Links -->
            <div class="auth-links d-flex justify-content-end gap-3">
                <a href="indexer.php" class="btn btn-outline-primary">Insert</a>
                <a href="menu.html" class="btn btn-primary">Menu</a>
            </div>
            
            <!-- Search Form -->
            <form method="POST" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" 
                           placeholder="Search by filename or hash..." 
                           value="<?php echo htmlspecialchars($search_term); ?>">
                    <button type="submit" class="btn btn-primary">Search</button>
                </div>
            </form>
            
            <?php if (!empty($search_term)): ?>
                <!-- Search Results -->
                <h2>Results</h2>
                <?php if ($total_items > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th>Type</th>
                                    <th>Filename</th>
                                    <th>Date</th>
                                    <th>Comment</th>
                                    <th>URL</th>
                                    <th>Size</th>
                                    <th class="text-center">Likes</th>
                                    <th class="text-center">Dislikes</th>
                                </tr>
                            </thead>
<tbody>
    <?php while ($row = $results->fetch_assoc()): ?>
        <?php
        // Extract file extension from the filename
        $fileExtension = pathinfo($row['filename'], PATHINFO_EXTENSION);
        
        // Set icon class based on file extension
switch (strtolower($fileExtension)) {
    case 'pdf':
        $icon = 'bi bi-file-earmark-pdf-fill'; // PDF icon
        break;
    case 'jpg':
    case 'jpeg':
    case 'png':
    case 'gif':
        $icon = 'bi bi-file-earmark-image-fill'; // Image icon
        break;
    case 'doc':
    case 'docx':
        $icon = 'bi bi-file-earmark-word-fill'; // Word doc icon
        break;
    case 'txt':
        $icon = 'bi bi-file-earmark-text-fill'; // Text file icon
        break;
    case 'mp3':
        $icon = 'bi bi-file-earmark-music-fill'; // MP3 icon
        break;
    case 'mp4':
        $icon = 'bi bi-file-earmark-play-fill'; // MP4 icon
        break;
    case 'zip':
        $icon = 'bi bi-file-earmark-zip-fill'; // ZIP icon
        break;
    default:
        $icon = 'bi bi-file-earmark-fill'; // Default file icon
        break;
}

        ?>
        <tr>
            <!-- New column for file icon -->
            <td class="text-center">
                <i class="<?php echo $icon; ?>" style="font-size: 1.5em;"></i>
            </td>
            <td><?php echo htmlspecialchars($row['filename']); ?></td>
            <td><?php echo htmlspecialchars($row['date']); ?></td>
            
            <td><?php $filename_entry = !empty($row['hash']) ? $row['hash'] : $row['filename']; echo "<a href='messages.php?hash=" . htmlspecialchars($filename_entry) . "'>Open</a>" ; ?></td>
            <td>
                <a href="<?php echo htmlspecialchars($row['url']); ?>" 
                   target="_blank" class="text-truncate d-inline-block" 
                   style="max-width: 200px;">
                    <?php echo htmlspecialchars($row['url']); ?>
                </a>
            </td>
            <td><?php echo $row['filesize'] ? number_format($row['filesize']) . ' bytes' : 'N/A'; ?></td>
            <td class="text-center">
                <a href="?action=like&id=<?php echo $row['id']; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page; ?>" 
                   class="like-btn">
                    <i class="bi bi-hand-thumbs-up"></i>
                    <span class="count-badge"><?php echo $row['likes']; ?></span>
                </a>
            </td>
            <td class="text-center">
                <a href="?action=dislike&id=<?php echo $row['id']; ?>&search=<?php echo urlencode($search_term); ?>&page=<?php echo $current_page; ?>" 
                   class="dislike-btn">
                    <i class="bi bi-hand-thumbs-down"></i>
                    <span class="count-badge"><?php echo $row['deslikes']; ?></span>
                </a>
            </td>
        </tr>
    <?php endwhile; ?>
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
                    <div class="alert alert-info">No results found for "<?php echo htmlspecialchars($search_term); ?>"</div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<div align='center'>DecenPHP 1.4</div>
</body>
</html>
<?php
$conn->close();
?>