<?php
// Konfiguracja
$apiKey = 'AIzaSyATVa_sDwLeLKWpoy7H0ab9u3CHuS6WfIs';

// Walidacja parametrów
if (!isset($_GET['origin']) || !isset($_GET['destination'])) {
  http_response_code(400);
  echo json_encode(['error' => 'Brak wymaganych parametrów']);
  exit;
}

// Odbierz dane GET
$origin = urlencode($_GET['origin']);
$destination = urlencode($_GET['destination']);

// Zbuduj URL do Google Distance Matrix API
$url = "https://maps.googleapis.com/maps/api/distancematrix/json?origins=$origin&destinations=$destination&key=$apiKey";

// Wyślij żądanie do Google
$response = file_get_contents($url);

// Przekaż odpowiedź do przeglądarki (jako JSON)
header('Content-Type: application/json');
echo $response;
