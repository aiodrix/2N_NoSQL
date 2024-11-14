<?php
session_start();

// Database configuration
$db_config = [
    'host' => 'localhost',
    'username' => 'root',
    'password' => '',
    'database' => 'decenphp'
];

class PostsSearch {
    private $conn;
    private $itemsPerPage = 10;
    
    public function __construct($db_config) {
        $this->conn = new mysqli(
            $db_config['host'],
            $db_config['username'],
            $db_config['password'],
            $db_config['database']
        );

        if ($this->conn->connect_error) {
            throw new Exception("Connection failed: " . $this->conn->connect_error);
        }

        $this->conn->set_charset("utf8mb4");
    }

    public function search($params) {
        $conditions = [];
        $values = [];
        $types = '';

        // Build search conditions
        if (!empty($params['query'])) {
            $searchTerm = "%{$params['query']}%";
            $conditions[] = "(title LIKE ? OR description LIKE ?)";
            $values[] = $searchTerm;
            $values[] = $searchTerm;
            $types .= 'ss';
        }

        if (!empty($params['category'])) {
            $conditions[] = "category = ?";
            $values[] = $params['category'];
            $types .= 's';
        }

        if (!empty($params['user'])) {
            $conditions[] = "user_id = ?";
            $values[] = $params['user'];
            $types .= 's';
        }

        // Calculate pagination
        $page = max(1, intval($params['page'] ?? 1));
        $offset = ($page - 1) * $this->itemsPerPage;

        // Build the query
        $whereClause = !empty($conditions) ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        // Count total results
        $countQuery = "SELECT COUNT(*) as total FROM posts $whereClause";
        $stmt = $this->conn->prepare($countQuery);
        if (!empty($values)) {
            $stmt->bind_param($types, ...$values);
        }
        $stmt->execute();
        $totalResults = $stmt->get_result()->fetch_assoc()['total'];
        $totalPages = ceil($totalResults / $this->itemsPerPage);

        // Get results
        $query = "SELECT * FROM posts $whereClause 
                 ORDER BY created_at DESC 
                 LIMIT ? OFFSET ?";
        
        $stmt = $this->conn->prepare($query);
        if (!empty($values)) {
            $values[] = $this->itemsPerPage;
            $values[] = $offset;
            $types .= 'ii';
            $stmt->bind_param($types, ...$values);
        } else {
            $stmt->bind_param('ii', $this->itemsPerPage, $offset);
        }
        
        $stmt->execute();
        $results = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

        return [
            'results' => $results,
            'totalResults' => $totalResults,
            'currentPage' => $page,
            'totalPages' => $totalPages
        ];
    }

    public function getCategories() {
        $stmt = $this->conn->prepare("SELECT DISTINCT category FROM posts ORDER BY category");
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function __destruct() {
        $this->conn->close();
    }
}

// Handle search request
$searchResults = [];
$categories = [];
$error = null;

try {
    $search = new PostsSearch($db_config);
    $categories = $search->getCategories();
    
    if ($_SERVER['REQUEST_METHOD'] == 'GET') {
        $searchResults = $search->search([
            'query' => $_GET['q'] ?? '',
            'category' => $_GET['category'] ?? '',
            'user' => $_GET['user'] ?? '',
            'page' => $_GET['page'] ?? 1
        ]);
    }
} catch (Exception $e) {
    $error = $e->getMessage();
}

// Handle AJAX requests
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    header('Content-Type: application/json');
    echo json_encode([
        'error' => $error,
        'results' => $searchResults
    ]);
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Posts</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --primary-hover: #1d4ed8;
            --secondary-color: #64748b;
            --border-color: #e2e8f0;
            --background-color: #f8fafc;
            --card-background: #ffffff;
            --text-color: #334155;
            --text-light: #64748b;
            --success-color: #10b981;
            --error-color: #ef4444;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
            --radius: 0.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            line-height: 1.5;
            color: var(--text-color);
            background-color: var(--background-color);
            padding: 2rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .search-form {
            background: var(--card-background);
            padding: 1.5rem;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            margin-bottom: 2rem;
            border: 1px solid var(--border-color);
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
        }

        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: var(--text-color);
            font-size: 0.875rem;
        }

        input, select {
            width: 100%;
            padding: 0.625rem 0.875rem;
            border: 1px solid var(--border-color);
            border-radius: calc(var(--radius) * 0.75);
            font-size: 0.875rem;
            color: var(--text-color);
            background-color: var(--card-background);
            transition: border-color 0.15s ease;
        }

        input:focus, select:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        button {
            background-color: var(--primary-color);
            color: white;
            padding: 0.625rem 1.25rem;
            border: none;
            border-radius: calc(var(--radius) * 0.75);
            cursor: pointer;
            font-weight: 500;
            font-size: 0.875rem;
            transition: all 0.15s ease;
            height: 38px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        button:hover {
            background-color: var(--primary-hover);
        }

        .results {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }

        .post-card {
            background: var(--card-background);
            border-radius: var(--radius);
            overflow: hidden;
            box-shadow: var(--shadow-sm);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }

        .post-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow);
        }

        .post-thumb {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-bottom: 1px solid var(--border-color);
        }

        .post-content {
            padding: 1.25rem;
        }

        .post-title {
            margin: 0 0 0.75rem 0;
            font-size: 1.125rem;
            font-weight: 600;
            color: var(--text-color);
            line-height: 1.4;
        }

        .post-meta {
            color: var(--text-light);
            font-size: 0.875rem;
            margin-bottom: 0.75rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .post-description {
            margin-bottom: 1rem;
            color: var(--text-light);
            font-size: 0.875rem;
            line-height: 1.6;
        }

        .post-link {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: 500;
            font-size: 0.875rem;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }

        .post-link:hover {
            color: var(--primary-hover);
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 2rem;
        }

        .pagination a {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: calc(var(--radius) * 0.75);
            text-decoration: none;
            color: var(--text-color);
            font-size: 0.875rem;
            min-width: 2.5rem;
            text-align: center;
            transition: all 0.15s ease;
        }

        .pagination a:hover {
            background-color: var(--background-color);
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .pagination a.active {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }

        .error {
            background-color: rgba(239, 68, 68, 0.1);
            color: var(--error-color);
            padding: 1rem;
            border-radius: var(--radius);
            margin-bottom: 1.5rem;
            font-size: 0.875rem;
            border: 1px solid rgba(239, 68, 68, 0.2);
        }

        .no-results {
            text-align: center;
            color: var(--text-light);
            padding: 3rem 1rem;
            font-size: 0.875rem;
        }

        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }

            .form-group[style*="flex"] {
                flex: none !important;
            }

            .results {
                grid-template-columns: 1fr;
            }

            body {
                padding: 1rem;
            }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --background-color: #0f172a;
                --card-background: #1e293b;
                --text-color: #e2e8f0;
                --text-light: #94a3b8;
                --border-color: #334155;
            }
        }
    </style>
</head>
<!-- [Rest of the HTML remains unchanged] -->
<body>
    <div class="container">
        <?php if ($error): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="search-form" method="GET" id="searchForm">
            <div class="form-row">
                <div class="form-group" style="flex: 2;">
                    <label for="q">Search</label>
                    <input type="text" id="q" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>" 
                           placeholder="Search by title or description">
                </div>
                
                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?php echo htmlspecialchars($cat['category']); ?>"
                                <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['category']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['category']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="user">User</label>
                    <input type="text" id="user" name="user" value="<?php echo htmlspecialchars($_GET['user'] ?? ''); ?>" 
                           placeholder="Search by user">
                </div>

                <div class="form-group" style="align-self: flex-end;">
                    <button type="submit">Search</button>
                </div>
            </div>
        </form>

        <?php if (!empty($searchResults['results'])): ?>
            <div class="results">
                <?php foreach ($searchResults['results'] as $post): ?>
                    <article class="post-card">
                        <img src="<?php echo htmlspecialchars($post['thumb']); ?>" alt="" class="post-thumb">
                        <div class="post-content">
                            <h2 class="post-title"><?php echo htmlspecialchars($post['title']); ?></h2>
                            <div class="post-meta">
                                <span>By <?php echo htmlspecialchars($post['user_id']); ?></span> •
                                <span><?php echo htmlspecialchars($post['category']); ?></span> •
                                <span><?php echo date('M j, Y', strtotime($post['created_at'])); ?></span>
                            </div>
                            <p class="post-description">
                                <?php echo htmlspecialchars(substr($post['description'], 0, 150)) . '...'; ?>
                            </p>
                            <a href="<?php echo htmlspecialchars($post['url']); ?>" class="post-link" target="_blank">
                                View Post →
                            </a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php if ($searchResults['totalPages'] > 1): ?>
                <div class="pagination">
                    <?php for ($i = 1; $i <= $searchResults['totalPages']; $i++): ?>
                        <a href="?<?php echo http_build_query(array_merge($_GET, ['page' => $i])); ?>" 
                           class="<?php echo ($searchResults['currentPage'] == $i) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        <?php elseif (isset($_GET['q']) || isset($_GET['category']) || isset($_GET['user'])): ?>
            <p>No results found.</p>
        <?php endif; ?>
    </div>

    <script>
        document.getElementById('searchForm').addEventListener('submit', function(e) {
            // Remove empty parameters from the form submission
            const formData = new FormData(this);
            const params = new URLSearchParams();
            
            for (const [key, value] of formData.entries()) {
                if (value.trim() !== '') {
                    params.append(key, value);
                }
            }
            
            window.location.href = '?' + params.toString();
            e.preventDefault();
        });
    </script>
</body>
</html>