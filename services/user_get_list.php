<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo user_get_list(true);

function user_get_list($bool_return_json) {
    global $db_conn;

    $user_account_query = $db_conn->prepare("SELECT *
                                               FROM user_account
                                               JOIN user_type ON user_account.user_type_name = user_type.user_type_name");
    if($user_account_query->execute()) {
        $user_account_result = $user_account_query->fetchAll();
        $user_account_array  = [];

        foreach($user_account_result as $system_param_single) {
            array_push($user_account_array, ["user_name"  => $system_param_single["user_name"], 
                                             "type_desc"  => $system_param_single["user_type_desc"],
                                             "valid_from" => $system_param_single["valid_from_date"],
                                             "valid_to"   => $system_param_single["valid_to_date"]]);
        }

        if(count($user_account_array) > 0) {
            if($bool_return_json) return json_encode($user_account_array);
            else return $user_account_array;
        } else return "ไม่มีบัญชีผู้ใช้ในระบบ";
    } else {
        die(error_display($user_account_query));
    }
}
?>