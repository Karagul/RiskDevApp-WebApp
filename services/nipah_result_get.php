<?php
require_once dirname(__FILE__)."/../config.php";

//if(isset($_GET["result_type"]) && isset($_GET["year"]) && isset($_GET["source"])) nipah_result_get($_GET["year"], $_GET["source"]);
if(isset($_GET["year"]) && isset($_GET["source"])) nipah_result_get($_GET["year"], $_GET["source"]);
else nipah_result_get("2017", "140908");

function nipah_result_get($selected_year, $selected_subdistrict_code) {
    global $db_conn;
	
    // Colour variables
    $risk_level_5 = "#B22222";
    $risk_level_4 = "#FF6347";
    $risk_level_3 = "#FFA500";
    $risk_level_2 = "#FFD700";
    $risk_level_1 = "#FFFACD";

    // Checking the input subdistrict code
    $nipah_result_query = $db_conn->prepare("SELECT *
                                               FROM result_nipah
                                              WHERE execute_first_date LIKE :yearPattern
                                                AND starting_subdistrict_code = :subdistrictCode");
    $nipah_result_query->bindValue(":yearPattern", $selected_year."%", PDO::PARAM_STR);
    $nipah_result_query->bindValue(":subdistrictCode", $selected_subdistrict_code, PDO::PARAM_STR);
    if($nipah_result_query->execute()) {
        $nipah_result_all = $nipah_result_query->fetchAll();
        $nipah_result_array = array("type" => "FeatureCollection",
                                    "features" => array());
									
        foreach($nipah_result_all as $nipah_result_single) {
            // Getting Subdistrict KML 
            $subdistrict_kml_query = $db_conn->prepare("SELECT *
                                                          FROM subdistrict_kml
                                                          JOIN subdistrict_master ON subdistrict_kml.subdistrict_code = subdistrict_master.subdistrict_code
                                                         WHERE subdistrict_kml.subdistrict_code = :subdistrictcode");
            $subdistrict_kml_query->bindParam(":subdistrictcode", $nipah_result_single["resulting_subdistrict_code"], PDO::PARAM_STR);
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
                switch($nipah_result_single["risk_level_final"]) {
                    case 5: $current_colour = $risk_level_5; break;
                    case 4: $current_colour = $risk_level_4; break;
                    case 3: $current_colour = $risk_level_3; break;
                    case 2: $current_colour = $risk_level_2; break;
                    case 1: $current_colour = $risk_level_1; break;
                    default: $current_colour = $risk_level_5;
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
				
                $current_feature["properties"] = array("subdistrict_code"   => $nipah_result_single["resulting_subdistrict_code"],
                                                       "subdistrict_name"   => $subdistrict_name[$nipah_result_single["resulting_subdistrict_code"]]["name"],
                                                       "fill"               => $current_colour,
                                                       "fill-opacity"       => 0.75,
                                                       "risk_level_final"   => $nipah_result_single["risk_level_final"],
                                                       "risk_level_average" => $nipah_result_single["risk_level_average"],
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