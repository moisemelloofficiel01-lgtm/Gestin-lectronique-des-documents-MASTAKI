<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "Starting debug...\n";

// 1. Check DB Config
try {
    require_once __DIR__ . '/../../config/db.php';
    echo "DB Config included.\n";
} catch (Throwable $e) {
    echo "Error including DB config: " . $e->getMessage() . "\n";
    exit;
}

if (!isset($conn)) {
    echo "Connection variable not set.\n";
    exit;
}

if (!$conn) {
    echo "Connection failed.\n";
    exit;
}

echo "Database connected.\n";

// 2. Check Schema Helper
try {
    require_once __DIR__ . '/schema_helper.php';
    echo "Schema Helper included.\n";
} catch (Throwable $e) {
    echo "Error including schema helper: " . $e->getMessage() . "\n";
    exit;
}

// 3. Test Schema Helper Function
if (function_exists('ensureFournisseursTableSchema')) {
    echo "Function exists.\n";
    try {
        if (ensureFournisseursTableSchema($conn)) {
             echo "Schema check passed.\n";
        } else {
             echo "Schema check failed.\n";
        }
    } catch (Throwable $e) {
        echo "Error running schema check: " . $e->getMessage() . "\n";
    }
} else {
    echo "Function ensureFournisseursTableSchema not found.\n";
}

echo "Debug complete.\n";
?>
