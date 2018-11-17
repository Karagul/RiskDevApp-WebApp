<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo user_get_type(true);

function user_get_type($bool_return_json) {
    global $db_conn;

    $user_type_query = $db_conn->prepare("SELECT *
                                            FROM user_type");
    if($user_type_query->execute()) {
        $user_type_result = $user_type_query->fetchAll();
        $user_type_array  = [];

        foreach($user_type_result as $system_param_single) {
            array_push($user_type_array, ["type_name" => $system_param_single["user_type_name"], 
                                          "type_desc" => $system_param_single["user_type_desc"]]);
        }

        if(count($user_type_array) > 0) {
            if($bool_return_json) return json_encode($user_type_array);
            else return $user_type_array;
        } else return null;
    } else {
        die(error_display($user_type_query));
    }
}
?>