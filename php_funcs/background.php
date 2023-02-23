<?php

include "database.php";
include "logging.php";

$user_id = $argv[1];
$events = unserialize(base64_decode($argv[2]));
