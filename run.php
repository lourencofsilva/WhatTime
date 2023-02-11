<?php
include "database.php";

$start_time = microtime(true); //Code to check exec time

//RUN TEST CODE BELOW



//createUser("Aran", file_get_contents("https://assets.manchester.ac.uk/corporate/images/design/logo-university-of-manchester.png" ), "a2trizzy", "aran@2trizzy.com", "test");
//showDB();
//echo(checkIfEmailExists("aran@2trizzy.com"));
//echo(checkIfUsernameExists("a2trizzy"));
//echo(checkIfUsernameExists("fakeUsername"));
$events = getTimetable("https://scientia-eu-v4-api-d3-02.azurewebsites.net//api/ical/b5098763-4476-40a6-8d60-5a08e9c52964/54df08df-70ec-869d-162a-1230db79bf15/timetable.ics");
saveTimetable(10, $events);



//RUN TEST CODE ABOVE

// Time taken for script output (Leave at end of file)
$end_time = microtime(true);
$execution_time = ($end_time - $start_time);
echo " Execution time of script = ".$execution_time." sec";