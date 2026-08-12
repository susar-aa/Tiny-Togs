<?php
// Simple PHP Proxy to bypass X-Frame-Options
$url = 'https://www.checkfresh.com/aveeno.html?lang=en';

// Initialize cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/115.0.0.0 Safari/537.36');
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$html = curl_exec($ch);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    die("Proxy Error: " . $error);
}

// Inject a base tag so relative CSS/JS/Images load correctly from checkfresh.com
$baseTag = '<base href="https://www.checkfresh.com/">';
$html = str_ireplace('<head>', '<head>' . $baseTag, $html);

// Remove any iframe-busting JS if they exist (Checkfresh usually relies on headers, but just in case)
$html = preg_replace('/<script[^>]*>.*?top\.location.*?<\/script>/is', '', $html);

// Serve the modified HTML
echo $html;
?>
