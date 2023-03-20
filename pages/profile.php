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

$realName = $username = $email = $password = $password_confirm = $office_start = $office_end = $error = "";
function updateName($new_name){
    $user_id = getLoggedInUserId();
    $pdo = openConn();

    $sql = "UPDATE users 
            SET name = :new_name
            WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'new_name' => $new_name,
        'user_id' => $user_id,
    ]);

    $pdo = null;
}
function updateEmail($new_email){
    $user_id = getLoggedInUserId();
    $pdo = openConn();

    $sql = "UPDATE users 
            SET email = :new_email
            WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'new_email' => $new_email,
        'user_id' => $user_id,
    ]);

    $pdo = null;
}
function updatePassword($new_password){
    $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $user_id = getLoggedInUserId();
    $pdo = openConn();

    $sql = "UPDATE users 
            SET password_hash = :new_password_hash
            WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'new_password_hash' => $new_password_hash,
        'user_id' => $user_id,
    ]);

    $pdo = null;
}

function updateOfficeHours($office_begin,$office_end){
    $office_begin = "2000-01-01 " . $office_begin . ":00";
    $office_end = "2000-01-01 " . $office_end . ":00";
    $user_id = getLoggedInUserId();
    $pdo = openConn();

    $sql = "UPDATE users 
            SET office_begin = :office_begin, office_end = :office_end
            WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        'office_begin' => $office_begin,
        'office_end' => $office_end,
        'user_id' => $user_id,
    ]);

    $pdo = null;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = $_POST["name"];
    $email = $_POST["email"];
    $current_pass = $_POST["curr_pass"];
    $new_pass = $_POST["new_pass"];
    $confirm_new_pass = $_POST["confirm_pass"];
    $office_start = $_POST["office_hour_start"];
    $office_end = $_POST["office_hour_end"];
    $passedValidation = true;
    $changePassword = false;
    $case = -1;
    if($office_start >= $office_end){
        $error = $error . "Office hour start cannot be greater than/equal to office hour end";
        $passedValidation = false;
    }
    if($current_pass == $new_pass && (!empty($new_pass) || !empty($confirm_new_pass))){
        $error = $error . "Current password cannot be same as new password";
        $passedValidation = false;
    }
    if($confirm_new_pass != $new_pass && (!empty($new_pass) || !empty($confirm_new_pass))){
        $error = $error . "New password and confirmation do not match";
    }
    if(empty($current_pass) && (!empty($new_pass) || !empty($confirm_new_pass))){
        $error = $error . "New password cannot be set without the current password";
    }
    if(!(empty($current_pass) || empty($new_pass) || empty($confirm_new_pass))) {
        $changePassword = true;
    }

    if($user_info["email"] != $email){
        updateEmail($email);
    }

    if($user_info["name"] != $name){
        updateName($name);
    }
    if($user_info["office_begin"] != $office_start || $user_info["office_end"] != $office_end){
        updateOfficeHours($office_start, $office_end);
    }

    if($changePassword){
        if (authenticateUsername($email, $current_pass) == -1) {
            $error = $error . "Current password is incorrect.";
        } else {
            $uppercase = preg_match('@[A-Z]@', $new_pass);
            $lowercase = preg_match('@[a-z]@', $new_pass);
            $numbers    = preg_match('@[0-9]@', $new_pass);
            $specialChars = preg_match('@[^\w]@', $new_pass);
            if (!$uppercase || !$lowercase || !$numbers || !$specialChars || strlen($new_pass) < 8) {
                $error = $error . "Password must contain at least:<br>1 Uppercase Character, 1 Lowercase Character<br>1 Number, 1 Special Character<br>";
            } else  {
                updatePassword($new_pass);
            }
        }}
}
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

        function checkAll() {

            //regex
            var regexPw = /^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,128}$/;
            var regexEmail = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
            var regexUsername = /^[0-9a-zA-Z]{1,20}$/;


            var em = document.getElementById('email').value;
            var p1 = document.getElementById('password').value;
            var p2 = document.getElementById('confirm').value;
            var username = document.getElementById('username').value;

            if (!regexUsername.test(username)) {
                document.getElementById("error").innerHTML = ("Username must be 1-20 alphanumeric characters.");
                return false;
            }
            if (!regexEmail.test(em)) {
                document.getElementById("error").innerHTML = "This is not a general email format.<br>";
                return false;
            }
            if (!regexPw.test(p1)) {
                document.getElementById("error").innerHTML = "Password should be of length 8 and contain at least\none uppercase [A-Z]\none lowercase [a-z]\none number [0-9]\none special character [~!@#$%^&*()-_+=]<br>";
                return false;
            }
            if (p1 != p2) {
                document.getElementById("error").innerHTML = "Passwords do not match.<br>";
                return false;
            }
        }

	</script>
</head>

<body>
	<div class="wrap">
		<div class="header">
			<button class="mainlogo" onClick="window.location.href = './dashboard.php'" id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
			<div class="nav">
				<button class="profile"><i class="fa-regular fa-user" onclick="window.location.href = './profile.php'"></i></button>
				<ul>
					<li><a href="./logout.php">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Log Out</a></li>
				</ul>
			</div>
		</div>

		<div class="main">
			<div class="left_container" style="padding-top:10px">
                <form action="<?php echo htmlspecialchars($_SERVER['REQUEST_URI']); ?>" method="post" onsubmit="return checkAll()">

				<div class="input_container">
					<div class="text">Name:</div>
					<input id="name" type="text" name="name" max="30" placeholder="Name" value="<?php echo htmlspecialchars($user_info["name"]) ?>" required>
				</div>

				<div class="input_container">
					<div class="text">Email Address:</div>
					<input id="email" type="text" name="email" max="30" placeholder="Email Address" value="<?php echo htmlspecialchars($user_info["email"]) ?>" required>
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
					<input id="curr_pass" type="text" name="curr_pass" max="30" placeholder="Current Password">
				</div>

				<div class="input_container">
					<div class="text">New Password:</div>
					<input id="new_pass" type="text" name="new_pass" max="30" placeholder="New Password">
				</div>

				<div class="input_container">
					<div class="text">Confirm New Password:</div>
					<input id="confirm_pass" type="text" name="confirm_pass" max="30" placeholder="Confirm Password">
				</div>

				<div class="input_container">
					<div class="text">Timetable URL:</div>
					<input id="timetable_url" type="text" name="timetable_url" max="30" placeholder="URL" readonly = "readonly" onclick = "if(confirm('Do you want to change your timetable?')) {window.location.href='registration.php'}" value="<?php echo htmlspecialchars($user_info["timetable_url"]) ?>" required>
				</div>


				<!-- <button class="buttondesign" onclick="window.location.href = '#something';">Create Group</button> -->
                    <div class="final">
                        <input class="continue" id="post" type="submit" value="Submit">
                    </div>
                    <div id="error" class="error">
                        <?php
                        //if (isset($passedValidation)) {
                            //if (!$passedValidation) {
                                echo ("<p>" . $error . "</p>");
                            //}
                        //}

                        ?>

                    </div>
                </form>
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