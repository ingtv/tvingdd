<?php
// simple_proxy.php (YENİ VERSİYON)

$url = $_GET['url'] ?? '';
if (!$url) {
    http_response_code(400);
    echo "URL eksik!";
    exit;
}

// cURL ile fetch
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true); // Yönlendirmeleri takip et
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/58.0.3029.110 Safari/537.3');

$ekranayazdirveriyi = curl_exec($ch);
$final_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL); // Son erişilen dinamik adresi yakala
$contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
curl_close($ch);

if ($ekranayazdirveriyi === false) {
    http_response_code(500);
    echo "Curl hatası: " . curl_error($ch);
    exit;
}

// Dinamik Host Adresini Oluşturma
$final_url_parts = parse_url($final_url);
$ingtvplus_next_url = "https://be1.ingtvplus.workers.dev/";

if (isset($final_url_parts['scheme']) && isset($final_url_parts['host'])) {
    $ingtvplus_next_url = $final_url_parts['scheme'] . '://' . $final_url_parts['host'];
    if (isset($final_url_parts['port'])) {
        $ingtvplus_next_url .= ":" . $final_url_parts['port'];
    }
}

// Yalnızca /hls/ ile başlayan göreceli yolları düzeltme
if (!empty($ingtvplus_next_url)) {
    // '/hls/' i bul ve başına dinamik hostu ekle.
    // Örn: '/hls/...' -> 'http://dinamikip:port/hls/...'
    $ekranayazdirveriyi = str_replace('/hls/', $ingtvplus_next_url . '/hls/', $ekranayazdirveriyi);
}

// Eğer .ts uzantısına ekleme yapıyorsanız bu satırı tutun, aksi halde kaldırın
// $ekranayazdirveriyi = str_replace('.ts', '.ts?ingsports.tv', $ekranayazdirveriyi);

// İçerik tipi uygun şekilde gönder
header('Content-Type: ' . ($contentType ?: 'application/vnd.apple.mpegurl'));
echo $ekranayazdirveriyi;
?>