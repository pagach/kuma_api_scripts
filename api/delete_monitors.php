<?php

include __DIR__."/functions.php";
login();

try {
    $existingMonitors = getKumaMonitors();

    foreach ($existingMonitors as $existingMonitor) {
        deleteMonitor($existingMonitor["id"], $existingMonitor["url"]);
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
    die;
}