<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo fmd_result_source(true);

function fmd_result_source($bool_return_json) {
    global $db_conn;

    $fmd_source_query = $db_conn->prepare("SELECT DISTINCT starting_subdistrict_code, subdistrict_name_th, 
                                                             district_name_th, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON starting_subdistrict_code = subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'FMD'
                                                AND result_for_year = :year");
    $fmd_source_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($fmd_source_query->execute()) {
        $fmd_source_list = $fmd_source_query->fetchAll();
        $fmd_source_array = array();

        foreach($fmd_source_list as $fmd_source_single) {
            $fmd_source_intermediate = array();
            $fmd_source_intermediate["subdistrict_code"] = $fmd_source_single["starting_subdistrict_code"];
            $fmd_source_intermediate["subdistrict_name"] = $fmd_source_single["subdistrict_name_th"];
            $fmd_source_intermediate["district_name"] = $fmd_source_single["district_name_th"];
            $fmd_source_intermediate["province_name"] = $fmd_source_single["province_name_th"];
            array_push($fmd_source_array, $fmd_source_intermediate);
        }

        if(count($fmd_source_array) > 0) {
            if($bool_return_json) return json_encode($fmd_source_array);
            else return $fmd_source_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>