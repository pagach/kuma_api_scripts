<?php

//https://github.com/MedAziz11/Uptime-Kuma-Web-API

function login(): void
{
    $loginUrl = "http://127.0.0.1:8000/login/access-token/";

    $username = "admin";
    $password = "admin";

    $data = http_build_query([
        'username' => $username,
        'password' => $password,
    ]);

    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $loginUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/x-www-form-urlencoded',
    ]);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Curl error: '.curl_error($ch);
    }

    curl_close($ch);

    $response_data = json_decode($response, true);
    if (isset($response_data['access_token'])) {
        $token = $response_data['access_token'];
        file_put_contents(__DIR__."/_token.txt", $token);
    } else {
        echo "No access token found in the response.\n";
    }
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

        $ret[] = [
            "id" => $monitor["id"],
            "url" => $parse['host'],
        ];
    }

    $ret = array_map('getBaseUrlMonitors', $ret);

    return array_filter(array_unique($ret));
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

function getBaseUrlMonitors($monitor)
{
    $url = $monitor["url"];

    $parsedUrl = parse_url($url);

    if (empty($parsedUrl['host']) && !str_starts_with($url, 'http')) {
        $parsedUrl = parse_url("https://$url");
    }

    if (empty($parsedUrl['host'])) {
        return null;
    }

    $monitor["url"] = $parsedUrl['scheme'].'://'.$parsedUrl['host'];

    return $monitor;
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

    return $url;
}