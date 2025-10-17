<?php

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

try {
    $existingMonitors = getKumaMonitors();

    foreach ($urls as $url) {
        $parsedUrl = parse_url($url);

        if (empty($parsedUrl['host'])) {
            continue;
        }

        if (!in_array($parsedUrl['scheme'].'://'.$parsedUrl['host'], $existingMonitors)) {
            createMonitor($parsedUrl['scheme'].'://'.$parsedUrl['host']);
        }
    }
} catch (Exception $e) {
    var_dump($e->getMessage());
    die;
}

function createMonitor($url)
{
    echo "Creating $url \n";

    $apiUrl = 'http://127.0.0.1:8000/monitors/';

    $token = file_get_contents(__DIR__."/_token.txt");

    $parse = parse_url($url);

    if (empty($parse['host'])) {
        echo "Skiping $url \n";

        return false;
    }

    $data = [
        'type' => 'http',
        'name' => $parse['host'],
        'url' => $url,
        'interval' => 60,
    ];

    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'cURL error: '.curl_error($ch);
    }

    curl_close($ch);

    return true;
}

function getKumaMonitors()
{
    $apiUrl = 'http://127.0.0.1:8000/monitors/';

    $token = file_get_contents(__DIR__."/_token.txt");

    $ch = curl_init($apiUrl);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Authorization: Bearer '.$token,
        'Content-Type: application/json',
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new \Exception('cURL error: '.curl_error($ch));
    }

    curl_close($ch);

    $response = json_decode($response, true);

    if (empty($response["monitors"]) && !empty($response["detail"])) {
        throw new \Exception('API error: '.$response["detail"]);
    }
    if (empty($response["monitors"])) {
        return [];
    }

    $ret = [];

    foreach ($response["monitors"] as $monitor) {
        if (empty($monitor["url"])) {
            continue;
        }

        $parse = parse_url($monitor["url"]);

        if (empty($parse['host'])) {
            continue;
        }
        $ret[] = $parse['host'];
    }

    $ret = array_map('getBaseUrl', $ret);

    return array_filter(array_unique($ret));
}

function getBaseUrl($url)
{
    $parsedUrl = parse_url($url);

    if (empty($parsedUrl['host']) && !str_starts_with($url, 'http')) {
        $parsedUrl = parse_url("https://$url");
    }

    if (empty($parsedUrl['host'])) {
        return null;
    }

    return $parsedUrl['scheme'].'://'.$parsedUrl['host'];
}