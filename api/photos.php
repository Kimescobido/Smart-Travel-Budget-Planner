<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Fetches a high-quality landscape photo for a destination city using Unsplash API.
 * Includes a reliable fallback if no API key is provided yet.
 */
function getDestinationPhoto($city)
{
    $accessKey = getenv('UNSPLASH_ACCESS_KEY');

    // If an Unsplash API Access Key is provided in .env, call the official API:
    if (!empty($accessKey)) {
        $url = "https://api.unsplash.com/search/photos?query=" . urlencode($city . " landmark travel") . "&orientation=landscape&per_page=1&client_id=" . urlencode($accessKey);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 200 && $response) {
            $data = json_decode($response, true);
            if (!empty($data['results'][0]['urls']['regular'])) {
                return [
                    'url' => $data['results'][0]['urls']['regular'],
                    'photographer' => $data['results'][0]['user']['name'] ?? 'Unsplash',
                    'photographer_url' => $data['results'][0]['user']['links']['html'] ?? 'https://unsplash.com'
                ];
            }
        }
    }

    // Smart Fallback (direct travel image search via Unsplash CDN)
    return [
        'url' => "https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=80",
        'photographer' => 'Unsplash Travel',
        'photographer_url' => 'https://unsplash.com'
    ];
}