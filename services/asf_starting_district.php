<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo asf_starting_district(true);

function asf_starting_district($bool_return_json) {
    global $db_conn;

    $asf_district_query = $db_conn->prepare("SELECT DISTINCT district_master.district_code, district_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                              WHERE execute_type_name = 'ASF' AND result_for_year = :year AND district_master.province_code = :provinceCode
                                              ORDER BY district_name_th ASC");
    $asf_district_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $asf_district_query->bindValue(":provinceCode", $_GET["province_code"], PDO::PARAM_STR);
    if($asf_district_query->execute()) {
        $asf_district_list = $asf_district_query->fetchAll();
        $asf_district_array = array();

        foreach($asf_district_list as $asf_source_single) {
            $asf_source_intermediate = array();
            $asf_source_intermediate["district_code"] = $asf_source_single["district_code"];
            //beg+++eKS10.04.2019 Removing กิ่งอำเภอ
            //$asf_source_intermediate["district_name_th"] = $asf_source_single["district_name_th"];
            $asf_source_intermediate["district_name_th"] = edit_district($asf_source_single["district_name_th"]);
            //end+++eKS10.04.2019 Removing กิ่งอำเภอ
            array_push($asf_district_array, $asf_source_intermediate);
        }

        if(count($asf_district_array) > 0) {
            if($bool_return_json) return json_encode($asf_district_array);
            else return $asf_district_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>