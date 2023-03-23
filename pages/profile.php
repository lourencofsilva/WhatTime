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
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = $error .  "Invalid Email Address.";
        } else {
            updateEmail($email);
        }
    }

    if($user_info["name"] != $name){
        if(strlen($name) > 30){
            $error = $error . "Name cannot be greater than 30 characters.";
        } else {
            updateName($name);
        }
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
        var calendar;
		document.addEventListener('DOMContentLoaded', function() {
            defaultView = resize();
			let tmz = new Date().getTimezoneOffset() / 60;
			// Set office hours selected to current value
			document.getElementById("office_hour_start").value = "<?php echo htmlspecialchars($user_info["office_begin"]) ?>";
			document.getElementById("office_hour_end").value = "<?php echo htmlspecialchars($user_info["office_end"]) ?>";

			var calendarEl = document.getElementById('calendar');
            calendar = new FullCalendar.Calendar(calendarEl, {
				height: '100%',
				initialView: defaultView,
				dayHeaderFormat: 'dddd DD/MM',
                headerToolbar: {
                    left: 'timeGridDay,timeGridWeek,dayGridMonth',
                    center: 'title',
                    right: 'today,prev,next'
                },
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
                eventDisplay: "block",
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

        window.addEventListener('load', function () {
            calendar.updateSize(); // This fixes the issue where overflow is visible after calendar load
        })

        function checkAll() {
            var regexPw = /^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,128}$/;
            var regexEmail = /^[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*@[0-9a-zA-Z]([-_.]?[0-9a-zA-Z])*.[a-zA-Z]{2,3}$/i;
			var regexName = /^[a-zA-Z][a-zA-Z\s]{1,29}$/;

			var name = document.getElementById('name').value;
            var email = document.getElementById('email').value;
           
            var currentPassword = document.getElementById('curr_pass').value;
            var newPassword = document.getElementById('new_pass').value;
			var confirmNewPassword = document.getElementById('confirm_pass').value; 

			if (!regexName.test(name)) {
				console.log("name not valid");
				document.getElementById("errormessage").innerHTML = ("Name must be 1-30 alphabetic character");
				return false;
			}
			if (!regexEmail.test(email)){
				console.log("email not valid");
				document.getElementById("errormessage").innerHTML = ("Email invalid");
				return false;
			}
			if (currentPassword != ""){
				console.log("password change");
				if (!regexPw.test(currentPassword)){
					console.log("old password not okay");
					document.getElementById("errormessage").innerHTML = ("Current Password invalid");
					return false;
				}
				if (newPassword != confirmNewPassword) {
					console.log("newpasswords dont match")
					document.getElementById("errormessage").innerHTML = ("Password confirmation invalid");
					return false;
				}
				if (newPassword == "" | confirmNewPassword ==""){
					console.log("no new password");
					document.getElementById("errormessage").innerHTML = ("New password is empty");
					return false;
				}
				
				if (!regexPw.test(newPassword )){
					console.log("invalid new password");
					document.getElementById("errormessage").innerHTML = ("Password should be of length 8 and contain at least\none uppercase [A-Z]\none lowercase [a-z]\none number [0-9]\none special character [~!@#$%^&*()-_+=]");
					return false;
				}
				if (newPassword == currentPassword){
					console.log("old password same as new password");
					document.getElementById("errormessage").innerHTML = ("Please choose another password");
					return false;
				}
			}

			else if (newPassword != "" | confirmNewPassword != "") {
				console.log("can't change password without current");
				return false;
			}

			return true;
        }

		window.addEventListener("resize", resize);

        function resize() {
            var width = window.innerWidth;

            if (width <= 1024) {
                $(".left_container").css("margin-left", "-100%");
                $(".hamburger_menu").css("display", "block");
                $(".right_container").css("width", "100%");
                if (typeof calendar !== 'undefined') {
                    calendar.changeView('timeGridDay');
                }
                return 'timeGridDay';
            }
            $(".left_container").css("margin-left", "0");
            $(".hamburger_menu").css("display", "none");
            $(".right_container").css("width", "72%");
            $(".left_container").css("width", "28%");
            $(".right_container").css("margin-right", "0");
            $(".crossbtn").css("display", "none");
            $(".createbtn").css("width", "10vw");
            if (typeof calendar !== 'undefined') {
                calendar.changeView('timeGridWeek');
            }
            return 'timeGridWeek';
        }

		// Function for hamburger menu and cross button
		function hamburger() {
			$(".hamburger_menu").fadeOut();
			$(".crossbtn").fadeIn();
			$(".left_container").css("width", "100%");
			$(".left_container").animate({
				marginLeft: 0
			}, 1000);
			$(".right_container").animate({
				marginRight: "-100%"
			}, 990);
			$(".createbtn").css("width", "20vw");
		}

		function cross() {
			$(".crossbtn").fadeOut();
			$(".hamburger_menu").fadeIn();
			$(".left_container").animate({
				marginLeft: "-100%"
			}, 990);
			$(".right_container").animate({
				marginRight: 0
			}, 1000);
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
			<div class="left_container">
				<div class="crossbtn_container">
					<button class="crossbtn" onclick="cross()"><i class="fa-solid fa-xmark"></i></button>
				</div>

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
					<input id="curr_pass" type="password" name="curr_pass" max="30" placeholder="Current Password">
				</div>

				<div class="input_container">
					<div class="text">New Password:</div>
					<input id="new_pass" type="password" name="new_pass" max="30" placeholder="New Password">
				</div>

				<div class="input_container">
					<div class="text">Confirm New Password:</div>
					<input id="confirm_pass" type="password" name="confirm_pass" max="30" placeholder="Confirm Password">
				</div>

				<div class="input_container">
					<div class="text">Timetable URL:</div>
					<input id="timetable_url" type="text" name="timetable_url" max="30" placeholder="URL" readonly = "readonly" onclick = "if(confirm('Do you want to change your timetable?')) {window.location.href='registration.php'}" value="<?php echo htmlspecialchars($user_info["timetable_url"]) ?>" required>
				</div>

                    <div class="final">
                        <input class="continue" id="post" type="submit" value="Submit">
                    </div>
                    <div id="error" class="error">
						<p id = "errormessage"><?php echo $error?></p>

                    </div>
                </form>
			</div>
			<div class="right_container">
				<header class="timetable_header">
					<button class="hamburger_menu" onclick="hamburger()"><i class="fa-solid fa-bars"></i></button>
				</header>

				<div class="timetable">
					<div id="calendar"></div>
				</div>

			</div>
		</div>

		<div class="footer">
			<a>©</a>
			<ul>
				<li><a href="#">Contact Us</a></li>
				<li><a href="#">Terms & Conditions</a></li>
				<li><a href="#">Privacy Policy</a></li>
			</ul>
		</div>
	</div>
</body>

</html>