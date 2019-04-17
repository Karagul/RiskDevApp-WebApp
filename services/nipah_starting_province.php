<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo nipah_starting_province(true);

function nipah_starting_province($bool_return_json) {
    global $db_conn;

    $nipah_province_query = $db_conn->prepare("SELECT DISTINCT province_master.province_code, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'NIPAH' AND result_for_year = :year
                                              ORDER BY province_name_th ASC");
    $nipah_province_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($nipah_province_query->execute()) {
        $nipah_province_list = $nipah_province_query->fetchAll();
        $nipah_province_array = array();

        foreach($nipah_province_list as $nipah_source_single) {
            $nipah_source_intermediate = array();
            $nipah_source_intermediate["province_code"] = $nipah_source_single["province_code"];
            $nipah_source_intermediate["province_name_th"] = $nipah_source_single["province_name_th"];
            array_push($nipah_province_array, $nipah_source_intermediate);
        }

        if(count($nipah_province_array) > 0) {
            if($bool_return_json) return json_encode($nipah_province_array);
            else return $nipah_province_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>