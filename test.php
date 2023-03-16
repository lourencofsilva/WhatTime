<?php
date_default_timezone_set('GMT'); // YOUR timezone, of the server

$date = new DateTime("2023-03-19 08:00:00"); // USER's timezone
$date->setTimezone(new DateTimeZone('Europe/London'));
echo $date->format('Y-m-d H:i:s');