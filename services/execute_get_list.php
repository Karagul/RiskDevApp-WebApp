<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo execute_get_list(true);

function execute_get_list($bool_return_json) {
    global $db_conn;

    $execute_list_query = $db_conn->prepare("SELECT result_type_name, execute_type_desc, execute_date, execute_first_date,
                                                    result_status_name, execute_status_desc
                                               FROM result_nipah
                                               JOIN result_type ON result_type_name = result_type.execute_type_name
                                               JOIN result_status ON result_status_name = result_status.execute_status_name
                                              WHERE result_status.execute_status_name = 'READY'
                                              GROUP BY execute_id");
    if($execute_list_query->execute()) {
        $execute_list_result = $execute_list_query->fetchAll();
        $execute_list_array = array();

        foreach($execute_list_result as $execute_list_single) {
            $execute_list_intermediate = array();
            $execute_list_intermediate["type"] = $execute_list_single["execute_type_desc"];
            $execute_list_intermediate["date"] = date("j M Y", strtotime($execute_list_single["execute_date"]));
            $execute_list_intermediate["year"] = date("Y", strtotime($execute_list_single["execute_first_date"]));
            $execute_list_intermediate["status"] = $execute_list_single["execute_status_desc"];
            array_push($execute_list_array, $execute_list_intermediate);
        }

        if(count($execute_list_result) > 0) {
            if($bool_return_json) return json_encode($execute_list_array);
            else return $execute_list_array;
        }
    }
}
?>