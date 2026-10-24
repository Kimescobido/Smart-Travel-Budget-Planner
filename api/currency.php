<?php
require_once __DIR__ . '/../config/db.php';

/**
 * Converts an amount from one currency to another using ExchangeRate-API
 */
function convertCurrency($amount, $fromCurrency, $toCurrency)
{
    if (strtoupper($fromCurrency) === strtoupper($toCurrency) || $amount == 0) {
        return floatval($amount);
    }

    $apiKey = getenv('EXCHANGE_RATE_API_KEY');
    if (empty($apiKey)) {
        return floatval($amount); // fallback
    }

    $url = "https://v6.exchangerate-api.com/v6/" . urlencode($apiKey) . "/pair/" . urlencode($fromCurrency) . "/" . urlencode($toCurrency) . "/" . urlencode($amount);

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
        if (isset($data['result']) && $data['result'] === 'success') {
            return floatval($data['conversion_result']);
        }
    }

    return floatval($amount);
}