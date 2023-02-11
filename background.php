<?php

include "database.php";

$user_id = $argv[1];
$events = unserialize(base64_decode($argv[2]));

$pdo = openConn();

$sql = "INSERT INTO events (user_id, active, summary, dt_start, dt_end)
VALUES (:user_id, :active, :summary, :dt_start, :dt_end)";

$stmt = $pdo->prepare($sql);

foreach ($events as $event) {
    $stmt->execute([
        'user_id' => $user_id,
        'active' => true,
        'summary' => $event[0],
        'dt_start' => $event[1],
        'dt_end' => $event[2]
    ]);
}

echo("Added all events to db");

closeConn($pdo);