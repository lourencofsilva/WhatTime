<?php

include "database.php";
include "logging.php";

doLog("ERROR1", "Adding events to db failed.", "RUNNING", "background.php");


$user_id = $argv[1];
$events = unserialize(base64_decode($argv[2]));

$pdo = openConn();

$sql = "DELETE FROM events WHERE user_id=:user_id"; // Delete current events first
$stmt = $pdo->prepare($sql);
$stmt->execute([
    'user_id' => $user_id,
]);

$sql = "INSERT INTO events (user_id, active, summary, dt_start, dt_end)
VALUES (:user_id, :active, :summary, :dt_start, :dt_end)";

$stmt = $pdo->prepare($sql);

$fails = 0; //Check if the inserts were successful
$errors = array();

foreach ($events as $event) {
    $result = $stmt->execute([
        'user_id' => $user_id,
        'active' => true,
        'summary' => $event[0],
        'dt_start' => $event[1],
        'dt_end' => $event[2]
    ]);
    if (!$result) {
        $fails++;
        $errors[] = implode(error_get_last());
    }
}
echo($fails);

if ($fails == 0) { // Set last_updated_date to the current time, or log if failed adding events.
    $sql = "UPDATE users SET timetable_last_updated='" . date('Y-m-d H:i:s') . "' WHERE id = :id";
    echo($sql);
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        'id' => $user_id,
    ]);
    echo($result);
} else {
    doLog("ERROR", "Adding events to db failed.", implode($errors), "background.php", $user_id);
}

closeConn($pdo);