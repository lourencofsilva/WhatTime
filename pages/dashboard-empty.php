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

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            resize();
        });
        // Function to handle creating group
        function createGroup() {
            let group_name = document.getElementById("group-name").value;
            var regexGroupName = /^[\w]([\w\s]{0,30})$/;
            if (!regexGroupName.test(group_name)) {
                document.getElementById("createGroupResponse").innerHTML = "An error ocurred in creating the group. The name of your group is not valid."
                return false;
            }

            $.LoadingOverlay("show");
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
            ajaxRequest.onreadystatechange = function() {
                if (this.readyState == 4 && this.status == 200) {
                    document.getElementById("createGroupResponse").innerHTML = this.responseText;
                    $.LoadingOverlay("hide");
                } else if (this.readyState == 4 && this.status == 201) {
                    let id = $('.group_row').length;
                    const url = new URL(window.location.href);
                    const searchParams = new URLSearchParams(url.search);
                    searchParams.set('group', id);
                    url.search = searchParams.toString();
                    window.location.replace(url.toString());
                }
            };


            ajaxRequest.open("GET", "api.php?endpoint=dashboard-create-group&name=" + encodeURIComponent(group_name), true);
            ajaxRequest.send(null);

        }

        window.addEventListener("resize", resize);

        function resize() {
            var width = window.innerWidth;

            if (width <= 1024) {
                $(".left_container").css("margin-left", "0");
                $(".right_container").css("width", "0%");
                $(".left_container").css("width", "100%");
                $(".createbtn").css("width", "20vw");
            } else {
                $(".left_container").css("margin-left", "0");
                $(".right_container").css("width", "72%");
                $(".left_container").css("width", "28%");
                $(".right_container").css("margin-right", "0");
            }
        }

        <?php
        if (getOnboardingStatus(getLoggedInUserId(), "dashboard-empty")) {
            echo(
            '$(document).ready(function () {
                            $(".popup").fadeIn(1000);
                
                        $(".popup-close").click(function () {
                            $(".popup").fadeOut(1000);
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
                            ajaxRequest.open("GET", "api.php?endpoint=onboarding-disable&type=dashboard-empty", true);
                            ajaxRequest.send(null);
                        });
                        });'
            );
        }
        ?>
    </script>
</head>

<body>
<div class="popup">
    <div class="popup-content">
        <h2>Get Started - Dashboard</h2>
        <p><br>To get started, either create a group and send the invite link to the members, or join an existing group by visiting an invite link provided to you.</p>
        <button class="popup-close">Got It</button>
    </div>
</div>
<div class="wrap">
    <div class="header">
        <button class="mainlogo" onClick="window.location.reload()" id="btn" type="button"><img class="main-img" src="../images/logo_white.png"></button>
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
                <button class="crossbtn" onclick="cross()"><i class="fa-solid fa-xmark"></i></button>
            </div>
            <div class="scroll_container">
                <h2>You are not currently part of any groups</h2>
            </div>
            <div class="btn_container">
                <button id="createGroupBtn" class="createbtn">Create Group</button>
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

                        </div>
                    </div>

                    <div class="createGroupResponseContainer">
                        <p id="createGroupResponse"></p>
                    </div>
                    <div class="modal-footer">
                        <button id="savechangesbutton" onclick="createGroup()" class="buttondesign" style="width: 45%;">Save Changes</button>
                    </div>

                </div>

            </div>

            <!-- Manage Group Modal -->
            <div id="manageGroupModal" class="modal">

                <!-- Modal content -->
                <div class="modal-content">
                    <span class="close closeManage">&times;</span>
                </div>

            </div>

            <div id="overlay"></div>

        </div>
        <div class="right_container">
            <button id="manageGroupBtn" style="display: none"></button>
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