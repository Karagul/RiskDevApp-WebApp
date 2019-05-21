<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo user_password_change(true);

function user_password_change($bool_return_json) {
    global $db_conn;

    $_POST["password"] = base64_encode($_POST["password"]);

    $password_change_query = $db_conn->prepare("UPDATE user_account
                                                   SET user_password = :password
                                                 WHERE user_name = :username");
    $password_change_query->bindValue(":password", $_POST["password"], PDO::PARAM_STR);
    $password_change_query->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
    if($password_change_query->execute()) {
        // Re-check the changed password
        $password_check_query = $db_conn->prepare("SELECT user_name, user_password
                                                     FROM user_account
                                                    WHERE user_name = :username AND user_password = :password");
        $password_check_query->bindValue(":username", $_POST["username"], PDO::PARAM_STR);
        $password_check_query->bindValue(":password", $_POST["password"], PDO::PARAM_STR);
        if($password_check_query->execute()) {
            $password_check_result = $password_check_query->fetchAll();
            if(count($password_check_result)) {
                return "เปลี่ยนรหัสผ่านเรียบร้อยแล้ว";
            } else return "ไม่สามารถเปลี่ยนรหัสผ่านได้ กรุณาติดต่อผู้ดูแลระบบ";
        } else die(error_display($password_change_query));
    } else die(error_display($password_change_query));
}
?>