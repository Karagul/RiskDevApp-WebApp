<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo hpai_starting_province(true);

function hpai_starting_province($bool_return_json) {
    global $db_conn;

    $hpai_province_query = $db_conn->prepare("SELECT DISTINCT province_master.province_code, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'HPAI' AND result_for_year = :year
                                              ORDER BY province_name_th ASC");
    $hpai_province_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($hpai_province_query->execute()) {
        $hpai_province_list = $hpai_province_query->fetchAll();
        $hpai_province_array = array();

        foreach($hpai_province_list as $hpai_source_single) {
            $hpai_source_intermediate = array();
            $hpai_source_intermediate["province_code"] = $hpai_source_single["province_code"];
            $hpai_source_intermediate["province_name_th"] = $hpai_source_single["province_name_th"];
            array_push($hpai_province_array, $hpai_source_intermediate);
        }

        if(count($hpai_province_array) > 0) {
            if($bool_return_json) return json_encode($hpai_province_array);
            else return $hpai_province_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>