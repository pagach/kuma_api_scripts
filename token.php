<?php

// URL za autentifikaciju
$login_url = "http://127.0.0.1:8000/login/access-token/";

// Korisničko ime i lozinka
$username = "admin";
$password = "admin";

// Priprema podataka za POST zahtjev
$data = http_build_query([
    'username' => $username,
    'password' => $password,
]);

// Inicijalizacija cURL-a
$ch = curl_init();

// Postavljanje cURL opcija za zahtjev
curl_setopt($ch, CURLOPT_URL, $login_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded',
]);

// Dodavanje opcije za praćenje preusmjeravanja
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Ovo omogućava praćenje preusmjerenja

// Izvršavanje zahtjeva i dobivanje odgovora
$response = curl_exec($ch);

// Provjera za greške u cURL zahtjevu
if (curl_errno($ch)) {
    echo 'Curl error: ' . curl_error($ch);
}

// Zatvaranje cURL sesije
curl_close($ch);

// Parsiranje JSON odgovora i dohvat tokena
$response_data = json_decode($response, true);
if (isset($response_data['access_token'])) {
    $token = $response_data['access_token'];
    file_put_contents("token.txt", $token);
} else {
    echo "No access token found in the response.\n";
}

?>
