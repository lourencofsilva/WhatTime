<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

//Disable cache as this causes issues
header("Cache-Control: no-cache, no-store, must-revalidate"); // HTTP 1.1.
header("Pragma: no-cache"); // HTTP 1.0.
header("Expires: 0"); // Proxies.

session_start();
redirectIfNotLoggedIn("./login.php");
if (isLoggedIn() && !checkTimetableExists(getLoggedInUserId())) {
	redirectIfLoggedIn("./registration.php");
}
if (!updateTimetable(getLoggedInUserId())) {
	errorRedirect("Error updating your timetable. Please try again later.");
}

if (isset($_GET["group"])) {
	$group = htmlspecialchars($_GET["group"]);
} else {
	$group = 0;
}

$groups = getUserGroupInfo(getLoggedInUserId());

if ($group >= count($groups)) {
	$group = 0;
}
if (!empty($groups)) {
    $group_id = $groups[$group]["id"];
    $group_name = $groups[$group]["name"];
    $invite_link = dirname($_SERVER["HTTP_HOST"] . $_SERVER['REQUEST_URI']) . "/invite.php?id=" . $groups[$group]["groupUID"];
    $group_users = getGroupUsers($group_id);
    $office_hours = getBestOfficeHours($group_users);

    foreach ($group_users as $user) {
        if ($user != getLoggedInUserId()) {
            updateTimetable($user);
        }
    }
}

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard · WhatTime?</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
    <meta http-equiv="Pragma" content="no-cache" />
    <meta http-equiv="Expires" content="0" />

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" type="text/css" href="../css/index.css">
	<link rel="stylesheet" type="text/css" href="../css/dashboard.css">
	<link rel="stylesheet" type="text/css" href="../css/modal.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<script defer type="text/javascript" src="../js/createGroupModal.js"></script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/gasparesganga-jquery-loading-overlay@2.1.7/dist/loadingoverlay.min.js"></script>

	<!--- FAVICONS --->
	<link rel="apple-touch-icon" sizes="180x180" href="../apple-touch-icon.png">
	<link rel="icon" type="image/png" sizes="32x32" href="../favicon-32x32.png">
	<link rel="icon" type="image/png" sizes="16x16" href="../favicon-16x16.png">
	<link rel="manifest" href="../site.webmanifest">
	<link rel="mask-icon" href="../safari-pinned-tab.svg" color="#5bbad5">
	<meta name="msapplication-TileColor" content="#da532c">
	<meta name="theme-color" content="#ffffff">

	<script src='https://cdn.jsdelivr.net/npm/moment@2.27.0/min/moment.min.js'></script>
	<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js'></script>
	<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/moment@6.1.4/index.global.min.js'></script>
	<script>
        groupCreated = false;
		document.addEventListener('DOMContentLoaded', function() {
            let tmz = new Date().getTimezoneOffset() / 60;
			var calendarEl = document.getElementById('calendar');
			var calendar = new FullCalendar.Calendar(calendarEl, {
				initialView: 'timeGridWeek',
				dayHeaderFormat: 'dddd DD/MM',
				weekends: false,
				firstDay: 1,
				slotMinTime: parseInt(<?php echo $office_hours[0] ?>) - tmz + ":00",
				slotMaxTime: parseInt(<?php echo $office_hours[1] ?>) - tmz + ":00",
				eventTimeFormat: {
					hour: '2-digit',
					minute: '2-digit',
					hour12: false
				},
				expandRows: true,
				eventColor: 'rgba(49, 95, 211, 1)',
				eventTextColor: 'white',
				height: '100%',
				events: <?php echo json_encode(whatTime($group_id)); ?>

			});
			calendar.render();
            let current_group = document.getElementById("selected");
            current_group.scrollIntoView();
		});

        // Function to handle creating group
        function createGroup() {
            let $group_name = document.getElementById("group-name").value;

            //FRONTEND: Add validation for the group name (no special characters, max 30 characters)

            var ajaxRequest;
            try {
                ajaxRequest = new XMLHttpRequest();
            }catch (e) {
                // Internet Explorer Browsers
                try {
                    ajaxRequest = new ActiveXObject("Msxm l2.XMLHTTP");
                }catch (e) {
                    try{
                        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    }catch (e){
                        alert("An error occured!");
                        return false;
                    }
                }
            }
            ajaxRequest.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("createGroupResponse").innerHTML = this.responseText;
                    groupCreated = true;
                }
            };
            ajaxRequest.open("GET", "api.php?endpoint=dashboard-create-group&name=" + encodeURIComponent($group_name), true);
            ajaxRequest.send(null);

        }

        function deleteGroup() {
            if (confirm("Are you sure you want to delete the group: <?php echo htmlspecialchars($group_name) ?>?")) {
                $.LoadingOverlay("show");
                var ajaxRequest;
                try {
                    ajaxRequest = new XMLHttpRequest();
                }catch (e) {
                    // Internet Explorer Browsers
                    try {
                        ajaxRequest = new ActiveXObject("Msxm l2.XMLHTTP");
                    }catch (e) {
                        try{
                            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                        }catch (e){
                            alert("An error occured!");
                            return false;
                        }
                    }
                }
                ajaxRequest.onreadystatechange = function() {
                    if (this.readyState == 4 && this.status == 200) {
                        location.reload()
                    }
                };

                ajaxRequest.open("GET", "api.php?endpoint=dashboard-group-delete&group-id=" + <?php echo htmlspecialchars($group_id) ?>, true);
                ajaxRequest.send(null);
            }
        }

        function deleteMember(member_id, index) {
            if (confirm("Are you sure you want to remove this member from the group: <?php echo htmlspecialchars($group_name) ?>?")) {
                var ajaxRequest;
                try {
                    ajaxRequest = new XMLHttpRequest();
                }catch (e) {
                    // Internet Explorer Browsers
                    try {
                        ajaxRequest = new ActiveXObject("Msxm l2.XMLHTTP");
                    }catch (e) {
                        try{
                            ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                        }catch (e){
                            alert("An error occured!");
                            return false;
                        }
                    }
                }
                ajaxRequest.open("GET", "api.php?endpoint=dashboard-member-delete&group-id=" + <?php echo htmlspecialchars($group_id) ?> + "&member-id=" + encodeURIComponent(member_id), true);
                ajaxRequest.send(null);
                var row = document.getElementsByClassName('member-row')[index];
                row.style.display = "none";
                $.LoadingOverlay("hide");
            }
        }

        function saveChanges() {
            let text = document.getElementById("manage-name").value;

            if (text === "<?php echo htmlspecialchars($group_name) ?>") {
                return;
            }
            $.LoadingOverlay("show");

            // FRONTEND: Add validation for group name here

            var ajaxRequest;
            try {
                ajaxRequest = new XMLHttpRequest();
            }catch (e) {
                // Internet Explorer Browsers
                try {
                    ajaxRequest = new ActiveXObject("Msxm l2.XMLHTTP");
                }catch (e) {
                    try{
                        ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
                    }catch (e){
                        alert("An error occured!");
                        return false;
                    }
                }
            }
            ajaxRequest.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    location.reload()
                }
            };

            ajaxRequest.open("GET", "api.php?endpoint=dashboard-change-name&group-id=" + <?php echo htmlspecialchars($group_id) ?> + "&new-name=" + encodeURIComponent(text), true);
            ajaxRequest.send(null);

        }

        // Function for searching group names
	    function search() {
	        	let text = document.getElementById("search").value.toLowerCase();
	        	$('.group_row').each(function(i, obj) {
	        		var name = document.getElementsByClassName('group_name_container')[i].innerHTML.toLowerCase();
	        		if (name.includes(text)) {
	        			obj.style.display = "block";
	        		} else {
	        			obj.style.display = "none";
	        		}
	        	});
	        }

        function reloadAfterCreation() {
            if (groupCreated) {
                $.LoadingOverlay("show");
                location.reload();
            }
        }

	    // Function for detection devices (sitll being worked by Jawoon)
	    $( document ).ready(function() {      
    		var is_mobile = false;
    		if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) {
    			is_mobile = true;
			}

    		if (is_mobile == true) {
        		$('.hamburger_menu').css('display') = 'block';
        		$('.left_container').css('display') = 'none';
    		} else {
    			$('.hamburger_menu').css('display') = 'none';
    			$('.left_container').css('display') = 'block';
    		}
 		});

		// Function for hamburger menu (still being worked by Jawoon)
	    function hamburger() {

	    }
	</script>
</head>

<body>
	<div class="wrap">
		<div class="header">
			<a class="mainlogo" href="../index.php"><img class="main-img" src="../images/logo_white.png"></a>
			<div class="nav">
				<button class="profile"><i class="fa-regular fa-user" onclick="window.location.href = './profile.php'"></i></button>
				<ul>
					<li><a href="./logout.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Log Out</a></li>
				</ul>
			</div>
		</div>

		<div class="main">
			<div class="left_container">
				<div class="search_container">
					<input type="text" id="search" placeholder="search" onkeyup="search()">
				</div>
				<div class="scroll_container">
					<?php
					if (empty($groups)) {
						echo "<h2>You are not currently part of any groups</h2>";
					} else {
						$count = 0;
						foreach ($groups as $currentGroup) {
							echo "<a href='./dashboard.php?group=" . $count . "'>";
							echo "<div ";
                            if ($count == $group) {
                                echo "id='selected' ";
                            }
                            echo "class='group_row";
                            if ($count == $group) {
                                echo " selected";
                            }
                            echo "'>";
							echo "<div class='group_image_container'>";
							echo "<img class='group_image' src='../images/group.png'>";
							echo "</div>";
							echo "<div class='group_name_container'>" . $currentGroup["name"] . "</div>";
							echo "</div>";
							echo "</a>";
							$count++;
						}
					}
					?>


				</div>
				<div class="btn_container">
					<button id="createGroupBtn" class="button">Create Group</button>
				</div>

				<!-- Create Group Modal -->
				<div id="createGroupModal" class="modal">

					<!-- Modal content -->
					<div class="modal-content">
						<span class="close closeCreate">&times;</span>
						<p style="font-size: 30px;">Create Group</p>
						<div class="form_info">
							<div class="input_container">
								<label>Group Name:</label>
								<input type="text" id="group-name" placeholder="Group Name" maxlength="30">
								<div id="createGroupResponse"></div><!-- FRONTEND: Please style this, backend added it -->
							</div>
						</div>
						<div class="modal-footer">
							<button id="createGroupBtn" onclick="createGroup()" class="buttondesign">Save Changes</button>
						</div>

					</div>

				</div>

				<!-- Manage Group Modal -->
				<div id="manageGroupModal" class="modal">

					<!-- Modal content -->
					<div class="modal-content">
						<span class="close closeManage">&times;</span>
						<p style="font-size: 30px;">Manage Group</p>
						<div class="group_information">
							<div class="input_container" style="padding-top: 5%;">
								<label>Group Name:</label>
								<input type="text" placeholder="Group Name" id="manage-name" value="<?php echo htmlspecialchars($group_name) ?>">
							</div>
							<div class="input_container" style="padding-top: 5%;">
								<label>Invite link:</label>
								<input type="text" readonly="readonly" value="<?php echo htmlspecialchars($invite_link) ?>">
							</div>
							<div class="input_container" style="padding-top: 5%;">
								<label>Members:</label>
								<div class="scroll_container" style="height: 20vh;border: 2px solid black;width:50%;margin-left:23%;width:80%">
                                    <?php
                                        $count = 0;
                                        foreach ($group_users as $user) {
                                            $info = getUserInfo($user);
                                            echo '<div style="height:10vh;width:100%;background-color: red;border: 1px solid black;" class="member-row">';
                                            if (!is_null($info["profile_picture"])) {
                                                echo    '<img alt="Profile Picture" style="width:3vw" src="data:image/png;base64,' . base64_encode($info['profile_picture']) . '"/>';
                                            }
                                            echo    '<p id="big_text">' . $info["name"]. '</p>';
                                            echo    '<p>' . $info["username"]. '</p>';
                                            if ($user != getLoggedInUserId()) {
                                                echo    '<i class="fa-regular fa-x" onclick="deleteMember(';
                                                echo    $info["id"] . ", " . $count;
                                                echo    ')"></i>';
                                            }
                                            echo '</div>';
                                            $count++;
                                        }
                                    ?>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button id="deleteGroupBtn" class="deleteGroupBtnDesign" onclick="deleteGroup()">Delete Group</button> <!-- TODO: This button should be centered -->
							<!-- <button id="savechanges" class="buttondesign">Save Changes</button> This isn't needed anymore, the changes are saved on exit from the modal-->
						</div>

					</div>

				</div>

				<div id="overlay"></div>

			</div>
			<div class="right_container">

				<div class="timetable_header">

					<button class="hamburger_menu" onclick="hamburger()"><i class="fa-solid fa-bars"></i></button>
                    <p id="big_text"><?php echo htmlspecialchars($group_name) ?></p>

					<button id="manageGroupBtn" class="buttondesign" onclick="showModal()">Manage Group</button>

				</div>

				<div id="overlay"></div>
				<div class="timetable">
					<div id="calendar"></div>
				</div>
			</div>
		</div>
	</div>

	<div class="footer">
		<a>©</a>
		<ul>
			<li><a href="#">Contact US</a></li>
			<li><a href="#">Terms & Conditions</a></li>
			<li><a href="#">Privacy Policy</a></li>
		</ul>
	</div>
	</div>

</body>

</html>