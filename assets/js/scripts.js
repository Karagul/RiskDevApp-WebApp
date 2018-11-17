// Variables
var current_web_location = "http://localhost/riskdevapp-webapp";

// General Function: Display System Dialogue
function system_display_dialogue(message) {
    $("#modal-message .modal-body").html(message);
    $("#modal-message").modal("toggle");
}

// General Binding: On Document Ready
$(document).ready(function() {
    refresh_execution_type();
    refresh_execution_list();
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
        } catch(e) {
            system_display_dialogue(data);
        }
    })
}

function refresh_execution_list() {
    $.get(current_web_location + "/services/execute_get_list.php", {
        type: $("#actions-calculation-input-type").val(),
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
            system_display_dialogue(data);
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
            system_display_dialogue(data);
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
                html += '<td>-</td>';
                html += '</tr>';
            });
            $("#files-list tbody").html(html);
        } catch(e) {
            system_display_dialogue(data);
        }
    });
}

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
                html += '<div class="col-md-4 col-sm-6">';
                html += '   <div class="card text-center">';
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
                html += '   <td>' + value.valid_to + '</td>';
                html += '   <td>';
                html += '       <button type="button" class="btn btn-sm btn-warning" onclick="modal_user_toggle(\'edit\', \'' + value.user_name +'\')"><i class="fas fa-fw fa-edit mr-2"></i>แก้ไข</button>';
                html += '       <button type="button" class="btn btn-sm btn-danger" onclick="modal_user_action(\'delete\', \'' + value.user_name + '\')"><i class="fas fa-fw fa-trash-alt mr-2"></i>ลบ</button>';
                html += '   </td>';
                html += '</tr>';
            });
            $("#users-list tbody").html(html);
        } catch(e) {
            system_display_dialogue(data);
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
            system_display_dialogue(data);
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
            system_display_dialogue(data);
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
                    system_display_dialogue(data);
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
function modal_user_toggle(action, username) {
    switch(action) {
        case "add": 
            $("#modal-user-input-username").prop("disabled", false);
            $("#modal-user .modal-title").html("เพิ่มบัญชีผู้ใช้");
            $("#modal-user .btn-success").show();
            $("#modal-user .btn-warning").hide();
            $("#modal-user").modal("toggle");
            break;
        case "edit":
            // Getting User Info
            $("#modal-user-input-username").prop("disabled", true);
            $("#modal-user .modal-title").html("แก้ไขบัญชีผู้ใช้");
            $("#modal-user .btn-success").hide();
            $("#modal-user .btn-warning").show();
            $("#modal-user-input-username").val(username);
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
                    return_type: "TEXT"
                }, function(data, status) {
                    system_display_dialogue(data);
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
                    return_type: "TEXT"
                }, function(data, status) {
                    system_display_dialogue(data);
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
                    system_display_dialogue(data);
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
        year: $("#actions-calculation-input-year").val(),
        return_type: "TEXT"
    }, function(data, status) {
        alert(data);
        refresh_execution_list();
    });
});