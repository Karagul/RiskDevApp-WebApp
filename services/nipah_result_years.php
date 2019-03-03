<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo nipah_result_years(true);

function nipah_result_years($bool_return_json) {
    global $db_conn;

    $nipah_year_query = $db_conn->prepare("SELECT DISTINCT result_for_year 
                                             FROM execute_result
                                            WHERE execute_type_name = 'NIPAH'");
    if($nipah_year_query->execute()) {
        $nipah_year_list = $nipah_year_query->fetchAll();
        $nipah_year_array = array();

        foreach($nipah_year_list as $nipah_year_single) {
            $nipah_year_intermediate = array();
            $nipah_year_intermediate["year"] = $nipah_year_single["result_for_year"];
            array_push($nipah_year_array, $nipah_year_intermediate);
        }

        if(count($nipah_year_array) > 0) {
            if($bool_return_json) return json_encode($nipah_year_array);
            else return $nipah_year_array;
        }
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>