<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo user_delete();

function user_delete() {
    global $db_conn;

    // Checking input validity
    if(!isset($_POST["user_name"])) return("ค่าตัวแปรไม่ถูกต้อง กรุณาติดต่อผู้ดูแลระบบ");

    // Preparing default values
    $valid_to_date   = date("Y-m-d");

    // Checking username validity
    $user_check_query = $db_conn->prepare("SELECT *
                                             FROM user_account
                                            WHERE user_name = :username");
    $user_check_query->bindParam(":username", $_POST["user_name"]);
    if($user_check_query->execute()) {
        $user_check_result = $user_check_query->fetchAll();
        if(count($user_check_result) == 0) return "ไม่พบผู้ใช้งานดังกล่าวในระบบ";
    }

    // Deleting the user
    $user_delete_query = $db_conn->prepare("DELETE FROM user_account
                                             WHERE user_name = :username");
    $user_delete_query->bindParam(":username", $_POST["user_name"], PDO::PARAM_STR);
    if($user_delete_query->execute()) {
        // Checking after deletion
        $user_check_query = $db_conn->prepare("SELECT *
                                                 FROM user_account
                                                WHERE user_name = :username");
        $user_check_query->bindParam(":username", $_POST["user_name"], PDO::PARAM_STR);
        if($user_check_query->execute()) {
            $user_check_result = $user_check_query->fetchAll();
            if(count($user_check_result) == 0) {
                return "ลบผู้ใช้สำเร็จ";
            } else {
                return "ไม่สามารถลบผู้ใช้ได้ในขณะนี้ กรุณาลองอีกครั้งหรือติดต่อผู้ดูแลระบบ";
            }
        }
    }
}
?>