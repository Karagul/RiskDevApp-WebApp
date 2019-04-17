<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo asf_starting_province(true);

function asf_starting_province($bool_return_json) {
    global $db_conn;

    $asf_province_query = $db_conn->prepare("SELECT DISTINCT province_master.province_code, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'ASF' AND result_for_year = :year
                                              ORDER BY province_name_th ASC");
    $asf_province_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($asf_province_query->execute()) {
        $asf_province_list = $asf_province_query->fetchAll();
        $asf_province_array = array();

        foreach($asf_province_list as $asf_source_single) {
            $asf_source_intermediate = array();
            $asf_source_intermediate["province_code"] = $asf_source_single["province_code"];
            $asf_source_intermediate["province_name_th"] = $asf_source_single["province_name_th"];
            array_push($asf_province_array, $asf_source_intermediate);
        }

        if(count($asf_province_array) > 0) {
            if($bool_return_json) return json_encode($asf_province_array);
            else return $asf_province_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>