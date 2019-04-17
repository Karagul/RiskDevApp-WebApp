<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo hpai_starting_district(true);

function hpai_starting_district($bool_return_json) {
    global $db_conn;

    $hpai_district_query = $db_conn->prepare("SELECT DISTINCT district_master.district_code, district_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                              WHERE execute_type_name = 'HPAI' AND result_for_year = :year AND district_master.province_code = :provinceCode
                                              ORDER BY district_name_th ASC");
    $hpai_district_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $hpai_district_query->bindValue(":provinceCode", $_GET["province_code"], PDO::PARAM_STR);
    if($hpai_district_query->execute()) {
        $hpai_district_list = $hpai_district_query->fetchAll();
        $hpai_district_array = array();

        foreach($hpai_district_list as $hpai_source_single) {
            $hpai_source_intermediate = array();
            $hpai_source_intermediate["district_code"] = $hpai_source_single["district_code"];
            //beg+++eKS10.04.2019 Removing กิ่งอำเภอ
            // $hpai_source_intermediate["district_name_th"] = $hpai_source_single["district_name_th"];
            $hpai_source_intermediate["district_name_th"] = edit_district($hpai_source_single["district_name_th"]);
            //end+++eKS10.04.2019 Removing กิ่งอำเภอ
            array_push($hpai_district_array, $hpai_source_intermediate);
        }

        if(count($hpai_district_array) > 0) {
            if($bool_return_json) return json_encode($hpai_district_array);
            else return $hpai_district_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>