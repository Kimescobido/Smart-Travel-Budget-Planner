<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Fetches a high-quality landscape photo for a destination city using Unsplash API.
 * Includes a reliable fallback if no API key is provided or limit exceeded.
 */
function getDestinationPhoto($city)
{
    $accessKey = getenv('UNSPLASH_ACCESS_KEY') ?: ($_SERVER['UNSPLASH_ACCESS_KEY'] ?? ($_ENV['UNSPLASH_ACCESS_KEY'] ?? ''));
    // Active verified Unsplash key fallback
    if (empty($accessKey) || $accessKey === '2PhpjRlIBi_eJnkZJUM2E8qLE0MYYUxv4c5f5INczPc') {
        $accessKey = '2PhpIRIiBI_uJnkZJUH2EBqLE0HMYUsv4c5fSINczPc';
    }

    $cleanCity = trim($city);
    if (strpos($cleanCity, ',') !== false) {
        $cleanCity = trim(explode(',', $cleanCity)[0]);
    }

    if (!empty($accessKey) && !empty($cleanCity)) {
        $url = "https://api.unsplash.com/search/photos?query=" . urlencode($cleanCity . " landmark travel") . "&orientation=landscape&per_page=1&client_id=" . urlencode($accessKey);

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

    // Curated high-resolution Unsplash city covers
    $normalized = strtolower(trim($city));
    $cityFallbacks = [
        'tokyo'     => 'https://images.unsplash.com/photo-1503899036084-c55cdd92da26?auto=format&fit=crop&w=1200&q=80',
        'japan'     => 'https://images.unsplash.com/photo-1545569341-9eb8b30979d9?auto=format&fit=crop&w=1200&q=80',
        'manila'    => 'https://images.unsplash.com/photo-1518684079-3c830dcef090?auto=format&fit=crop&w=1200&q=80',
        'melbourne' => 'https://images.unsplash.com/photo-1514565131-fce0801e5785?auto=format&fit=crop&w=1200&q=80',
        'paris'     => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?auto=format&fit=crop&w=1200&q=80',
        'new york'  => 'https://images.unsplash.com/photo-1496442226666-8d4d0e62e6e9?auto=format&fit=crop&w=1200&q=80',
        'london'    => 'https://images.unsplash.com/photo-1513635269975-59663e0ac1ad?auto=format&fit=crop&w=1200&q=80',
        'singapore' => 'https://images.unsplash.com/photo-1525625293386-3f8f99389edd?auto=format&fit=crop&w=1200&q=80',
        'cebu'      => 'https://images.unsplash.com/photo-1518509562904-e7ef99cdcc86?auto=format&fit=crop&w=1200&q=80',
        'iligan'    => 'https://images.unsplash.com/photo-1432405972618-c60b0225b8f9?auto=format&fit=crop&w=1200&q=80',
    ];

    foreach ($cityFallbacks as $key => $photoUrl) {
        if (strpos($normalized, $key) !== false) {
            return [
                'url' => $photoUrl,
                'photographer' => ucfirst($key) . ' Travel via Unsplash',
                'photographer_url' => 'https://unsplash.com'
            ];
        }
    }

    return [
        'url' => "https://images.unsplash.com/photo-1488646953014-85cb44e25828?auto=format&fit=crop&w=1200&q=80",
        'photographer' => 'Unsplash Travel',
        'photographer_url' => 'https://unsplash.com'
    ];
}
