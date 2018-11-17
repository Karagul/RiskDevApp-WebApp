<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_POST["return_type"])) echo param_get_info(true);

function param_get_info($bool_return_json) {
    global $db_conn;

    $param_info_query = $db_conn->prepare("SELECT * FROM system_param WHERE param_name = :paramname");
    $param_info_query->bindValue(":paramname", $_POST["param_name"], PDO::PARAM_STR);
    if($param_info_query->execute()) {
        $param_info_result = $param_info_query->fetchAll();
        $param_info_array  = [];

        if(strpos($param_info_result[0]["param_value"], "{year}") !== false) {
            $param_info_result[0]["param_value"] = str_replace("{year}", date("Y"), $param_info_result[0]["param_value"]);
            $param_info_result[0]["param_value"] = date("j M", strtotime($param_info_result[0]["param_value"]));
        }

        foreach($param_info_result as $param_info_single) {
            array_push($param_info_array, ["parameter_name"        => $param_info_single["param_name"],
                                           "parameter_value"       => $param_info_single["param_value"],
                                           "parameter_unit"        => $param_info_single["param_unit"],
                                           "parameter_description" => $param_info_single["param_desc"]]);
        }

        if(count($param_info_array) > 0) {
            if($bool_return_json) return json_encode($param_info_array);
            else return $param_info_array;
        } else return null;
    } else die(error_display($param_info_query));
}
?>