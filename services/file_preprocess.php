<?php 
require_once dirname(__FILE__)."/../config.php";

// Settings for file path
$path_result = dirname(__FILE__)."/../results/";
$path_rmodel = dirname(__FILE__)."/../scripts/R_model/";
$path_script = dirname(__FILE__)."/../scripts/Scripts/";

// Parsing request parameters
$current_filename = $_POST["file_name"];
$current_filetype = $_POST["file_type"];
$current_filepath = "$path_result$current_filename";

// Calling the Python script, according to the file type
switch($current_filetype) {
    case "GENERAL - ไฟล์ประชากรสัตว์ (Animal Population)":
        $command_string = escapeshellcmd($path_script."NIPAH_processPopulation.py $path_result $path_rmodel $current_filepath");
        $command_output = die($python_bin." $command_string 2>&1");

        //$command_string = escapeshellcmd($path_script."fixPop.py $current_filepath $path_upload"); 
        //$command_output .= exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "GENERAL - ไฟล์การเคลื่อนย้ายสัตว์ (E-Movement)":
        $command_string = escapeshellcmd($path_script."processEmove.py $path_result $path_rmodel $current_filepath True");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "ASF - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)":
        $command_string = escapeshellcmd($path_script."ASF_processSubdistrictRisk.py $path_result $path_rmodel $current_filepath");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "FMD - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)":
        $command_string = escapeshellcmd($path_script."FMD_processSubdistrictRisk.py $path_result $path_rmodel $current_filepath");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "FMD - ไฟล์ประชากรสัตว์ (Animal Population)":
        $path_fmd_movement   = $current_filepath + "/E_Movement_FMD_2017.csv";
        $path_fmd_population = $current_filepath + "/Population_FMD_2017.csv";

        $command_string = escapeshellcmd($path_script."FMD_fixPop.py $path_fmd_movement $path_fmd_population $path_result");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "HPAI - ไฟล์ประชากรสัตว์ (Animal Population)":
        $path_hpai_movement   = $current_filepath + "/E_Movement_HPAI_2017.csv";
        $path_hpai_population = $current_filepath + "/Population_HPAI_2017.csv";

        $command_string = escapeshellcmd($path_script."HPAI_fixPop.py $path_fmd_movement $path_fmd_population $path_result");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "HPAI - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)":
        $command_string = escapeshellcmd($path_script."HPAI_processSubdistrictRisk.py $path_result $path_rmodel $current_filepath");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "NIPAH - ไฟล์การเคลื่อนย้ายสัตว์ (Pig Movement)": 
        $command_string = escapeshellcmd($path_script."processEmove.py $path_result $path_rmodel $current_filepath True"); 
        $command_output = die($python_bin." $command_string 2>&1");
        die($command_output);
    case "NIPAH - ไฟล์ประชากรหมู (Pig Population)": 
        $command_string = escapeshellcmd($path_script."NIPAH_fixPop.py $current_filepath $path_result"); 
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    case "NIPAH - ไฟล์ความเสี่ยงการเกิดโรค (Initial Risk)": 
        $command_string = escapeshellcmd($path_script."NIPAH_processSubdistrictRisk.py $path_result $path_rmodel $current_filepath");
        $command_output = exec($python_bin." $command_string 2>&1");
        die($command_output);
    default: die("ไม่สามารถตัดไฟล์ได้ กรุณาติดต่อผู้ดูแลระบบ");
}
?>