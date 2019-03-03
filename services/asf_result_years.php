<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo asf_result_years(true);

function asf_result_years($bool_return_json) {
    global $db_conn;

    $asf_year_query = $db_conn->prepare("SELECT DISTINCT result_for_year 
                                             FROM execute_result
                                            WHERE execute_type_name = 'ASF'");
    if($asf_year_query->execute()) {
        $asf_year_list = $asf_year_query->fetchAll();
        $asf_year_array = array();

        foreach($asf_year_list as $asf_year_single) {
            $asf_year_intermediate = array();
            $asf_year_intermediate["year"] = $asf_year_single["result_for_year"];
            array_push($asf_year_array, $asf_year_intermediate);
        }

        if(count($asf_year_array) > 0) {
            if($bool_return_json) return json_encode($asf_year_array);
            else return $asf_year_array;
        }
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>