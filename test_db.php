<?php
/**
 * Connection Test Script
 * Food Delivery Management System
 */
require_once __DIR__ . '/config/db.php';

// Define BASE_URL if functions.php is not loaded
if (!defined('BASE_URL')) {
    define('BASE_URL', '/');
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <title>Database Connection Test</title>
    <style>
        body { font-family: 'DM Sans', sans-serif; background-color: #f7f9fc; padding: 3rem; color: #2c3e50; }
        .card { background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); max-width: 600px; margin: 0 auto; }
        h2 { border-bottom: 2px solid #eaedf2; padding-bottom: 1rem; color: #FF6B2B; }
        .success { color: #2ecc71; font-weight: bold; }
        .error { color: #e74c3c; font-weight: bold; }
        .warning { color: #f39c12; font-weight: bold; }
        .status-box { background: #f8f9fa; border-left: 4px solid #ccd1d9; padding: 1rem; margin-top: 1rem; border-radius: 4px; }
    </style>
</head>
<body>
<div class='card'>
    <h2>🔌 Oracle Database Connection Test</h2>";

// 1. Verify OCI8 Connection
echo "<h3>1. Testing OCI8 Extension Connection...</h3>";
if (function_exists('oci_connect')) {
    $connection_string = "(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = " . DB_HOST . ")(PORT = " . DB_PORT . "))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = " . DB_SID . ")
        )
    )";
    
    // Disable error reporting during connection test to handle error gracefully
    $conn = @oci_connect(DB_USER, DB_PASS, $connection_string);
    
    if ($conn) {
        echo "<span class='success'>✓ OCI8 Connection Successful!</span>";
        
        // Run test query
        $stmt = oci_parse($conn, "SELECT 'Connection Active' AS STATUS FROM dual");
        oci_execute($stmt);
        if ($row = oci_fetch_assoc($stmt)) {
            echo "<div class='status-box'>Test query status: <strong>" . $row['STATUS'] . "</strong></div>";
        }
        oci_free_statement($stmt);
        oci_close($conn);
    } else {
        $e = oci_error();
        echo "<span class='error'>✗ OCI8 Connection Failed</span>";
        echo "<p>Error details: <code>" . htmlentities($e['message']) . "</code></p>";
        echo "<p><em>Check if your DB_USER, DB_PASS, DB_HOST, and DB_SID values are correct in config/db.php.</em></p>";
    }
} else {
    echo "<span class='warning'>⚠ OCI8 Extension is not enabled/installed in your PHP environment.</span>";
    echo "<p>Please ensure you uncommented <code>extension=oci8_19</code> (or similar version) in your <code>php.ini</code> file and restarted Apache.</p>";
}

// 2. Verify PDO OCI Connection
echo "<h3>2. Testing PDO (OCI) Connection...</h3>";
try {
    $tns = "(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = " . DB_HOST . ")(PORT = " . DB_PORT . "))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = " . DB_SID . ")
        )
    )";
    
    $conn = new PDO("oci:dbname=" . $tns, DB_USER, DB_PASS);
    echo "<span class='success'>✓ PDO Connection Successful!</span>";
    $conn = null;
} catch (PDOException $e) {
    echo "<span class='error'>✗ PDO Connection Failed</span>";
    echo "<p>Error details: <code>" . $e->getMessage() . "</code></p>";
}

echo "</div>
</body>
</html>";
?>
