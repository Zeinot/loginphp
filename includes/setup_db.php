<?php
// Include database connection
require_once 'db.php';

// Read SQL from schema file
$sql = file_get_contents(__DIR__ . '/schema.sql');

// Execute multi-query SQL
if ($conn->multi_query($sql)) {
    do {
        // Store first result set
        if ($result = $conn->store_result()) {
            $result->free();
        }
        // Move to next result set
    } while ($conn->more_results() && $conn->next_result());
    
    echo "Database setup completed successfully!";
} else {
    echo "Error setting up database: " . $conn->error;
}

$conn->close();
?>
