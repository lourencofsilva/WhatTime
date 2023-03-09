<?php

include "../php_funcs/database.php";
$calendarEvents = [];

foreach (get_busy_time_slots(4) as $event) {
	$calendarEvents[] = array("title" => "UNAVAILABLE", "start" => str_replace(" ", "T", $event["dt_start"]), "end" => str_replace(" ", "T", $event["dt_end"]));
}

?>

<!DOCTYPE html>

<html>

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>dashboard</title>
	<link rel="preconnect" href="https://fonts.googleapis.com">
	<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
	<link href="https://fonts.googleapis.com/css2?family=Overpass:wght@300&display=swap" rel="stylesheet">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" integrity="sha512-MV7K8+y+gLIBoVD59lQIYicR65iaqukzvf/nwasF0nqhPay5w/9lJmVM2hMDcnK1OnMGCdVK+iQrJ7lzPJQd1w==" crossorigin="anonymous" referrerpolicy="no-referrer" />
	<link rel="stylesheet" type="text/css" href="../css/index.css">
	<link rel="stylesheet" type="text/css" href="../css/dashboard.css">

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
				events: <?php echo json_encode($calendarEvents); ?>

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
				<div class="search_container">
					<input type="text" placeholder="search">
				</div>
				<div class="scroll_container">

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>

					<div class="group_row">
						<div class="group_image_container">
						</div>
						<div class="group_name_container">name</div>
					</div>
				</div>
				<!-- <div class = "buttonbox"> -->
				<button class="buttondesign" onclick="window.location.href = '#something';">Create Group</button>
				<!-- </div> -->
			</div>
			<div class="right_container">

				<div class="timetable_header">
					<p id="big_text">Group Name</p>
					<button class="buttondesign" style="float: right; margin-right: 10%; margin-top: 1%;" onclick="window.location.href = '#something';">Manage Group</button>

				</div>
				<div id="calendar" style="width: 100%;"></div>
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