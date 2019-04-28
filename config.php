<?php
$settings_os = strtoupper(php_uname("s"));

// Database Handler
$hostname = "localhost";
$username = "riskdevapp";
$password = "riskdevapp2018";
$database = "riskdevapp";
$charset  = "utf8mb4";

$server_path = "http://164.115.23.67/riskdevapp-webapp";
$path_directory_script    = $server_path."/scripts";
$path_directory_workspace = $server_path."/results";
$python_bin 			  = "C:\\Users\\Administrator\\AppData\\Local\\Programs\\Python\\Python37-32\\python.exe";
$r_bin 					  = "C:\\Program Files\\R\\R-3.5.1\\bin\\Rscript.exe";

// Enforcing UTF-8 encoding
header("Content-type: text/html; charset=utf-8");

// Determining database driver from OS
if(substr($settings_os, 0, 3) == "WIN") {
    // Assume SQL Server
    $conn_str = "sqlsrv:Server=$hostname;Database=$database";
} else {
    // Assume MySQL
    $conn_str = "mysql:host=$hostname;dbname=$database;charset=$charset";
    $conn_opt = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
}

// Connecting using PDO drivers
try {
    $db_conn = new PDO($conn_str, $username, $password, $conn_opt);
    mb_internal_encoding("UTF-8");
    mb_http_output("UTF-8");
} catch(Exception $e) {
    die("Could not connect to the database:".$e->getMessage());
}

// Functions: Error Reporting
function error_display($query) {
    $error_info = $query->errorInfo();
    // Parsing the error information
    $error_code    = $error_info[1];
    $error_message = $error_info[2];
    return "Error: $error_message (Code: $error_code)";
}

//beg+++iKS10.04.2019 Removing กิ่งอำเภอ
function edit_district($districtName) {
    return str_replace("กิ่งอำเภอ", "", $districtName);
}
//end+++iKS10.04.2019 Removing กิ่งอำเภอ
?>