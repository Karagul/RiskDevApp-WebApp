<?php
require_once dirname(__FILE__)."/../config.php";

//if(isset($_GET["result_type"]) && isset($_GET["year"]) && isset($_GET["source"])) FMD_result_get($_GET["year"], $_GET["source"]);
// if(isset($_GET["year"]) && isset($_GET["source"])) fmd_result_get($_GET["year"], $_GET["source"], $_GET["with_normdist"], $_GET["initial_view"]);
// else fmd_result_get("2017", "140908;150306;170403;190502;200602;200605;240106;240107;240211;240212;260307;260308", "True", "False");
fmd_result_get("2017", "160109;160201;160202;160203;160204;160207;160208;160406;160407;160701;161001;161002;161003;161006;191101;191102;191104;191107;191109;191201;191203;200207;200301;200707;220703;270401;270406;270704;270902;300210;300302;300305;300805;301405;301807;301808;302001;302004;302005;302007;302009;302010;302101;302102;302103;302106;302107;302112;302503;311205;360901;360902;400110;400118;400711;420703;421301;501201;501305;501306;502101;502102;502103;502301;502302;502303;510201;510702;570906;630503;700405;700505;700506;700509;700702;700707;710106;710113;710502;710503;710504;710505;710506;710515;710517;710602;710603;710606;710609;710610;710613;711102;711201;730120;730121;730125;730203;730207;730210;730213;730214;760401;760404;760408;760513;760709;760804;770102;770103;770105;770106;770201;770202;770206;770207;770305;770504;770602;770607;770703;770705;770802;770803;770805;860202;860304;900104;900401;930108;930109;930203;930505;931101", "True", "False");

function fmd_result_get($selected_year, $selected_subdistrict_code, $bool_with_normdist, $bool_initial_view) {
    global $db_conn;

    //beg+++iKS12.12.2018 Allowing multiple source subdistricts
    if(strpos($selected_subdistrict_code, ";") !== false) {
        // Multiple source subdistricts detected
        $subdistrict_array = explode(";", $selected_subdistrict_code);
    } else {
        $subdistrict_array = array($selected_subdistrict_code);
    }
    //end+++iKS12.12.2018 Allowing multiple source subdistricts

    if(gettype($bool_with_normdist) != "boolean") {
        if(strtoupper($bool_with_normdist) == "TRUE") $bool_with_normdist = true;
        else $bool_with_normdist = false;
    }

    if(gettype($bool_initial_view) != "boolean") {
        if(strtoupper($bool_initial_view) == "TRUE") $bool_initial_view = true;
        else $bool_initial_view = false;
    }

    // Colour variables
    $risk_level_5 = "#B22222";
    $risk_level_4 = "#FF6347";
    $risk_level_3 = "#FFA500";
    $risk_level_2 = "#FFD700";
    $risk_level_1 = "#FFFACD";

    if($bool_initial_view == true) {
        $fmd_result_query = $db_conn->prepare("SELECT DISTINCT starting_subdistrict_code
                                                 FROM execute_result
                                                WHERE execute_type_name = 'FMD'
                                                  AND result_for_year = :year
                                                ORDER BY starting_subdistrict_code");
    } else if($bool_with_normdist == false) {
        $fmd_result_query = $db_conn->prepare("SELECT resulting_subdistrict_code,
                                                      MAX(risk_level_final) AS risk_level_final
                                                 FROM execute_result
                                                WHERE execute_type_name = 'FMD' 
                                                  AND result_for_year = :year
                                                  AND starting_subdistrict_code IN (".implode(",", $subdistrict_array).")
                                                GROUP BY resulting_subdistrict_code
                                                ORDER BY resulting_subdistrict_code");
    } else if($bool_with_normdist == true) {
        $fmd_result_query = $db_conn->prepare("SELECT resulting_subdistrict_code,
                                                      MAX(risk_level_normdist) AS risk_level_final
                                                 FROM execute_result
                                                WHERE execute_type_name = 'FMD' 
                                                  AND result_for_year = :year
                                                  AND starting_subdistrict_code IN (".implode(",", $subdistrict_array).")
                                                GROUP BY resulting_subdistrict_code
                                                ORDER BY resulting_subdistrict_code");
    }
    $fmd_result_query->bindValue(":year", $selected_year, PDO::PARAM_STR);
    //end+++eKS12.12.2018 Allowing multiple source subdistricts
    if($fmd_result_query->execute()) {
        $fmd_result_all = $fmd_result_query->fetchAll();
        
        $fmd_result_array = array("type" => "FeatureCollection",
                                  "features" => array());
									
        foreach($fmd_result_all as $fmd_result_single) {
            // Getting Subdistrict KML 
            $subdistrict_kml_query = $db_conn->prepare("SELECT subdistrict_master.subdistrict_code, subdistrict_name_th, subdistrict_latitude, subdistrict_longitude,
                                                               subdistrict_kml, district_name_th, province_name_th
                                                          FROM subdistrict_kml
                                                          JOIN subdistrict_master ON subdistrict_kml.subdistrict_code = subdistrict_master.subdistrict_code
                                                          JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                                          JOIN province_master ON district_master.province_code = province_master.province_code
                                                         WHERE subdistrict_kml.subdistrict_code = :subdistrictcode");
            if($bool_initial_view == true) {
                $subdistrict_kml_query->bindParam(":subdistrictcode", $fmd_result_single["starting_subdistrict_code"], PDO::PARAM_STR);
            } else {
                $subdistrict_kml_query->bindParam(":subdistrictcode", $fmd_result_single["resulting_subdistrict_code"], PDO::PARAM_STR);
            }
            
            if($subdistrict_kml_query->execute()) {
                $subdistrict_name               = array();
                $subdistrict_kml_result         = $subdistrict_kml_query->fetchAll();
                #$subdistrict_coordinate_segment = array();
                $subdistrict_segment_ordered    = array();
				
                foreach($subdistrict_kml_result as $subdistrict_kml_single) {
                    $current_subdistrict_kml = new SubdistrictKML($subdistrict_kml_single["subdistrict_code"], $subdistrict_kml_single["subdistrict_kml"]);
                    #array_push($subdistrict_coordinate_segment, $current_subdistrict_kml)
                    array_push($subdistrict_segment_ordered, $current_subdistrict_kml->get_coordinate_array());
                    
                    // Adding subdistrict master information
                    $subdistrict_name[$subdistrict_kml_single["subdistrict_code"]]["name"] = $subdistrict_kml_single["subdistrict_name_th"];
                }
				
                // Parsing Colour Hex
                if($bool_initial_view == false) {
                    // Parsing colour hex according to the settings
                    switch($fmd_result_single["risk_level_final"]) {
                        case 5: $current_colour = $risk_level_5; break;
                        case 4: $current_colour = $risk_level_4; break;
                        case 3: $current_colour = $risk_level_3; break;
                        case 2: $current_colour = $risk_level_2; break;
                        case 1: $current_colour = $risk_level_1; break;
                    }
                } else {
                    $current_colour = $risk_level_5;
                    $fmd_result_single["risk_level_final"] = 1;
                }

                // Parsing GeoJSON array
                $current_feature = array();
                $current_feature["type"] = "Feature";
				//beg+++eKS20.11.2018 Adapting for PHP5.5
                //$current_feature["geometry"] = array("type" => "Polygon",
                //                                     "coordinates" => $subdistrict_segment_ordered);
				$geometry_array = array();
				$geometry_array["type"] = "Polygon";
				$geometry_array["coordinates"] = $subdistrict_segment_ordered;
				$current_feature["geometry"] = $geometry_array;
                
                if($bool_initial_view == true) {
                    $current_subdistrict_code = $fmd_result_single["starting_subdistrict_code"];
                } else {
                    $current_subdistrict_code = $fmd_result_single["resulting_subdistrict_code"];
                }

                $current_feature["properties"] = array("subdistrict_code"   => $current_subdistrict_code,
                                                       "subdistrict_name"   => $subdistrict_name[$current_subdistrict_code]["name"],
                                                       "district_name"      => $subdistrict_kml_result[0]["district_name_th"],
                                                       "province_name"      => $subdistrict_kml_result[0]["province_name_th"],
                                                       "fill"               => $current_colour,
                                                       "fill-opacity"       => 0.5,
                                                       "latitude"           => $subdistrict_kml_result[0]["subdistrict_latitude"],
                                                       "longitude"          => $subdistrict_kml_result[0]["subdistrict_longitude"],
                                                       "risk_level_final"   => $fmd_result_single["risk_level_final"],
                                                       "stroke"             => "#FFFFFF",
                                                       "stroke-width"       => 2);
				//end+++eKS20.11.2018 Adapting for PHP5.5
                array_push($fmd_result_array["features"], $current_feature);
            } else die(var_dump($subdistrict_kml_query->errorInfo()));
        }

        if(count($fmd_result_array) > 0) die(json_encode($fmd_result_array));
        else return "ไม่พบข้อมูล กรุณาลองอีกครั้ง";
    } else die(var_dump($fmd_result_query->errorInfo()));
}

class Coordinate {
    public $latitude  = 0.0;
    public $longitude = 0.0;

    public function __construct($latitude, $longitude) {
        $this->latitude  = $latitude;
        $this->longitude = $longitude;
    }
}

class SubdistrictKML {
    public $subdistrict_code;
    public $subdistrict_kml_array;

    public function __construct($subdistrict_code, $subdistrict_kml) {
        $this->subdistrict_code = $subdistrict_code;
        $this->subdistrict_kml_array = array();

        $subdistrict_string_tokenizer = explode("), (", $subdistrict_kml);    
        foreach($subdistrict_string_tokenizer as $subdistrict_string_single) {
            $current_kml_string = str_replace("(", "", $subdistrict_string_single);
            $current_kml_string = str_replace(")", "", $current_kml_string);
            $current_kml_coordinate = explode(",", $current_kml_string);
            #array_push($this->subdistrict_kml_array, new Coordinate(doubleval($current_kml_coordinate[0]), doubleval($current_kml_coordinate[1])));
            try {
                array_push($this->subdistrict_kml_array, new Coordinate(doubleval($current_kml_coordinate[0]), doubleval($current_kml_coordinate[1])));
            } catch(Exception $e) {
                die(var_dump($current_kml_coordinate));
            }
        }
    }

    public function get_coordinate_array() {
        $return_array = array();
        foreach($this->subdistrict_kml_array as $subdistrict_kml_single) {
            array_push($return_array, array($subdistrict_kml_single->latitude, $subdistrict_kml_single->longitude));
        }
        return $return_array;
    }

    public function get_coordinate_string() {
        $return_string = "";
        foreach($this->subdistrict_kml_array as $subdistrict_kml_single) {
            if($return_string != "") {
                $return_string .= ", [" . number_format($subdistrict_kml_single->latitude, 6) . ", " . number_format($subdistrict_kml_single->longitude, 6) . "]";
            } else $return_string .= "[" . number_format($subdistrict_kml_single->latitude, 6) . ", " . number_format($subdistrict_kml_single->longitude, 6) . "]";
        }
        return $return_string;
    }

    public function get_first_coordinate() {
        return $this->subdistrict_kml_array[0];
    }

    public function get_last_coordinate() {
        return $this->subdistrict_kml_array[count($this->subdistrict_kml_array) - 1];
    }

    public function invert_coordinate_list() {
        $current_list_size        = count($this->subdistrict_kml_array);
        $current_list_size_halved = $current_list_size / 2;

        for($count = 0; $count < $current_list_size_halved; $count ++) {
            $temp_coordinate                                              = $this->subdistrict_kml_array[$current_list_size - $count - 1];
            $this->subdistrict_kml_array[$current_list_size - $count - 1] = $this->subdistrict_kml_array[$count];
            $this->subdistrict_kml_array[$count]                          = $temp_coordinate;
        }
    }
}

function get_euclidean_distance($coordinate_first, $coordinate_second) {
    return sqrt(pow(($coordinate_first->latitude  - $coordinate_second->latitude), 2) + 
                pow(($coordinate_first->longitude - $coordinate_second->longitude), 2));
}
?>