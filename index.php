<!doctype html>
<?php
require_once dirname(__FILE__)."/services/file_get_list.php";
require_once dirname(__FILE__)."/services/file_get_type.php";

// Session check
//beg+++dKS20.11.2018 Omitting the session for testing other functionalities
session_start();
if(!isset($_SESSION["user_name"]) || !isset($_SESSION["user_type_desc"])) header("Location: login.php");
//end+++dKS20.11.2018 Omitting the session for testing other functionalities

// Initial Data
$list_file_type = file_get_type(false);

// Populating year list
$list_year    = [];
$loop_year    = "2017";
//$current_year = date("Y");
$current_year = "2017";
while($current_year >= $loop_year) {
    array_push($list_year, $current_year);
    $current_year = (string) intval($current_year) - 1;
}
?>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Home | RiskDevApp Console</title>
        <!-- Stylesheets -->
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" href="assets/css/jquery.dm-uploader.css">
        <link rel="stylesheet" href="assets/css/styles.css">
    </head>
    <body>
        <div class="container-fluid h-100">
            <div class="row h-100">
                <!-- Navigation Panel -->
                <div class="col-md-3 h-100 pt-2 pb-2" id="navigation-panel">
                    <!-- Navigation Panel: User Menu -->
                    <div class="card mb-2">
                        <div class="card-header font-weight-bold">ผู้ใช้งานระบบ</div>
                        <div class="card-body text-center">
                            <p class="lead"><?php echo $_SESSION["user_name"]; ?><br /><small class="text-muted"><?php echo $_SESSION["user_type_desc"]; ?></small></p>
                            <button type="button" class="btn btn-block btn-danger" onclick="user_logout()">ออกจากระบบ</button>
                        </div>
                    </div>

                    <!-- Navigation Panel: Navigation Menu -->
                    <div class="card">
                        <div class="card-header font-weight-bold">เมนูหลัก</div>
                        <div class="card-body">
                            <nav class="nav nav-pills flex-column">
                                <a class="nav-link active" data-toggle="pill" href="#menu-actions"><i class="fas fa-fw fa-play-circle mr-3"></i>วิเคราะห์ความเสี่ยง</a>
                                <a class="nav-link" data-toggle="pill" href="#menu-files"><i class="fas fa-fw fa-copy mr-3"></i>อัพโหลดไฟล์</a>
                                <a class="nav-link" data-toggle="pill" href="#menu-users"><i class="fas fa-fw fa-users-cog mr-3"></i>บัญชีผู้ใช้</a>
                                <a class="nav-link" data-toggle="pill" href="#menu-settings"><i class="fas fa-fw fa-cog mr-3"></i>การตั้งค่าระบบ</a>
                            </nav>
                        </div>
                    </div>
                </div>
                <!-- Navigation Contents -->
                <div class="col-md-9 h-100 pt-2 pb-2" id="navigation-content">
                    <div class="tab-content h-100">
                        <!-- Navigation Contents: Actions -->
                        <div class="tab-pane fade show active h-100" id="menu-actions">
                            <div class="card h-100">
                                <div class="card-header">
                                    <nav class="nav nav-tabs card-header-tabs">
                                        <a class="nav-link active" data-toggle="tab" href="#actions-calculation">การคำนวณความเสี่ยง</a>
                                    </nav>
                                </div>
                                <div class="card-body h-100 p-3">
                                    <div class="tab-content">
                                        <!-- Risk: Calculation -->
                                        <div class="tab-pane fade show active" id="actions-calculation">
                                            <div class="alert alert-secondary mb-2">
                                                <form class="form-inline">
                                                    <label class="mr-2">วิเคราะห์:</label>
                                                    <select class="form-control custom-select mr-2" id="actions-calculation-input-type"></select>
                                                    <label class="mr-2">สำหรับปี:</label>
                                                    <select class="form-control custom-select mr-2" id="actions-calculation-input-year">
                                                    <?php foreach($list_year as $year) echo '<option value="'.$year.'">'.$year.'</option>'; ?>
                                                    </select>
                                                    <button type="button" class="btn btn-success" id="actions-calculation-button-execute"><i class="fas fa-fw fa-play-circle mr-2"></i>เริ่มการวิเคราะห์</button>
                                                </form>
                                            </div>
                                            <div class="alert alert-warning mb-2">
                                                <form class="form-inline">
                                                    <label class="mr-2">แสดงการวิเคราะห์ประเภท:</label>
                                                    <select class="form-control custom-select" id="actions-calculation-display-type"></select>
                                                </form>
                                            </div>
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="thead-dark">
                                                    <th>ประเภท</th>
                                                    <th>วันที่ดำเนินการ</th>
                                                    <th>สำหรับปี</th>
                                                    <th>สถานะ</th>
                                                </thead>
                                                <tbody>
                                                    <tr><td colspan="4" class="text-center">&ndash;&nbsp;ไม่พบผลการวิเคราะห์ในระบบ&nbsp;&ndash;</td></tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Navigation Contents: Files -->
                        <div class="tab-pane fade h-100" id="menu-files">
                            <div class="card h-100">
                                <div class="card-header">
                                    <nav class="nav nav-tabs card-header-tabs">
                                        <a class="nav-link active" data-toggle="tab" href="#files-list">รายชื่อไฟล์ในระบบ</a>
                                    </nav>
                                </div>
                                <div class="card-body h-100 p-3">
                                    <div class="tab-content">
                                        <!-- File: List -->
                                        <div class="tab-pane fade show active h-100" id="files-list">
                                            <!-- File List: Actions -->
                                            <div class="alert alert-secondary mb-2">
                                                <form class="form-inline" id="file-upload-container">
                                                    <label class="mr-2">อัพโหลดไฟล์:</label>
                                                    <input type="file" class="d-none" id="file-upload">
                                                    <div class="input-group mr-2">
                                                        <input type="text" class="form-control" id="file-upload-selection-name" disabled>
                                                        <div class="input-group-append">
                                                            <button type="button" class="btn btn-primary" onclick="file_upload_selection()">เลือกไฟล์</button>
                                                        </div>
                                                    </div>
                                                    <label class="mr-2">ประเภท:</label>
                                                    <select class="form-control custom-select mr-2" id="file-upload-selection-type"></select>
                                                    <button type="button" class="btn btn-success" onclick="file_upload_commence()" disabled><i class="fas fa-fw fa-file-upload mr-2"></i>อัพโหลด</button>
                                                </form>
                                            </div>
                                            <!-- File List: Filtering -->
                                            <div class="alert alert-warning mb-2">
                                                <form class="form-inline">
                                                    <label class="mr-2">แสดงไฟล์ประเภท:</label>
                                                    <select class="form-control custom-select" id="files-list-selection-filter"></select>
                                                </form>
                                            </div>
                                            <!-- File List: Table --> 
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="thead-dark">
                                                    <th>ชื่อไฟล์</th>
                                                    <th>ประเภทของไฟล์</th>
                                                    <th>อัพโหลดเมื่อ</th>
                                                    <th>อัพโหลดโดย</th>
                                                    <th>การดำเนินการ</th>
                                                </thead>
                                                <tbody class="h-max-100"><td colspan="5">&ndash;&nbsp;ไม่พบไฟล์ในระบบ&nbsp;&ndash;</td></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Navigation Contents: Users -->
                        <div class="tab-pane fade h-100" id="menu-users">
                            <div class="card h-100">
                                <div class="card-header">
                                    <nav class="nav nav-tabs card-header-tabs">
                                        <a class="nav-link active" data-toggle="tab" href="#users-list">รายชื่อผู้ใช้</a>
                                    </nav>
                                </div>
                                <div class="card-body h-100 p-3">
                                    <div class="tab-content">
                                        <!-- Users: List -->
                                        <div class="tab-pane fade show active" id="users-list">
                                            <!-- User List: Actions -->
                                            <div class="alert alert-warning mb-2">
                                                <form class="form-inline">
                                                    <label class="mr-2">การดำเนินการ:</label>
                                                    <button type="button" class="btn btn-sm btn-success" onclick="modal_user_toggle('add')"><i class="fas fa-fw fa-plus mr-2"></i>เพิ่มบัญชีผู้ใช้</button>
                                                </form>
                                            </div>
                                            <!-- User List: Table -->
                                            <table class="table table-sm table-bordered table-hover">
                                                <thead class="thead-dark">
                                                    <th>ชื่อผู้ใช้</th>
                                                    <th>ประเภทของผู้ใช้</th>
                                                    <th>วันที่เริ่มใช้งาน</th>
                                                    <th>ใช้งานได้ถึง</th>
                                                    <th>การดำเนินการ</th>
                                                </thead>
                                                <tbody class="h-max-100"><td colspan="5">&ndash;&nbsp;ไม่พบผู้ใช้งานในระบบ&nbsp;&ndash;</td></tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- Navigation Contents: Settings -->
                        <div class="tab-pane fade h-100" id="menu-settings">
                            <div class="card h-100">
                                <div class="card-header">
                                    <nav class="nav nav-tabs card-header-tabs">
                                        <a class="nav-link active" data-toggle="tab" href="#settings-param">การตั้งค่าตัวแปร</a>
                                    </nav>
                                </div>
                                <div class="card-body h-100 p-3">
                                    <div class="container-fluid" id="settings-tiles">
                                        <div class="row" id="settings-tiles-container">
                                            <div class="col-12">
                                                <div class="jumbotron text-center mb-0">
                                                    <h1><i class="fas fa-3x fa-fw fa-exclamation-triangle mb-4"></i><br />ไม่พบการตั้งค่าในระบบ<br /></h1>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Parameters -->
        <div class="modal fade" tabindex="-1" id="modal-parameter">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">แก้ไขค่าตัวแปร</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form class="form-inline">
                            <input type="hidden" id="modal-parameter-param-name">
                            <label class="mr-2" id="modal-parameter-param-desc"></label>
                            <input type="input" class="form-control mr-2" id="modal-parameter-param-value">
                            <label class="mr-2" id="modal-parameter-param-unit"></label>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-warning" onclick="modal_param_action('EDIT')"><i class="fas fa-fw fa-edit mr-2"></i>แก้ไข</button>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">ยกเลิก</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: Users -->
        <div class="modal fade" tabindex="-1" id="modal-user">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">บัญชีผู้ใช้</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <!-- User: Username -->
                            <div class="form-group">
                                <label>ชื่อผู้ใช้งาน:</label>
                                <input type="text" class="form-control" id="modal-user-input-username">
                            </div>
                            <!-- User: Type -->
                            <div class="form-group">
                                <label>ประเภทของผู้ใช้:</label>
                                <select class="form-control custom-select" id="modal-user-input-usertype"></select>
                            </div>
                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-success" onclick="modal_user_action('add')">เพิ่มบัญชีผู้ใช้</button>
                        <button type="button" class="btn btn-warning" onclick="modal_user_action('edit')">แก้ไขบัญชีผู้ใช้</button>
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">ยกเลิก</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal: System Messages -->
        <div class="modal fade" tabindex="-2" id="modal-message">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">ข้อความจากระบบ</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">ตกลง</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Javascripts -->
        <script src="assets/js/jquery-3.3.1.min.js"></script>
        <script src="assets/js/bootstrap.bundle.min.js"></script>
        <script src="assets/js/moment.min.js"></script>
        <script src="assets/js/jquery.dm-uploader.js"></script>
        <script src="assets/js/scripts.js"></script>
        <script>
            $("#file-upload-container").dmUploader({
                auto: false,
                multiple: false,
                url: "<?php echo $server_path; ?>/services/file_upload.php",
                extFilter: ["csv"],
                extraData: function() {
                    return {
                        "upload_by":   "<?php echo $_SESSION["user_name"]; ?>",
                        "upload_type": $("#file-upload-selection-type").val()
                    }
                },
                onFileExtError: function(file) {
                    alert("ระบบไม่รองรับไฟล์ประเภทนี้ กรุณาตรวจสอบอีกครั้ง");
                },
                onFileSizeError: function(file) {
                    alert("ไฟล์ที่ท่านเลือก มีขนาดใหญ่เกินกว่าที่ระบบรองรับ กรุณาลองใหม่อีกครั้ง");
                },
                onFileTypeError: function(file) {
                    alert()
                },
                onNewFile: function(id, file) {
                    if(file.name != "") {
                        $("#file-upload-selection-name").val(file.name);
                        $("#file-upload-container .btn-success").prop("disabled", false);
                    } else {
                        $("#file-upload-selection-name").val("");
                        $("#file-upload-container .btn-success").prop("disabled", true);
                    }
                },
                onUploadError: function(id, xhr, status, errorThrown) {
                    alert("Error: " + errorThrown);
                },
                onUploadProgress(id, percent) {

                },
                onUploadSuccess(id, data) {
                    system_display_dialogue(data);
                    $("#file-upload-selection-name").val("");
                    $("#file-upload-container .btn-success").prop("disabled", true);
                    $("#file-upload-container").dmUploader("reset");
                    if(data.includes("สำเร็จ")) {
                        refresh_file_list();
                    }
                }
            });

            function file_upload_commence() {
                if(confirm("ต้องการอัพโหลดไฟล์นี้ใช่หรือไม่")) {
                    $("#file-upload-container").dmUploader("start");
                }
            }

            function file_upload_selection() {
                $("#file-upload").click();
            }
        </script>
    </body>
</html>