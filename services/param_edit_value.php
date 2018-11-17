<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo param_edit_value(true);

function param_edit_value($bool_return_json) {
    global $db_conn;

    $param_edit_query = $db_conn->prepare("UPDATE system_param SET param_value = :paramValue WHERE param_name = :paramName");
    $param_edit_query->bindValue(":paramValue", $_POST["param_value"], PDO::PARAM_STR);
    $param_edit_query->bindValue(":paramName", $_POST["param_name"], PDO::PARAM_STR);
    if($param_edit_query->execute()) {
        // Check updated value
        $param_check_query = $db_conn->prepare("SELECT * FROM system_param WHERE param_name = :paramName AND param_value = :paramValue");
        $param_check_query->bindValue(":paramName", $_POST["param_name"], PDO::PARAM_STR);
        $param_check_query->bindValue(":paramValue", $_POST["param_value"], PDO::PARAM_STR);
        if($param_check_query->execute()) {
            $param_check_result = $param_check_query->fetchAll();
            if(count($param_check_result) > 0) {
                die("แก้ไขค่าตัวแปรสำเร็จ");
            } else die("ไม่สามารถแก้ไขค่าตัวแปรได้ในขณะนี้ กรุณาติดต่อผู้ดูแลระบบ");
        } else die(error_display($param_check_query));
    } else die(error_display($param_edit_query));
}
?>