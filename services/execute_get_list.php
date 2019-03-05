<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo execute_get_list(true);

function execute_get_list($bool_return_json) {
    global $db_conn;

    $execute_list_query = $db_conn->prepare("SELECT execute_type_desc, result_for_year, execute_date, execute_status_desc
                                               FROM execute_result
                                               JOIN execute_type ON execute_result.execute_type_name = execute_type.execute_type_name
                                               JOIN execute_status ON execute_result.execute_status_name = execute_status.execute_status_name
                                              GROUP BY execute_result.execute_type_name, execute_result.result_for_year,
													   execute_type.execute_type_desc, execute_date, execute_status_desc");
    if($execute_list_query->execute()) {
        $execute_list_result = $execute_list_query->fetchAll();
        $execute_list_array = array();

        foreach($execute_list_result as $execute_list_single) {
            $execute_list_intermediate = array();
            $execute_list_intermediate["type"] = $execute_list_single["execute_type_desc"];
            $execute_list_intermediate["date"] = date("j M Y", strtotime($execute_list_single["execute_date"]));
            $execute_list_intermediate["year"] = $execute_list_single["result_for_year"];
            $execute_list_intermediate["status"] = $execute_list_single["execute_status_desc"];
            array_push($execute_list_array, $execute_list_intermediate);
        }

        if(count($execute_list_result) > 0) {
            if($bool_return_json) return json_encode($execute_list_array);
            else return $execute_list_array;
        } else die("ไม่พบผลการวิเคราะห์ความเสี่ยงในระบบ");
    } else die(var_dump($execute_list_query->errorInfo()));
}
?>