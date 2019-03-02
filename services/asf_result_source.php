<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo asf_result_source(true);

function asf_result_source($bool_return_json) {
    global $db_conn;

    $asf_source_query = $db_conn->prepare("SELECT DISTINCT starting_subdistrict_code, subdistrict_name_th, 
                                                             district_name_th, province_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON starting_subdistrict_code = subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                               JOIN province_master ON district_master.province_code = province_master.province_code
                                              WHERE execute_type_name = 'ASF'
                                                AND result_for_year = :year");
    $asf_source_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    if($asf_source_query->execute()) {
        $asf_source_list = $asf_source_query->fetchAll();
        $asf_source_array = array();

        foreach($asf_source_list as $asf_source_single) {
            $asf_source_intermediate = array();
            $asf_source_intermediate["subdistrict_code"] = $asf_source_single["starting_subdistrict_code"];
            $asf_source_intermediate["subdistrict_name"] = $asf_source_single["subdistrict_name_th"];
            $asf_source_intermediate["district_name"] = $asf_source_single["district_name_th"];
            $asf_source_intermediate["province_name"] = $asf_source_single["province_name_th"];
            array_push($asf_source_array, $asf_source_intermediate);
        }

        if(count($asf_source_array) > 0) {
            if($bool_return_json) return json_encode($asf_source_array);
            else return $asf_source_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>