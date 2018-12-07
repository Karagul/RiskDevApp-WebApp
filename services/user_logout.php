<?php
require_once dirname(__FILE__)."/../config.php";

// Logging out
session_start();
session_destroy();
echo "ออกจากระบบสำเร็จ";
header("Location: ".$server_path."/login.php");
?>