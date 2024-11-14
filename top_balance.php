<?php

// Database connection configuration
$config = [
    'host' => 'localhost',
    'dbname' => 'decenphp',
    'username' => 'root',
    'password' => ''
];

try {
    $pdo = new PDO(
        "mysql:host={$config['host']};dbname={$config['dbname']};charset=utf8mb4",
        $config['username'],
        $config['password'],
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );

    // Get the date range for last week
    $endDate = date('Y-m-d H:i:s');
    $startDate = date('Y-m-d H:i:s', strtotime('-1 week'));
    $previousWeekStart = date('Y-m-d H:i:s', strtotime('-2 week'));

    // First, get all unique file_hashes from invest table
    $fileHashQuery = "SELECT DISTINCT file_hash FROM invest";
    $stmt = $pdo->query($fileHashQuery);
    $fileHashes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $likesGrowth = [];

    // For each file_hash, calculate the likes growth
    foreach ($fileHashes as $fileHash) {
        // Get likes count for current week
        $currentWeekLikesQuery = "
            SELECT COUNT(*) as likes_count
            FROM likes l
            JOIN files f ON l.file_id = f.id
            WHERE f.hash = :file_hash
            AND l.action = 'like'
            AND l.timestamp BETWEEN :start_date AND :end_date";

        $stmt = $pdo->prepare($currentWeekLikesQuery);
        $stmt->execute([
            ':file_hash' => $fileHash,
            ':start_date' => $startDate,
            ':end_date' => $endDate
        ]);
        $currentWeekLikes = $stmt->fetch(PDO::FETCH_ASSOC)['likes_count'];

        // Get likes count for previous week
        $previousWeekLikesQuery = "
            SELECT COUNT(*) as likes_count
            FROM likes l
            JOIN files f ON l.file_id = f.id
            WHERE f.hash = :file_hash
            AND l.action = 'like'
            AND l.timestamp BETWEEN :previous_start AND :start_date";

        $stmt = $pdo->prepare($previousWeekLikesQuery);
        $stmt->execute([
            ':file_hash' => $fileHash,
            ':previous_start' => $previousWeekStart,
            ':start_date' => $startDate
        ]);
        $previousWeekLikes = $stmt->fetch(PDO::FETCH_ASSOC)['likes_count'];

        // Calculate growth percentage
        $previousLikes = max(1, $previousWeekLikes); // Prevent division by zero
        $growthPercent = (($currentWeekLikes - $previousLikes) / $previousLikes) * 100;
        $likesGrowth[$fileHash] = $growthPercent;
    }

    // Get investments and calculate new amounts
    $investQuery = "
        SELECT 
            i.user_id,
            i.file_hash,
            i.amount,
            i.transaction_date
        FROM invest i
        ORDER BY i.user_id, i.transaction_date";

    $stmt = $pdo->prepare($investQuery);
    $stmt->execute();
    $results = [];

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $userId = $row['user_id'];
        $fileHash = $row['file_hash'];
        $originalAmount = $row['amount'];
        
        // Calculate new amount based on likes growth
        $growth = $likesGrowth[$fileHash] ?? 0;
        $newAmount = $originalAmount * (1 + ($growth / 100));
        
        if (!isset($results[$userId])) {
            $results[$userId] = [
                'original_total' => 0,
                'new_total' => 0,
                'files' => []
            ];
        }
        
        $results[$userId]['original_total'] += $originalAmount;
        $results[$userId]['new_total'] += $newAmount;
        $results[$userId]['files'][] = [
            'file_hash' => $fileHash,
            'original_amount' => $originalAmount,
            'new_amount' => $newAmount,
            'growth_percent' => $growth,
            'transaction_date' => $row['transaction_date']
        ];
    }

    // Display results
    echo "<h2>Investment Growth Report</h2>\n";
    foreach ($results as $userId => $data) {
        echo "<h3>User ID: $userId</h3>\n";
        echo "Original Total Amount: $" . number_format($data['original_total'], 2) . "\n";
        echo "New Total Amount: $" . number_format($data['new_total'], 2) . "\n";
        echo "Total Growth: " . number_format(($data['new_total'] - $data['original_total']) / $data['original_total'] * 100, 2) . "%\n\n";
        
        echo "File Details:\n";
        foreach ($data['files'] as $file) {
            echo "File Hash: " . $file['file_hash'] . "\n";
            echo "Original Amount: $" . number_format($file['original_amount'], 2) . "\n";
            echo "New Amount: $" . number_format($file['new_amount'], 2) . "\n";
            echo "Growth: " . number_format($file['growth_percent'], 2) . "%\n";
            echo "Transaction Date: " . $file['transaction_date'] . "\n\n";
        }
        echo "----------------------------------------\n";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>