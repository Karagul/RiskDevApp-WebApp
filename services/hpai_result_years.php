<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo hpai_result_years(true);

function hpai_result_years($bool_return_json) {
    global $db_conn;

    $hpai_year_query = $db_conn->prepare("SELECT DISTINCT result_for_year 
                                             FROM execute_result
                                            WHERE execute_type_name = 'HPAI'");
    if($hpai_year_query->execute()) {
        $hpai_year_list = $hpai_year_query->fetchAll();
        $hpai_year_array = array();

        foreach($hpai_year_list as $hpai_year_single) {
            $hpai_year_intermediate = array();
            $hpai_year_intermediate["year"] = explode("-", $hpai_year_single["execute_first_date"])[0];
            array_push($hpai_year_array, $hpai_year_intermediate);
        }

        if(count($hpai_year_array) > 0) {
            if($bool_return_json) return json_encode($hpai_year_array);
            else return $hpai_year_array;
        }
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>