<?php

function doLog($log_type, $summary = NULL, $log = NULL, $function = NULL, $user_id = NULL, $group_id = NULL, $event_id = NULL) {
    if (empty($log_type)) {
        trigger_error("Logging failed: Log_type is a required parameter.");
        die();
    }

    $pdo = openConn();

    $sql = "INSERT INTO logs (user_id, group_id, event_id, log_type, summary, log, function_name)
 VALUES (:user_id, :group_id, :event_id, :log_type, :summary, :log, :function_name)";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        'user_id' => $user_id,
        'group_id' => $group_id,
        'event_id' => $event_id,
        'log_type' => $log_type,
        'summary' => $summary,
        'log' => $log,
        'function_name' => $function,
    ]);

    $pdo = null;
}