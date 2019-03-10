<?php
require_once dirname(__FILE__)."/../config.php";

//if(isset($_GET["result_type"]) && isset($_GET["year"]) && isset($_GET["source"])) nipah_result_get($_GET["year"], $_GET["source"]);
if(isset($_GET["year"]) && isset($_GET["source"])) nipah_result_get($_GET["year"], $_GET["source"], $_GET["with_normdist"], $_GET["initial_view"]);
else nipah_result_get("2017", "140908;150306;170403;190502;200602;200605;240106;240107;240211;240212;260307;260308", "True", "False");

function nipah_result_get($selected_year, $selected_subdistrict_code, $bool_with_normdist, $bool_initial_view) {
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
        $nipah_result_query = $db_conn->prepare("SELECT DISTINCT starting_subdistrict_code
                                                 FROM execute_result
                                                WHERE execute_type_name = 'NIPAH'
                                                  AND result_for_year = :year
                                                ORDER BY starting_subdistrict_code");
        $nipah_result_query->bindValue(":year", $selected_year, PDO::PARAM_STR);
    } else {
        $nipah_result_query = $db_conn->prepare("SELECT resulting_subdistrict_code,
                                                      MAX(risk_level_final) AS risk_level_final
                                                 FROM execute_result
                                                WHERE execute_type_name = 'NIPAH' 
                                                  AND result_for_year = :year
                                                  AND starting_subdistrict_code IN (".implode(",", $subdistrict_array).")
                                                GROUP BY resulting_subdistrict_code
                                                ORDER BY resulting_subdistrict_code");
        $nipah_result_query->bindValue(":year", $selected_year, PDO::PARAM_STR);
    }
    //end+++eKS12.12.2018 Allowing multiple source subdistricts
    if($nipah_result_query->execute()) {
        $nipah_result_all = $nipah_result_query->fetchAll();

        //beg+++iKS12.12.2018 Pre-calculating the normal distribution
        if($bool_with_normdist == true) {
            // Enabled: Display colour intensity according to the normal distribution
            $nipah_result_dof  = count($nipah_result_all) - 1;
            $nipah_result_mean = 0.0;
            $nipah_result_std  = 0.0;

            if($nipah_result_dof == 0) $nipah_result_dof = 1;

            // Finding the mean
            foreach($nipah_result_all as $nipah_result_single) {
                $nipah_result_mean += $nipah_result_single["risk_level_final"];
            }
            $nipah_result_mean /= count($nipah_result_all);
            
            // Finding the standard deviation
            $nipah_result_std_sigma = 0.0;
            foreach($nipah_result_all as $nipah_result_single) {
                $nipah_result_std_sigma += pow($nipah_result_single["risk_level_final"] - $nipah_result_mean, 2);
            }
            $nipah_result_std = sqrt($nipah_result_std_sigma / $nipah_result_dof);

            $nipah_result_percentile_75 = $nipah_result_mean + (0.675 * $nipah_result_std);
            $nipah_result_percentile_25 = $nipah_result_mean - (0.675 * $nipah_result_std);
        }
        //end+++iKS12.12.2018 Pre-calculating the normal distribution
        
        $nipah_result_array = array("type" => "FeatureCollection",
                                  "features" => array());
									
        foreach($nipah_result_all as $nipah_result_single) {
            // Getting Subdistrict KML 
            $subdistrict_kml_query = $db_conn->prepare("SELECT subdistrict_master.subdistrict_code, subdistrict_name_th, subdistrict_latitude, subdistrict_longitude,
                                                               subdistrict_kml, district_name_th, province_name_th
                                                          FROM subdistrict_kml
                                                          JOIN subdistrict_master ON subdistrict_kml.subdistrict_code = subdistrict_master.subdistrict_code
                                                          JOIN district_master ON subdistrict_master.district_code = district_master.district_code
                                                          JOIN province_master ON district_master.province_code = province_master.province_code
                                                         WHERE subdistrict_kml.subdistrict_code = :subdistrictcode");
            if($bool_initial_view == true) {
                $subdistrict_kml_query->bindParam(":subdistrictcode", $nipah_result_single["starting_subdistrict_code"], PDO::PARAM_STR);
            } else {
                $subdistrict_kml_query->bindParam(":subdistrictcode", $nipah_result_single["resulting_subdistrict_code"], PDO::PARAM_STR);
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
                if($bool_with_normdist) {
                    // Parsing colour hex according to the normal distribution
                    if($nipah_result_single["risk_level_final"] >= $nipah_result_percentile_75) {
                        $current_colour = $risk_level_5;
                        $nipah_result_single["risk_level_final"] = 5;
                    } else if($nipah_result_single["risk_level_final"] >= $nipah_result_mean) {
                        $current_colour = $risk_level_4;
                        $nipah_result_single["risk_level_final"] = 4;
                    } else if($nipah_result_single["risk_level_final"] >= $nipah_result_percentile_25) {
                        $current_colour = $risk_level_3;
                        $nipah_result_single["risk_level_final"] = 3;
                    } else {
                        $current_colour = $risk_level_2;
                        $nipah_result_single["risk_level_final"] = 2;
                    }
                } else if(!$bool_initial_view) {
                    // Parsing colour hex according to the settings
                    switch($nipah_result_single["risk_level_final"]) {
                        case 5: $current_colour = $risk_level_5; break;
                        case 4: $current_colour = $risk_level_4; break;
                        case 3: $current_colour = $risk_level_3; break;
                        case 2: $current_colour = $risk_level_2; break;
                        case 1: $current_colour = $risk_level_1; break;
                    }
                } else {
                    $current_colour = $risk_level_5;
                    $nipah_result_single["risk_level_final"] = 1;
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
                    $current_subdistrict_code = $nipah_result_single["starting_subdistrict_code"];
                } else {
                    $current_subdistrict_code = $nipah_result_single["resulting_subdistrict_code"];
                }

                $current_feature["properties"] = array("subdistrict_code"   => $current_subdistrict_code,
                                                       "subdistrict_name"   => $subdistrict_name[$current_subdistrict_code]["name"],
                                                       "district_name"      => $subdistrict_kml_result[0]["district_name_th"],
                                                       "province_name"      => $subdistrict_kml_result[0]["province_name_th"],
                                                       "fill"               => $current_colour,
                                                       "fill-opacity"       => 0.5,
                                                       "latitude"           => $subdistrict_kml_result[0]["subdistrict_latitude"],
                                                       "longitude"          => $subdistrict_kml_result[0]["subdistrict_longitude"],
                                                       "risk_level_final"   => $nipah_result_single["risk_level_final"],
                                                       "stroke"             => "#FFFFFF",
                                                       "stroke-width"       => 2);
				//end+++eKS20.11.2018 Adapting for PHP5.5
                array_push($nipah_result_array["features"], $current_feature);
            } else die(var_dump($subdistrict_kml_query->errorInfo()));
        }

        if(count($nipah_result_array) > 0) die(json_encode($nipah_result_array));
        else return "ไม่พบข้อมูล กรุณาลองอีกครั้ง";
    } else die(var_dump($nipah_result_query->errorInfo()));
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