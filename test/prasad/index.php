<?php
require_once 'db_connection.php';
require_once 'ListGenerator.php';

$listGenerator = new ListGenerator($conn);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!empty($_POST["new_member"])) {
        $newMember = $_POST["new_member"];
        $parentId = isset($_POST["parentid"]) ? $_POST["parentid"] : null;
        if ($listGenerator->addMember($newMember, $parentId)) {
            $members = $listGenerator->generateList();
            echo json_encode(array("status" => "success", "message" => "New member added successfully.", "data" => $members));
            exit;
        } else {
            echo json_encode(array("status" => "error", "message" => "Error occurred while adding the member."));
            exit;
        }
    }
}
?>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Member List</title>
    <style>
    </style>
</head>
<body>
    <ul>
        <h4>Member List</h4>
        <div id="memberlist">
            
        </div>
    </ul>

    <button id="addMemberBtn">Add Member</button>

    <div id="addMemberModal" style="display: none;">
        <form id="addMemberForm">
            <div class="modal-header">
                <span class="close">&times;</span>
                <h3>Add New Member</h3>
            </div>
            <div class="modal-body">
                <label for="parentid"><b>Parent</b></label>
                <select name="parentid" id="parentid">
                    <option value="">Select Parent</option>
                </select>
                <label for="new_member"><b>Name</b></label>
                <input type="text" id="new_member" name="new_member">
                <span id="nameError" class="error" style="color:red;"></span>
                <div class="btn-align">
                    <input type="submit" value="Add Member" class="btn-add">
                    <span class="close" onclick="closeModal()">Close</span>
                </div>
            </div>
        </form>
    </div>

    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            
            function fetchMemberList() {
                $.ajax({
                    type: "GET",
                    url: "ajaxcall.php",
                    data: { action: 'generate_list' }, 
                    dataType: "json",
                    success: function(response) {
                        var html = generateListItems(response.data, parentId = 0);
                        $("#memberlist").html(html); 
                    },
                    error: function(xhr, status, error) {
                        console.error("Error fetching member list:", error);
                    }
                });
            }
            
            function generateListItems(members, parentId = 0) {
                var html = "<ul>";
                members.forEach(member => {
                    if (member.parentid == parentId) {
                        html += "<li>" + member.name;
                        html += generateListItems(members, member.id);
                        html += "</li>";
                    }
                });
                html += "</ul>";
                return html;
            }

            function dropdlist() {
                $.ajax({
                    url: 'ajaxcall.php',
                    type: 'GET',
                    data: { action: 'drop_down' }, 
                    dataType: 'json',
                    success: function(response) {
                        if (response.status === 'success') {
                            var members = response.data;
                            var dropdown = $('#parentid');
                            dropdown.empty(); 
                            dropdown.append($('<option>').text('Select Parent').attr('value', ''));
                            $.each(members, function(index, member) {
                                dropdown.append($('<option>').text(member.name).attr('value', member.id));
                            });
                        } else {
                            console.error('Failed to fetch member list: ' + response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('AJAX error:', error);
                    }
                });
            }
            fetchMemberList();
            dropdlist();
            $("#addMemberBtn").click(function() {
                $("#addMemberModal").show();
            });
            

            $("#addMemberForm").submit(function(event) {
                event.preventDefault(); 
                var name = $("#new_member").val();
                if (name.trim() === "") {
                    $("#nameError").text("Name cannot be empty");
                    return false; 
                } else if (!/^[a-zA-Z ]+$/.test(name)) {
                    $("#nameError").text("Name must contain only letters");
                    return false;
                } else {
                    $("#nameError").text(""); 
                    var formData = $(this).serialize();
                    var parentId = $("#parentid").val();
                    if (parentId === "") {
                        formData += "&parentid=null";
                   }
                }
                $.ajax({
                    type: "POST",
                    url: "<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>",
                    data: formData,
                    dataType: "json",
                    success: function(response) {
                        if (response.status === "success") {
                            fetchMemberList(); 
                            dropdlist();
                            closeModal(); 
                            $("#new_member").val("");
                            $("#addMemberModal").hide();
                        } else {
                            alert(response.message);
                            $("#addMemberModal").hide();
                        }
                    },
                    error: function(xhr, status, error) {
                        alert("An error occurred while adding the member: " + error);
                    }
                });
            });

            $(document).mouseup(function(e) {
                var container = $("#addMemberModal");
                if (!container.is(e.target) && container.has(e.target).length === 0) {
                    container.hide();
                }
            });
        });

        var span = document.getElementsByClassName("close")[0];
        span.onclick = function() {
            addMemberModal.style.display = "none";
        }
        
        function closeModal() {
            $("#addMemberModal").hide();
        }
    </script>
</body>
</html>
