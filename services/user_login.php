<?php
require_once dirname(__FILE__)."/../config.php";

// Check login credentials
$user_login_query = $db_conn->prepare("SELECT *
                                         FROM user_account
                                         JOIN user_type ON user_account.user_type_name = user_type.user_type_name
                                        WHERE user_name     = :username
                                          AND user_password = :password");
$user_login_query->bindParam(":username", $_POST["username"], PDO::PARAM_STR);
$user_login_query->bindValue(":password", base64_encode($_POST["password"]), PDO::PARAM_STR);
if($user_login_query->execute()) {
    $user_login_result = $user_login_query->fetchAll();

    if(count($user_login_result) == 0) die("ชื่อบัญชีผู้ใช้หรือรหัสผ่านไม่ถูกต้อง<br />กรุณาตรวจสอบอีกครั้ง");
    else if(count($user_login_result) > 1) die("เกิดข้อผิดพลาด กรุณาติดต่อผู้ดูแลระบบ");
    
	$current_user = $user_login_result[0];
	
    // Check user validity
	//beg++eKS19.11.2018 Adapt for PHP5.5
    //if(date("Y-m-d", strtotime($current_user["valid_to_date"])) < date("Y-m-d")) die("ไม่สามารถเข้าใช้งานใดหลังวันที่กำหนด กรุณาติดต่อผู้ดูแลระบบ");
	if($current_user["valid_to_date"] < date("Y-m-d")) die("ไม่สามารถเข้าใช้งานใดหลังวันที่กำหนด กรุณาติดต่อผู้ดูแลระบบ");
	//end++eKS19.11.2018 Adapt for PHP5.5
	
	//beg+++iKS21.11.2018 User Authentication for WebApp
	if(isset($_POST["device"]) && $_POST["device"] == "WEB" && $current_user["user_type_name"] != "ADMIN") 
		die("ผู้ใช้นี้ ไม่สามารถเข้าใช้ WebApp ได้");
	//end+++iKS21.11.2018 User Authentication for WebApp
	
    // Starting the current session	
    session_start();
    $_SESSION["user_name"]      = $current_user["user_name"];
    $_SESSION["user_type_desc"] = $current_user["user_type_desc"];
    die("เข้าสู่ระบบสำเร็จ");
}
?>