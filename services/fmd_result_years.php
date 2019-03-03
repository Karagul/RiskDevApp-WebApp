<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo fmd_result_years(true);

function fmd_result_years($bool_return_json) {
    global $db_conn;

    $fmd_year_query = $db_conn->prepare("SELECT DISTINCT result_for_year 
                                             FROM execute_result
                                            WHERE execute_type_name = 'FMD'");
    if($fmd_year_query->execute()) {
        $fmd_year_list = $fmd_year_query->fetchAll();
        $fmd_year_array = array();

        foreach($fmd_year_list as $fmd_year_single) {
            $fmd_year_intermediate = array();
            $fmd_year_intermediate["year"] = $fmd_year_single["result_for_year"];
            array_push($fmd_year_array, $fmd_year_intermediate);
        }

        if(count($fmd_year_array) > 0) {
            if($bool_return_json) return json_encode($fmd_year_array);
            else return $fmd_year_array;
        }
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>