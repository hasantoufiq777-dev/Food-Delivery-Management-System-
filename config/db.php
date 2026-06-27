<?php

define('DB_HOST', 'localhost');
define('DB_PORT', '1521');
define('DB_SID', 'XE'); 
define('DB_USER', 'fooddel');
define('DB_PASS', 'password');





function get_db_connection_pdo() {
    $tns = "(DESCRIPTION =
        (ADDRESS = (PROTOCOL = TCP)(HOST = " . DB_HOST . ")(PORT = " . DB_PORT . "))
        (CONNECT_DATA =
            (SERVER = DEDICATED)
            (SERVICE_NAME = " . DB_SID . ")
        )
    )";
    
    try {
        $conn = new PDO("oci:dbname=" . $tns, DB_USER, DB_PASS);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}
?>
