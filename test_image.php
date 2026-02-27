<?php
$baseUrl = 'http://localhost:8000/';
$url = 'http://localhost:8000/public/uploads/logo.png';

$localPath = str_replace($baseUrl, realpath(__DIR__ . '/../../') . '/', $url);
$localPath = str_replace('/', DIRECTORY_SEPARATOR, $localPath);

echo "Base URL: " . $baseUrl . "\n";
echo "URL     : " . $url . "\n";
echo "Path    : " . $localPath . "\n";
echo "Exists  : " . (file_exists($localPath) ? 'YES' : 'NO') . "\n";

$qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=120x120&data=http%3A%2F%2Flocalhost%3A8000%2Fconnect%2Forder%2Feb221c5f850e02aa89bbd8b76174bb0f';

try {
    $context = stream_context_create(['http' => ['timeout' => 5]]);
    $data = @file_get_contents($qrCodeUrl, false, $context);
    echo "QR Len  : " . strlen($data) . "\n";
} catch (\Exception $e) {
    echo "QR Err  : " . $e->getMessage() . "\n";
}

// Check with cURL
if (function_exists('curl_init')) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $qrCodeUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $data = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    if ($data) {
        echo "cURL QR Len: " . strlen($data) . "\n";
    } else {
        echo "cURL QR Err: " . $error . "\n";
    }
}
