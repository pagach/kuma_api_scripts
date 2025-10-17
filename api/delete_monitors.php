<?php

include __DIR__."/token.php";
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

function deleteMonitor($id, $url)
{
    echo "DELETING $url \n";

    $apiUrl = "http://127.0.0.1:8000/monitors/$id";

    $token = file_get_contents(__DIR__."/_token.txt");

    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL error: '.curl_error($ch);
    }

    curl_close($ch);

    return true;
}