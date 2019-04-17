<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo fmd_starting_province(true);

function fmd_starting_province($bool_return_json) {
    global $db_conn;

    $fmd_province_query = $db_conn->prepare("SELECT DISTINCT province_master.province_code, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'FMD' AND result_for_year = :year
                                              ORDER BY province_name_th ASC");
    $fmd_province_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($fmd_province_query->execute()) {
        $fmd_province_list = $fmd_province_query->fetchAll();
        $fmd_province_array = array();

        foreach($fmd_province_list as $fmd_source_single) {
            $fmd_source_intermediate = array();
            $fmd_source_intermediate["province_code"] = $fmd_source_single["province_code"];
            $fmd_source_intermediate["province_name_th"] = $fmd_source_single["province_name_th"];
            array_push($fmd_province_array, $fmd_source_intermediate);
        }

        if(count($fmd_province_array) > 0) {
            if($bool_return_json) return json_encode($fmd_province_array);
            else return $fmd_province_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>