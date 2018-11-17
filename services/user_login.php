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
    
    // Check user validity
    if(date("Y-m-d", strtotime($user_login_result[0]["valid_to_date"])) < date("Y-m-d")) die("ไม่สามารถเข้าใช้งานใดหลังวันที่กำหนด กรุณาติดต่อผู้ดูแลระบบ");

    // Starting the current session
    session_start();
    $_SESSION["user_name"]      = $user_login_result[0]["user_name"];
    $_SESSION["user_type_desc"] = $user_login_result[0]["user_type_desc"];
    die("เข้าสู่ระบบสำเร็จ");
}
?>