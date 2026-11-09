<?php
/**
 * API #5 — Wikipedia REST API
 * Fetches a destination guide (summary, description, thumbnail) from Wikipedia.
 * No API key required. Free to use for educational projects.
 * Endpoint: https://en.wikipedia.org/api/rest_v1/page/summary/{CITY}
 */
function getDestinationGuide($city)
{
    if (empty($city)) return null;

    $query = trim($city);
    // If city contains a comma (e.g. "Japan, Tokyo"), take the first part
    if (strpos($query, ',') !== false) {
        $query = trim(explode(',', $query)[0]);
    }

    $url = "https://en.wikipedia.org/api/rest_v1/page/summary/" . urlencode(ucwords(strtolower($query)));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 6);
    curl_setopt($ch, CURLOPT_USERAGENT, 'SmartTravelPlanner/1.0 (Educational Project; kimescobido30@gmail.com)');
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode === 200 && $response) {
        $data = json_decode($response, true);
        if (!empty($data['extract'])) {
            return [
                'title'       => $data['title']       ?? ucwords($query),
                'description' => $data['description'] ?? 'Destination Overview',
                'extract'     => $data['extract'],
                'thumbnail'   => $data['thumbnail']['source'] ?? null,
                'page_url'    => $data['content_urls']['desktop']['page'] ?? ('https://en.wikipedia.org/wiki/' . urlencode(ucwords($query)))
            ];
        }
    }

    return null;
}
