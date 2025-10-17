<?php

include __DIR__."/functions.php";
login();

// LOGIN
$url = "https://crm.shipshape-solutions.com/api/login";
$postFields = [
    'username' => 'pinger',
    'password' => 'Pinger901!',
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
    ],
]);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ovo omogućava praćenje preusmjerenja

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: '.curl_error($ch);
}

curl_close($ch);

$response_data = json_decode($response, true);
if (isset($response_data['error']) && !$response_data['error']) {
    $token = $response_data['data']['token'];
} else {
    die("No access token found in the response.");
}


// GET MONITORS
$url = "https://crm.shipshape-solutions.com/api/export_project_urls";
$postFields = [
    'token' => $token,
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => $postFields,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_TIMEOUT => 20,
    CURLOPT_HTTPHEADER => [
        'Accept: application/json',
    ],
]);

curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ovo omogućava praćenje preusmjerenja

$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo 'Curl error: '.curl_error($ch);
}

curl_close($ch);

$response_data = json_decode($response, true);

$urls = $response_data['data'];

$urls = array_map('getBaseUrl', $urls);

$urls = array_filter(array_unique($urls));

try {
    $existingMonitors = getKumaMonitors();

    foreach ($urls as $url) {
        $parsedUrl = parse_url($url);

        if (empty($parsedUrl['host'])) {
            continue;
        }

        $fullUrl = $parsedUrl['scheme'].'://'.$parsedUrl['host'];

        if (!in_array($fullUrl, $existingMonitors)) {
            createMonitor($fullUrl);
        } else {
            $existingMonitors = array_diff($existingMonitors, [$fullUrl]);
        }
    }

    if (!empty($existingMonitors)) {
        // ima viska monitora
        foreach ($existingMonitors as $existingMonitor) {
            deleteMonitor($existingMonitor["id"], $existingMonitor["url"]);
        }
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
    die;
}