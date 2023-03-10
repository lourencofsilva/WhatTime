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

?>

<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Profile</title>
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

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.4/index.global.min.js'></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'timeGridWeek',
                weekends: false,
                firstDay: 1,
                slotMinTime: "09:00:00",
                slotMaxTime: "18:00:00",
                businessHours: {
                    // days of week. an array of zero-based day of week integers (0=Sunday)
                    daysOfWeek: [1, 2, 3, 4, 5], // Monday - Thursday

                    startTime: '10:00', // a start time (10am in this example)
                    endTime: '18:00', // an end time (6pm in this example)
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
				<button class="mainlogo" onClick="window.location.reload()" id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
				<div class="nav">
					<button class="profile"><i class="fa-regular fa-user"></i></button>
					<ul>
						<li><a href="./logout.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Log Out</a></li>
					</ul>
				</div>
			</div>

		<div class="main">
			<div class="left_container">

                <div class="input_container">
                    <div class="text">Name:</div>
                    <input id="name" type="text" name="name" max="30" placeholder="Username" required>
                </div>

				<div class="input_container">
                    <div class="text">Email Address:</div>
                    <input id="email" type="text" name="name" max="30" placeholder="Email Address" required>
                </div>

				<div class="input_container">
                    <div class="text">Office Hours:</div>
	
					<select name="office_hour_start" style="margin-right: 20%;">
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

					  <select name="office_hour_end">
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
                    <input id="timetable_url" type="text" name="name" max="30" placeholder="URL" required>
                </div>


						<button class="buttondesign" onclick="window.location.href = '#something';">Create Group</button>
			</div>
			<div class="right_container">
				
				<div id="calendar"></div>

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