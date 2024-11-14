<?php
// Database configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'decenphp',
    'username' => 'root',
    'password' => ''
];

try {
    // Create PDO connection
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );

    // The enhanced query with percentage of total likes
    $query = "
        WITH 
        current_likes AS (
            SELECT 
                file_id,
                COUNT(*) as current_count
            FROM likes
            WHERE action = 'like'
                AND timestamp <= NOW()
            GROUP BY file_id
            HAVING current_count >= 10
        ),
        previous_likes AS (
            SELECT 
                file_id,
                COUNT(*) as previous_count
            FROM likes
            WHERE action = 'like'
                AND timestamp <= DATE_SUB(NOW(), INTERVAL 1 WEEK)
            GROUP BY file_id
        ),
        total_likes AS (
            SELECT SUM(current_count) as total_likes_count
            FROM current_likes
        )
        SELECT 
            c.file_id,
            f.filename,
            c.current_count as current_likes,
            COALESCE(p.previous_count, 0) as likes_week_ago,
            CASE 
                WHEN COALESCE(p.previous_count, 0) = 0 THEN 100
                ELSE ROUND(
                    ((c.current_count - COALESCE(p.previous_count, 0)) / COALESCE(p.previous_count, 1) * 100),
                    2
                )
            END as percentage_increase,
            ROUND(
                (c.current_count / (SELECT total_likes_count FROM total_likes) * 100),
                2
            ) as percentage_of_total
        FROM current_likes c
        LEFT JOIN previous_likes p ON c.file_id = p.file_id
        LEFT JOIN files f ON c.file_id = f.id
        ORDER BY c.current_count DESC";

    $stmt = $pdo->query($query);
    $results = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Likes Analysis</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 20px;
            background-color: #f5f5f5;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            padding: 20px;
        }

        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }

        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #dee2e6;
        }

        .stats-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }

        .stats-table th,
        .stats-table td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        .stats-table th {
            background-color: #f8f9fa;
            color: #333;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .stats-table tr:hover {
            background-color: #f8f9fa;
        }

        .percentage-badge {
            padding: 5px 10px;
            border-radius: 15px;
            color: white;
            font-weight: 500;
            display: inline-block;
            min-width: 60px;
            text-align: center;
        }

        .positive {
            background-color: #28a745;
        }

        .negative {
            background-color: #dc3545;
        }

        .neutral {
            background-color: #6c757d;
        }

        .total-percentage {
            background-color: #17a2b8;
        }

        .filename {
            max-width: 300px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .stats-table th,
            .stats-table td {
                padding: 8px 10px;
            }

            .filename {
                max-width: 150px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>File Likes Analysis</h1>
        
        <div class="summary">
            <strong>Note:</strong> Showing files with 10 or more likes. Data is sorted by number of current likes.
        </div>

        <?php if (empty($results)): ?>
            <p>No files found with 10 or more likes.</p>
        <?php else: ?>
            <table class="stats-table">
                <thead>
                    <tr>
                        <th>File ID</th>
                        <th>Filename</th>
                        <th>Current Likes</th>
                        <th>Likes Week Ago</th>
                        <th>Weekly Change</th>
                        <th>% of Total Likes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($results as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['file_id']) ?></td>
                            <td class="filename" title="<?= htmlspecialchars($row['filename']) ?>">
                                <?= htmlspecialchars($row['filename']) ?>
                            </td>
                            <td><?= number_format($row['current_likes']) ?></td>
                            <td><?= number_format($row['likes_week_ago']) ?></td>
                            <td>
                                <?php
                                $percentageClass = $row['percentage_increase'] > 0 ? 'positive' : 
                                                 ($row['percentage_increase'] < 0 ? 'negative' : 'neutral');
                                ?>
                                <span class="percentage-badge <?= $percentageClass ?>">
                                    <?= $row['percentage_increase'] ?>%
                                </span>
                            </td>
                            <td>
                                <span class="percentage-badge total-percentage">
                                    <?= $row['percentage_of_total'] ?>%
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>