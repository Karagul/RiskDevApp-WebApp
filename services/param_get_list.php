<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo param_get_list(true);

function param_get_list($bool_return_json) {
    global $db_conn;

    $system_param_query = $db_conn->prepare("SELECT *
                                               FROM system_param");
    if($system_param_query->execute()) {
        $system_param_result = $system_param_query->fetchAll();
        $system_param_array  = [];

        foreach($system_param_result as $system_param_single) {
            if(strpos($system_param_single["param_value"], "{year}") !== false) {
                $system_param_single["param_value"] = str_replace("{year}", date("Y"), $system_param_single["param_value"]);
                $system_param_single["param_value"] = date("j M", strtotime($system_param_single["param_value"]));
            }

            array_push($system_param_array, ["param_name"  => $system_param_single["param_name"], 
                                             "param_value" => $system_param_single["param_value"],
                                             "param_unit"  => $system_param_single["param_unit"],
                                             "param_desc"  => $system_param_single["param_desc"]]);
        }

        if(count($system_param_array) > 0) {
            if($bool_return_json) return json_encode($system_param_array);
            else return $system_param_array;
        } else return null;
    } else {
        die(error_display($system_param_query));
    }
}
?>