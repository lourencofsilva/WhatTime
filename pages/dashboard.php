<?php

include "../php_funcs/database.php";
include "../php_funcs/user-session.php";

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

?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Dashboard · WhatTime?</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" type="text/css" href="../css/index.css">
	<link rel="stylesheet" type="text/css" href="../css/dashboard.css">
	<link rel="stylesheet" type="text/css" href="../css/modal.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<script defer type="text/javascript" src="../js/createGroupModal.js"></script>
	<script defer type="text/javascript" src="../js/manageGroupModal.js"></script>

	<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

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
		document.addEventListener('DOMContentLoaded', function() {
			var calendarEl = document.getElementById('calendar');
			var calendar = new FullCalendar.Calendar(calendarEl, {
				initialView: 'timeGridWeek',
                dayHeaderFormat: 'dddd DD/MM',
				weekends: false,
				firstDay: 1,
				slotMinTime: "<?php echo $office_hours[0] ?>:00",
				slotMaxTime: "<?php echo $office_hours[1] ?>:00",
				eventTimeFormat: {
					hour: '2-digit',
					minute: '2-digit',
					hour12: false
				},
				expandRows: true,
				eventColor: 'rgba(49, 95, 211, 1)',
				eventTextColor: 'white',
				events: <?php echo json_encode(whatTime($group_id)); ?>

			});
			calendar.render();
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
                }
            };
            ajaxRequest.open("GET", "api.php?endpoint=dashboard-create-group&name=" + encodeURIComponent($group_name), true);
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
						foreach ($groups as $group) {
							echo "<a href='./dashboard.php?group=" . $count . "'>";
							echo "<div class='group_row'>";
							echo "<div class='group_image_container'>";
							echo "<img class='group_image' src='../images/group.png'>";
							echo "</div>";
							echo "<div class='group_name_container'>" . $group["name"] . "</div>";
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
								<input type="text" id="group-name" placeholder="Group Name">
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
								<input type="text" placeholder="Group Name" value="<?php echo htmlspecialchars($group_name) ?>">
							</div>
							<div class="input_container" style="padding-top: 5%;">
								<label>Invite link:</label>
								<input type="text" readonly="readonly" value="<?php echo htmlspecialchars($invite_link) ?>">
							</div>
							<div class="input_container" style="padding-top: 5%;">
								<label>Members:</label>
								<div class="scroll_container" style="height: 20vh;border: 2px solid black;width:50%;margin-left:23%;width:80%">
									<div style="height:10vh;width:100%;background-color: red;border: 1px solid black;"></div>
									<div style="height:10vh;width:100%;background-color: red;border: 1px solid black;"></div>
									<div style="height:10vh;width:100%;background-color: red;border: 1px solid black;"></div>
								</div>
							</div>
						</div>
						<div class="modal-footer">
							<button id="deleteGroupBtn" class="deleteGroupBtnDesign">Delete Group</button>
							<button id="savechanges" class="buttondesign">Save Changes</button>
						</div>

					</div>

				</div>

				<div id="overlay"></div>

			</div>
			<div class="right_container">

				<div class="timetable_header">

					<button class="hamburger_menu" onclick="hamburger()"><i class="fa-solid fa-bars"></i></button>

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