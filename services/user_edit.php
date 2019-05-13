<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo user_edit($_POST["return_type"]);

function user_edit($return_type) {
    global $db_conn;

    // Checking Input Validity
    if(!isset($_POST["user_name"]) || !isset($_POST["user_type"])) die("ค่าตัวแปรไม่ถูกต้อง กรุณาติดต่อผู้ดูแลระบบ");

    // Checking Username Validity
    $user_check_query = $db_conn->prepare("SELECT * FROM user_account WHERE user_name = :username");
    $user_check_query->bindValue(":username", $_POST["user_name"], PDO::PARAM_STR);
    if($user_check_query->execute()) {
        $user_check_result = $user_check_query->fetchAll();
        if(count($user_check_result) == 0) die("ไม่พบผู้ใช้ดังกล่าวในระบบ กรุณาลองใหม่อีกครั้ง");
    }

    // Checking User Type Validity
    $user_type_query = $db_conn->prepare("SELECT * FROM user_type WHERE user_type_name = :typename");
    $user_type_query->bindValue(":typename", $_POST["user_type"], PDO::PARAM_STR);
    if($user_type_query->execute()) {
        $user_type_result = $user_type_query->fetchAll();
        if(count($user_type_result) == 0) die("ไม่พบประเภทผู้ใช้งานดังกล่าวในระบบ กรุณาลองใหม่อีกครั้ง");
    }

    // Editing this user
    $user_edit_query = $db_conn->prepare("UPDATE user_account SET user_type_name = :typename, valid_to_date = :validUntil WHERE user_name = :username");
    $user_edit_query->bindValue(":typename", $_POST["user_type"], PDO::PARAM_STR);
    $user_edit_query->bindValue(":validUntil", date("Y-m-d", strtotime($_POST["valid_to"])), PDO::PARAM_STR);
    $user_edit_query->bindValue(":username", $_POST["user_name"], PDO::PARAM_STR);
    if($user_edit_query->execute()) {
        // Check edited user
        $user_check_query = $db_conn->prepare("SELECT * FROM user_account WHERE user_name = :username AnD user_type_name = :usertype");
        $user_check_query->bindValue(":username", $_POST["user_name"], PDO::PARAM_STR);
        $user_check_query->bindValue(":usertype", $_POST["user_type"], PDO::PARAM_STR);
        if($user_check_query->execute()) {
            $user_check_result = $user_check_query->fetchAll();
            if(count($user_check_result) > 0) die("แก้ไขบัญชีผู้ใช้สำเร็จ");
            else die("ไม่สามารถแก้ไขบัญชีผู้ใช้ได้ในขณะนี้ กรุณาลองใหม่อีกครั้ง");
        }
    } else die(error_display($user_edit_query));
}
?>