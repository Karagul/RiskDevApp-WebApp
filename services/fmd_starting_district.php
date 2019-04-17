<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo fmd_starting_district(true);

function fmd_starting_district($bool_return_json) {
    global $db_conn;

    $fmd_district_query = $db_conn->prepare("SELECT DISTINCT district_master.district_code, district_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                              WHERE execute_type_name = 'FMD' AND result_for_year = :year AND district_master.province_code = :provinceCode
                                              ORDER BY district_name_th ASC");
    $fmd_district_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $fmd_district_query->bindValue(":provinceCode", $_GET["province_code"], PDO::PARAM_STR);
    if($fmd_district_query->execute()) {
        $fmd_district_list = $fmd_district_query->fetchAll();
        $fmd_district_array = array();

        foreach($fmd_district_list as $fmd_source_single) {
            $fmd_source_intermediate = array();
            $fmd_source_intermediate["district_code"] = $fmd_source_single["district_code"];
            //beg+++eKS10.04.2019 Removing กิ่งอำเภอ
            //$fmd_source_intermediate["district_name_th"] = $fmd_source_single["district_name_th"];
            $fmd_source_intermediate["district_name_th"] = edit_district($fmd_source_single["district_name_th"]);
            //end+++eKS10.04.2019 Removing กิ่งอำเภอ
            array_push($fmd_district_array, $fmd_source_intermediate);
        }

        if(count($fmd_district_array) > 0) {
            if($bool_return_json) return json_encode($fmd_district_array);
            else return $fmd_district_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>