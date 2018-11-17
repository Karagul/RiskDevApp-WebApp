<!doctype html>
<?php 
require_once "config.php";
session_start();
if(isset($_SESSION["user_name"]) && isset($_SESSION["user_type_desc"])) header("Location: index.php");
?>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Login | RiskDevApp Console</title>
        <link rel="stylesheet" href="assets/css/bootstrap.min.css">
        <link rel="stylesheet" href="assets/css/all.min.css">
        <link rel="stylesheet" href="assets/css/styles.css">
        <style>
            html {background: url(assets/img/background.jpg) no-repeat center center fixed;}
            -webkit-background-size: cover;
            -moz-background-size: cover;
            -o-background-size: cover;
            background-size: cover;
        </style>
    </head>
    <body>
        <div class="container-fluid h-100">
            <div class="row h-100">
                <div class="col-md-4 offset-md-4 col-sm-6 offset-sm-3 col-xs-12 h-100">
                    <div class="card mt-5">
                        <div class="card-header"><b>ลงชื่อเข้าใช้งาน</b></div>
                        <div class="card-body p-3">
                            <div class="alert alert-danger d-none" id="login-alert-message"></div>
                            <form>
                                <!-- Login: Username -->
                                <div class="form-group">
                                    <label>ชื่อบัญชีผู้ใช้</label>
                                    <input type="text" class="form-control" id="login-input-username">
                                </div>
                                <!-- Login: Password -->
                                <div class="form-group">
                                    <label>รหัสผ่าน</label>
                                    <input type="password" class="form-control" id="login-input-password">
                                </div>
                                <!-- Login: Buttons -->
                                <button type="button" class="btn btn-block btn-success" id="login-button-submit" onclick="user_login()">เข้าสู่ระบบ</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script src="assets/js/jquery-3.3.1.min.js"></script>
        <script>
            var current_web_location = "<?php echo $server_path; ?>";

            function user_login() {
                // Validating user input
                if($("#login-input-username").val() == "") {
                    $("#login-alert-message").html("<b>กรุณากรอกชื่อผู้ใช้งาน</b>");
                    $("#login-alert-message").removeClass("d-none");
                    return;
                }

                if($("#login-input-password").val() == "") {
                    $("#login-alert-message").html("<b>กรุณากรอกรหัสผ่าน</b>");
                    $("#login-alert-message").removeClass("d-none");
                    return;
                }

                // Sending a check request
                $.post(current_web_location + "/services/user_login.php", {
                    username: $("#login-input-username").val(),
                    password: $("#login-input-password").val()
                }, function(data, status) {
                    if(data.includes("สำเร็จ")) {
                        $("#login-alert-message").removeClass("alert-danger").addClass("alert-success");
                        $("#login-alert-message").html("<b>" + data + "</b>");
                        window.location.replace("index.php");
                    } else {
                        $("#login-alert-message").html("<b>" + data + "</b>");
                        $("#login-alert-message").removeClass("d-none");
                    }
                });
            }

            // Binding the enter keypress event
            $(document).keypress(function(e) {
                if(e.which == 13) $("#login-button-submit").click();
            })
        </script>
    </body>
</html>