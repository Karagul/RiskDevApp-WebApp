<?php
require_once dirname(__FILE__)."/../config.php";

if(isset($_GET["return_type"])) echo nipah_starting_district(true);

function nipah_starting_district($bool_return_json) {
    global $db_conn;

    $nipah_district_query = $db_conn->prepare("SELECT DISTINCT district_master.district_code, district_name_th
                                               FROM execute_result
                                               JOIN subdistrict_master ON execute_result.starting_subdistrict_code = subdistrict_master.subdistrict_code
                                               JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                              WHERE execute_type_name = 'NIPAH' AND result_for_year = :year AND district_master.province_code = :provinceCode
                                              ORDER BY district_name_th ASC");
    $nipah_district_query->bindValue(":year", $_GET["year"], PDO::PARAM_STR);
    $nipah_district_query->bindValue(":provinceCode", $_GET["province_code"], PDO::PARAM_STR);
    if($nipah_district_query->execute()) {
        $nipah_district_list = $nipah_district_query->fetchAll();
        $nipah_district_array = array();

        foreach($nipah_district_list as $nipah_source_single) {
            $nipah_source_intermediate = array();
            $nipah_source_intermediate["district_code"] = $nipah_source_single["district_code"];
            //beg+++eKS10.04.2019 Removing กิ่งอำเภอ
            // $nipah_source_intermediate["district_name_th"] = $nipah_source_single["district_name_th"];
            $nipah_source_intermediate["district_name_th"] = edit_district($nipah_source_single["district_name_th"]);
            //end+++eKS10.04.2019 Removing กิ่งอำเภอ
            array_push($nipah_district_array, $nipah_source_intermediate);
        }

        if(count($nipah_district_array) > 0) {
            if($bool_return_json) return json_encode($nipah_district_array);
            else return $nipah_district_array;
        } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
    } else return "ไม่พบข้อมูลการวิเคราะห์ กรุณาตรวจสอบอีกครั้ง";
}
?>