<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Fetches current weather for a city using OpenWeatherMap API
 * Returns an array with temp, description, icon, humidity, or null on error.
 */
function getDestinationWeather($city)
{
    $apiKey = getenv('OPENWEATHER_API_KEY');
    if (empty($apiKey) || empty($city)) {
        return null;
    }

    $url = "https://api.openweathermap.org/data/2.5/weather?q=" . urlencode($city) . "&units=metric&appid=" . urlencode($apiKey);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // For local XAMPP environments
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        return [
            'temp' => round($data['main']['temp']),
            'condition' => ucfirst($data['weather'][0]['description'] ?? 'Clear'),
            'icon' => $data['weather'][0]['icon'] ?? '01d',
            'humidity' => $data['main']['humidity'] ?? 0,
            'wind' => $data['wind']['speed'] ?? 0
        ];
    }

    return null;
}