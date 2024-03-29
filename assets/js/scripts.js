// Variables
var current_web_location = "http://164.115.23.67/riskdevapp-webapp";
var current_year = moment().format("YYYY");
// General Function: Display System dialog
function system_display_dialog(message) {
    $("#modal-message .modal-body").html(message);
    $("#modal-message").modal("toggle");
}

// General Binding: On Document Ready
$(document).ready(function() {
    refresh_execution_type();
    //refresh_execution_list();
    refresh_file_type();
    refresh_file_list();
    refresh_param_list();
    refresh_user_list();
    refresh_user_type();
});

// Execution: Types
function refresh_execution_type() {
    $.get(current_web_location + "/services/execute_get_type.php", {
        return_type: "JSON"
    }, function(data, status) {
        try {
            var response = JSON.parse(data);
            var html     = "";
            // Adjusting the executiong type selection
            $.each(response, function(index, value) {
                html += '<option value="' + value.type_name + '">' + value.type_desc + '</option>';
            });
            $("#actions-calculation-input-type").html(html);
            $("#actions-calculation-display-type").html('<option value="ALL">ทุกประเภท</option>' + html);
			$("#actions-calculation-display-type").val("ALL");
			refresh_execution_list();
        } catch(e) {
            system_display_dialog(data);
        }
    })
}

function refresh_execution_list() {
    $.get(current_web_location + "/services/execute_get_list.php", {
        type: $("#actions-calculation-display-type").val(),
        return_type: "JSON"
    }, function(data, status) {
        try {
            var response = JSON.parse(data);
            var html = "";
            // Adjusting the execution list
            $.each(response, function(index, value) {
                html += '<tr>';
                html += '<td>' + value.type + '</td>';
                html += '<td>' + value.date + '</td>';
                html += '<td>' + value.year + '</td>';
                html += '<td>' + value.status + '</td>';
                html += '</tr>';
            });
            $("#actions-calculation tbody").html(html);
        } catch(e) {
            system_display_dialog(data);
        }
    });
}

// File: Types 
function refresh_file_type() {
    $.get(current_web_location + "/services/file_get_type.php", {
        return_type: "JSON"
    }, function(data, status) {
        try {
            var response = JSON.parse(data);
            var html     = "";
            // Adjusting the file type selection
            $.each(response, function(index, value) {
                html += '<option value="' + value.type_name + '">' + value.type_desc + '</option>';
            });
            $("#files-list-selection-filter").html('<option value="ALL">ทุกประเภท</option>' + html);
            $("#file-upload-selection-type").html(html);
        } catch(e) {
            system_display_dialog(data);
        }
    });
}

function refresh_file_list() {
    var file_type = $("#files-list-selection-filter").val();
    if(file_type == "" || file_type == null) file_type = "ALL";
    
    $.post(current_web_location + "/services/file_get_list.php", {
        file_type: file_type,
        return_type: "JSON"
    }, function(data, status) {
        try {
            var response = JSON.parse(data);
            var html = "";
            $.each(response, function(index, value) {
                html += '<tr>';
                html += '<td><b>' + value.file_name + '</b></td>';
                html += '<td>' + value.file_type + '</td>';
                html += '<td>' + value.upload_date + '</td>';
                html += '<td>' + value.upload_by + '</td>';
                html += '<td><button type="button" class="btn btn-sm btn-warning" onclick="file_cut(\'' + value.file_name + '\', \'' + value.file_type + '\')">ตัดไฟล์นี้</button> ';
                html += '<button type="button" class="btn btn-sm btn-danger" onclick="file_delete(\'' + value.file_name + '\', \'' + value.file_type + '\')">ลบไฟล์นี้</button></td>'
                html += '</tr>';
            });
            $("#files-list tbody").html(html);
        } catch(e) {
            system_display_dialog(data);
        }
    });
}

//beg+++iKS01.01.2019 Adding file truncation through R scripts provided
function file_cut(filename, filetype) {
    $.post(current_web_location + "/services/file_preprocess.php", {
        file_name: filename,
        file_type: filetype
    }, function(data, status) {
        system_display_dialog(data);
    });
}
//end+++iKS01.01.2019 Adding file truncation through R scripts provided

//beg+++iKS03.02.2019 Adding file delete function
function file_delete(filename, filetype) {
    if(confirm("ยืนยันการลบไฟล์ " + filename)) {
        $.post(current_web_location + "/services/file_delete.php", {
            file_name: filename,
            file_type: filetype
        }, function(data, status) {
            system_display_dialog(data);
            refresh_file_list();
        });
    }
}
//end+++iKS03.02.2019 Adding file delete function

$("#files-list-selection-filter").change(function() {refresh_file_list();});

// Parameter: List
function refresh_param_list() {
    $.get(current_web_location + "/services/param_get_list.php", {
        return_type: "JSON"
    }, function(data, status) {
        try {
            // Parsing JSON data
            var response = JSON.parse(data);
            var html     = "";
			
            // Adjusting the listing panel
            $.each(response, function(index, value) {
				//beg+++iKS05.03.2019 Adding colors
				var current_background = "";
				if(value.param_name.startsWith("ASF")) {
					current_background = "#FFFFDD";
				} else if(value.param_name.startsWith("FMD")) {
					current_background = "#FFFFEE";
				} else if(value.param_name.startsWith("HPAI")) {
					current_background = "#CCFFFF";
				} else if(value.param_name.startsWith("NIPAH")) {
					current_background = "#CCFFEE";
				}
				//end+++iKS05.03.2019 Adding colors
            
                //beg+++iKS14.03.2019 Fixing Greek alphabet
                value.param_desc = value.param_desc.replace("[BETA]", "&beta;");
                value.param_desc = value.param_desc.replace("[GAMMA]", "&gamma;");
                value.param_desc = value.param_desc.replace("[SIGMA]", "&sigma;");
                //end+++iKS14.03.2019 Fixing Greek alphabet

                html += '<div class="col-md-4 col-sm-6">';
                html += '   <div class="card text-center" style="background-color: ' + current_background + '">';
                html += '       <div class="card-body p-3">';
                html += '           <h5>' + value.param_desc + '</h5>';
                html += '           <h1 class="display-4 mt-3">' + value.param_value + '</h1>';
                html += '           <h5 class="mb-3">' + value.param_unit + '</h5>';
                html += '           <a href="#modal-param" onclick="modal_param_toggle(\'' + value.param_name + '\')"><i class="fas fa-fw fa-edit mr-2"></i>แก้ไขค่า</a>';
                html += '       </div>';
                html += '   </div>';
                html += '</div>';
            });
            $("#settings-tiles-container").html(html);
        } catch(e) {
            // Adjusting the listing panel
            $("#settings-tiles-container").html('<div class="col-12"><div class="jumbotron text-center h-100 mb-0"><h1><i class="fas fa-3x fa-fw fa-exclamation-triangle mb-4"></i><br />ไม่พบการตั้งค่าในระบบ<br /></h1></div></div>');
        }
    });
}

// User: List
function refresh_user_list() {
    $.get(current_web_location + "/services/user_get_list.php", {
        return_type: "JSON"
    }, function(data, status) {
        try {
            // Parsing JSON data
            var response = JSON.parse(data);
            var html     = "";
            // Adjusting the table display
            $.each(response, function(index, value) {
                html += '<tr>';
                html += '   <td class="bg-secondary text-white">' + value.user_name + '</td>';
                html += '   <td>' + value.type_desc + '</td>';
                html += '   <td>' + value.valid_from + '</td>';
                html += '   <td>' + moment(value.valid_to).format("D MMM YYYY") + '</td>';
                html += '   <td>';
                html += '       <button type="button" class="btn btn-sm btn-warning" onclick="modal_user_toggle(\'edit\', \'' + value.user_name +'\', \'' + value.type_name + '\', \'' + value.valid_to + '\')"><i class="fas fa-fw fa-edit mr-2"></i>แก้ไข</button>';
                html += '       <button type="button" class="btn btn-sm btn-danger" onclick="modal_user_action(\'delete\', \'' + value.user_name + '\')"><i class="fas fa-fw fa-trash-alt mr-2"></i>ลบ</button>';
                html += '   </td>';
                html += '</tr>';
            });
            $("#users-list tbody").html(html);
        } catch(e) {
            system_display_dialog(data);
            // Adjusting the table display
        }
    });
}

// User: Logout
function user_logout() {
    if(confirm("ยืนยันการออกจากระบบ")) {
        window.location.href = current_web_location + "/services/user_logout.php";
    }
}

// User: Types 
function refresh_user_type() {
    $.get(current_web_location + "/services/user_get_type.php", {
        return_type: "JSON"
    }, function(data, status) {
        try {
            // Parsing JSON data
            var response = JSON.parse(data);
            var html     = "";
            // Adjusting the modal display
            html = "";
            $.each(response, function(index, value) {
                html += '<option value="' + value.type_name + '">' + value.type_desc + '</option>';
            });
            $("#modal-user-input-usertype").html(html);
        } catch(e) {
            system_display_dialog(data);
            // Adjusting the table display
            $("#users-type tbody").html('<tr><td coslpan="3">&ndash;&nbsp;ไม่พบประเภทผู้ใช้ในระบบ&nbsp;&ndash;</td></tr>');
            // Adjusting the modal display
        }
    });
}

// Modal Toggling Handler: Parameter
function modal_param_toggle(param_name) {
    // Getting parameter information
    $.post(current_web_location + "/services/param_get_info.php", {
        param_name:  param_name,
        return_type: "JSON"
    }, function(data, status) {
        try {
            // Parsing JSON data
            var response = JSON.parse(data);
            // Adjusting Modal Elements
            $("#modal-parameter-param-name").val(response[0].parameter_name);
            $("#modal-parameter-param-value").val(response[0].parameter_value);
            $("#modal-parameter-param-unit").html(response[0].parameter_unit);
            $("#modal-parameter-param-desc").html(response[0].parameter_description);
            // Toggling the modal
            $("#modal-parameter").modal("toggle");
        } catch(e) {
            system_display_dialog(data);
        }
    });
}

// Modal Action Handler: Parameter
function modal_param_action(action) {
    switch(action) {
        case "EDIT":
            if(confirm("ยืนยันการแก้ไขค่าตัวแปร")) {
                $.post(current_web_location + "/services/param_edit_value.php", {
                    param_name:  $("#modal-parameter-param-name").val(),
                    param_value: $("#modal-parameter-param-value").val(),
                    return_type: "TEXT"
                }, function(data, status) {
                    system_display_dialog(data);
                    if(data.includes("สำเร็จ")) {
                        $("#modal-parameter").modal("toggle");
                        refresh_param_list();
                    }
                });
            }
            break;
    }
}

// Modal Toggling Handler: User
function modal_user_toggle(action, username, usertype, validto) {
    validto = moment(validto).format("D MMM YYYY");

    switch(action) {
        case "add": 
            $("#modal-user-input-username").prop("disabled", false);
            $("#modal-user .modal-title").html("เพิ่มบัญชีผู้ใช้");
            $("#modal-user .btn-success").show();
            $("#modal-user .btn-warning").hide();
            $("#modal-user-input-validuntil").val(validto);
            $("#modal-user").modal("toggle");
            break;
        case "edit":
            // Getting User Info
            $("#modal-user-input-username").prop("disabled", true);
            $("#modal-user .modal-title").html("แก้ไขบัญชีผู้ใช้");
            $("#modal-user .btn-success").hide();
            $("#modal-user .btn-warning").show();
            $("#modal-user-input-username").val(username);
            $("#modal-user-input-usertype").val(usertype);
            $("#modal-user-input-validuntil").val(validto);
            $("#modal-user").modal("toggle");
            break;
    }
}

// Modal Action Handler: User
function modal_user_action(action, username) {
    switch(action) {
        case "add": 
            if(confirm("ยืนยันการเพิ่มบัญชีผู้ใช้งาน")) {
                $.post(current_web_location + "/services/user_add.php", {
                    user_name: $("#modal-user-input-username").val(),
                    user_type: $("#modal-user-input-usertype").val(),
                    valid_to: $("#modal-user-input-validuntil").val(),
                    return_type: "TEXT"
                }, function(data, status) {
                    system_display_dialog(data);
                    if(data.includes("สำเร็จ")) {
                        refresh_user_list();
                        $("#modal-user").modal("toggle");
                    }
                });
            }
            break;
        case "edit": 
            if(confirm("ยืนยันการแก้ไขบัญชีผู้ใช้งาน")) {
                $.post(current_web_location + "/services/user_edit.php", {
                    user_name: $("#modal-user-input-username").val(),
                    user_type: $("#modal-user-input-usertype").val(),
                    valid_to: $("#modal-user-input-validuntil").val(),
                    return_type: "TEXT"
                }, function(data, status) {
                    system_display_dialog(data);
                    if(data.includes("สำเร็จ")) {
                        refresh_user_list();
                        $("#modal-user").modal("toggle");
                    }
                });
            }
            break;
        case "delete": 
            if(confirm("ยืนยันลบผู้ใช้ " + username)) {
                $.post(current_web_location + "/services/user_delete.php", {
                    user_name: username,
                    return_type: "TEXT"
                }, function(data, status) {
                    system_display_dialog(data);
                    if(data.includes("สำเร็จ")) {
                        refresh_user_list();
                    }
                });
            }
            break;
    }
}

$("#actions-calculation-button-execute").click(function() {
    $.post(current_web_location + "/services/execute_python_script.php", {
        type: $("#actions-calculation-input-type").val(),
        // beg+++eKS21.05.2019 Fixing year selection, per request
        // year: $("#actions-calculation-input-year").val(),
        year: current_year,
        // end+++eKS21.05.2019 Fixing year selection, per request
        return_type: "TEXT"
    }, function(data, status) {
        alert(data);
        refresh_execution_list();
    });
});

$("#actions-calculation-display-type").change(function() {
	refresh_execution_list();
});

// beg+++iKS31.05.2019 Adding Password Change Modal
function toggle_password_change() {
    $("#modal-user").modal("hide");
    $("#modal-password").modal("show");
    $("#modal-password-username").val($("#modal-user-input-username").val());
}

function submit_password_change() {
    if($("#modal-password-new").val().length == 0 || $("#modal-password-confirm").val().length == 0) {
        alert("กรุณากรอกรหัสผ่านใหม่ พร้อมยืนยันรหัสผ่าน");
    } else if($("#modal-password-new").val() != $("#modal-password-confirm").val()) {
        alert("รหัสผ่านไม่ตรงกัน กรุณาลองอีกครั้ง");
    } else {
        $.post(current_web_location + "/services/user_password_change.php", {
            username: $("#modal-password-username").val(),
            password: $("#modal-password-confirm").val(),
            return_type: "TEXT"
        }, function(data, status) {
            alert(data);
            $("#modal-password").modal("hide");
        });
    }
}
// end+++iKS31.05.2019 Adding Password Change Modal