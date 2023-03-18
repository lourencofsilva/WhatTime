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

$office_hours = getUserOfficeHours(getLoggedInUserId());
$user_info = getUserInfo(getLoggedInUserId());
?>

<!DOCTYPE html>
<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Profile · WhatTime?</title>
	<meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate" />
	<meta http-equiv="Pragma" content="no-cache" />
	<meta http-equiv="Expires" content="0" />

	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" type="text/css" href="../css/index.css">
	<link rel="stylesheet" type="text/css" href="../css/dashboard.css">
	<link rel="stylesheet" type="text/css" href="../css/profile.css">

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
			let tmz = new Date().getTimezoneOffset() / 60;
			// Set office hours selected to current value
			document.getElementById("office_hour_start").value = "<?php echo htmlspecialchars($user_info["office_begin"]) ?>";
			document.getElementById("office_hour_end").value = "<?php echo htmlspecialchars($user_info["office_end"]) ?>";

			var calendarEl = document.getElementById('calendar');
			var calendar = new FullCalendar.Calendar(calendarEl, {
				height: '100%',
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
				businessHours: {
					daysOfWeek: [1, 2, 3, 4, 5],
					startTime: parseInt(<?php echo $office_hours[2] ?>) - tmz + ":00",
					endTime: parseInt(<?php echo $office_hours[3] ?>) - tmz + ":00",
				},
				eventTextColor: 'white',
				expandRows: true,
				eventClick: function(info) {
					if (info.el.style.backgroundColor === 'rgb(200, 30, 65)') {
						info.el.style.backgroundColor = 'rgb(49, 95, 211)'; // Change the background color
						info.el.style.borderColor = 'rgb(49, 95, 211)'; // Change the border color
					} else {
						info.el.style.backgroundColor = 'rgb(200, 30, 65)'; // Change the background color
						info.el.style.borderColor = 'rgb(200, 30, 65)'; // Change the border color
					}

					var ajaxRequest;
					try {
						ajaxRequest = new XMLHttpRequest();
					} catch (e) {
						// Internet Explorer Browsers
						try {
							ajaxRequest = new ActiveXObject("Msxm l2.XMLHTTP");
						} catch (e) {
							try {
								ajaxRequest = new ActiveXObject("Microsoft.XMLHTTP");
							} catch (e) {
								alert("An error occured!");
								return false;
							}
						}
					}
					ajaxRequest.open("GET", "api.php?endpoint=profile-events&event=" + info.event.id, true);
					ajaxRequest.send(null);
				},
				events: <?php echo json_encode(getUserEvents(getLoggedInUserId())); ?>

			});
			calendar.render();
		});
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

				<div class="input_container">
					<div class="text">Name:</div>
					<input id="name" type="text" name="name" max="30" placeholder="Name" value="<?php echo htmlspecialchars($user_info["name"]) ?>" required>
				</div>

				<div class="input_container">
					<div class="text">Email Address:</div>
					<input id="email" type="text" name="name" max="30" placeholder="Email Address" value="<?php echo htmlspecialchars($user_info["email"]) ?>" required>
				</div>

				<div class="input_container">
					<div class="text">Office Hours:</div>

					<select name="office_hour_start" id="office_hour_start" style="margin-right: 20%;">
						<option value="00:00">00:00</option>
						<option value="01:00">01:00</option>
						<option value="02:00">02:00</option>
						<option value="03:00">03:00</option>
						<option value="04:00">04:00</option>
						<option value="05:00">05:00</option>
						<option value="06:00">06:00</option>
						<option value="07:00">07:00</option>
						<option value="08:00">08:00</option>
						<option value="09:00">09:00</option>
						<option value="10:00">10:00</option>
						<option value="11:00">11:00</option>
						<option value="12:00">12:00</option>
						<option value="13:00">13:00</option>
						<option value="14:00">14:00</option>
						<option value="15:00">15:00</option>
						<option value="16:00">16:00</option>
						<option value="17:00">17:00</option>
						<option value="18:00">18:00</option>
						<option value="19:00">19.00</option>
						<option value="20:00">20:00</option>
						<option value="21:00">21:00</option>
						<option value="22:00">22:00</option>
						<option value="23:00">23:00</option>
					</select>

					<select name="office_hour_end" id="office_hour_end">
						<option value="00:00">00:00</option>
						<option value="01:00">01:00</option>
						<option value="02:00">02:00</option>
						<option value="03:00">03:00</option>
						<option value="04:00">04:00</option>
						<option value="05:00">05:00</option>
						<option value="06:00">06:00</option>
						<option value="07:00">07:00</option>
						<option value="08:00">08:00</option>
						<option value="09:00">09:00</option>
						<option value="10:00">10:00</option>
						<option value="11:00">11:00</option>
						<option value="12:00">12:00</option>
						<option value="13:00">13:00</option>
						<option value="14:00">14:00</option>
						<option value="15:00">15:00</option>
						<option value="16:00">16:00</option>
						<option value="17:00">17:00</option>
						<option value="18:00">18:00</option>
						<option value="19:00">19.00</option>
						<option value="20:00">20:00</option>
						<option value="21:00">21:00</option>
						<option value="22:00">22:00</option>
						<option value="23:00">23:00</option>
					</select>

				</div>

				<div class="input_container">
					<div class="text">Current Password:</div>
					<input id="curr_pass" type="text" name="name" max="30" placeholder="Current Password" required>
				</div>

				<div class="input_container">
					<div class="text">New Password:</div>
					<input id="new_pass" type="text" name="name" max="30" placeholder="New Password" required>
				</div>

				<div class="input_container">
					<div class="text">Confirm New Password:</div>
					<input id="confirm_pass" type="text" name="name" max="30" placeholder="Confirm Password" required>
				</div>

				<div class="input_container">
					<div class="text">Timetable URL:</div>
					<input id="timetable_url" type="text" name="name" max="30" placeholder="URL" value="<?php echo htmlspecialchars($user_info["timetable_url"]) ?>" required>
				</div>


				<!-- <button class="buttondesign" onclick="window.location.href = '#something';">Create Group</button> -->
				<div class="btn_container">
					<button id="createGroupBtn" class="button" onclick="window.location.href = '#something';">Submit</button>
				</div>

			</div>
			<div class="right_container" style="display: flex;">

				<div class="timetable">
					<div id="calendar"></div>
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