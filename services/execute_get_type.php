<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo execute_get_type(true);

function execute_get_type($bool_return_json) {
    global $db_conn;

    $execute_type_query = $db_conn->prepare("SELECT * FROM result_type;");
    if($execute_type_query->execute()) {
        $execute_type_result = $execute_type_query->fetchAll();
        $execute_type_array  = array();

        foreach($execute_type_result as $execute_type_single) {
            array_push($execute_type_array, array("type_name" => $execute_type_single["execute_type_name"],
                                                  "type_desc" => $execute_type_single["execute_type_desc"]));
        }

        if(count($execute_type_array) > 0) {
            if($bool_return_json) return json_encode($execute_type_array);
            else return $execute_type_array;
        }
    }
}
?>