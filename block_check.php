<?php

// Directory where the blocks are stored
$blockDir = 'block/';

// Function to validate the blockchain
function isBlockchainValid() {
    global $blockDir;
    
    // Get all block files sorted by creation time (oldest first)
    $blockFiles = glob($blockDir . '*.block');
    usort($blockFiles, function($a, $b) {
        return filemtime($a) - filemtime($b);
    });
    
    $previousHash = '0'; // Initial hash for the genesis block

    // Iterate over all blocks and validate
    foreach ($blockFiles as $blockFile) {
        // Read block content
        $blockContent = file_get_contents($blockFile);
        $blockData = json_decode($blockContent, true);
        
        if (!$blockData) {
            echo "Invalid block format: " . basename($blockFile) . "\n";
            return false;
        }
        
        // Calculate the hash of the current block (excluding prev_hash for calculation)
        $calculatedHash = hash('sha256', json_encode($blockData, JSON_PRETTY_PRINT));
        
        // Check if the current block's previous hash matches the stored previous hash
        if ($blockData['prev_hash'] !== $previousHash) {
            echo "Blockchain broken at block: " . basename($blockFile) . "\n";
            return false;
        }
        
        // Update previous hash for the next iteration
        $previousHash = $calculatedHash;
    }

    // If no issues found, blockchain is valid
    return true;
}

// Check if the blockchain is valid
if (isBlockchainValid()) {
    echo "The blockchain is valid.\n";
} else {
    echo "The blockchain is invalid.\n";
}
?>
