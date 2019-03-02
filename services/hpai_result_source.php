<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo hpai_result_source(true);

function hpai_result_source($bool_return_json) {
    global $db_conn;

    $hpai_source_query = $db_conn->prepare("SELECT DISTINCT starting_subdistrict_code, subdistrict_name_th, 
                                                             district_name_th, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON starting_subdistrict_code = subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'HPAI'
                                                AND result_for_year = :year");
    $hpai_source_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($hpai_source_query->execute()) {
        $hpai_source_list = $hpai_source_query->fetchAll();
        $hpai_source_array = array();

        foreach($hpai_source_list as $hpai_source_single) {
            $hpai_source_intermediate = array();
            $hpai_source_intermediate["subdistrict_code"] = $hpai_source_single["starting_subdistrict_code"];
            $hpai_source_intermediate["subdistrict_name"] = $hpai_source_single["subdistrict_name_th"];
            $hpai_source_intermediate["district_name"] = $hpai_source_single["district_name_th"];
            $hpai_source_intermediate["province_name"] = $hpai_source_single["province_name_th"];
            array_push($hpai_source_array, $hpai_source_intermediate);
        }

        if(count($hpai_source_array) > 0) {
            if($bool_return_json) return json_encode($hpai_source_array);
            else return $hpai_source_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>