<?php

mb_internal_encoding('UTF-8');

/*
|--------------------------------------------------------------------------
| إعدادات الصفحة
|--------------------------------------------------------------------------
*/
$pageId = '434309674008905';
$accessToken = 'EAARdg15lAY8BQ4POkyhj3beYt5YBDHWp2RaFW5MNr7oFSgiZBrSPDAtXi07vA6EVPafJLQ3YlS1LID5CW8OVSifUsR6X6XWnOcEV1aaceZBZA7OezQbxzISGP49zfiFuZBGkTLWqbwMbHLZBh7bje0E82XFgpZBJEZAYFeTPZAVdlZBYbP9BzhhYw8S95vyvQhzZCOprLr';

/*
|--------------------------------------------------------------------------
| دوال مساعدة
|--------------------------------------------------------------------------
*/
function httpGetJson($url, $timeout = 20)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER => [
            'Accept: application/json'
        ]
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => $error];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        return [
            'ok' => false,
            'error' => 'HTTP ' . $httpCode,
            'body' => $response
        ];
    }

    if (!is_array($json)) {
        return [
            'ok' => false,
            'error' => 'Invalid JSON response',
            'body' => $response
        ];
    }

    return ['ok' => true, 'data' => $json];
}

function httpPostForm($url, array $data, $timeout = 25)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT => $timeout,
        CURLOPT_SSL_VERIFYPEER => true
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        $error = curl_error($ch);
        curl_close($ch);
        return ['ok' => false, 'error' => $error];
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $json = json_decode($response, true);

    if ($httpCode < 200 || $httpCode >= 300) {
        return [
            'ok' => false,
            'error' => 'HTTP ' . $httpCode,
            'body' => $response,
            'json' => $json
        ];
    }

    return ['ok' => true, 'data' => $json];
}

function cleanArabicText($text)
{
    $text = trim($text);
    $text = preg_replace("/[ \t]+/u", " ", $text);
    $text = preg_replace("/\R{3,}/u", "\n\n", $text);
    return $text;
}

function pickOne(array $items)
{
    return $items[array_rand($items)];
}

function pickRandomTags(array $tags, $count = 3)
{
    $tags = array_values(array_unique($tags));
    shuffle($tags);
    return implode(' ', array_slice($tags, 0, min($count, count($tags))));
}

/*
|--------------------------------------------------------------------------
| محتوى متنوع للبروفايل البصري للنص
|--------------------------------------------------------------------------
*/
$introTemplates = [
    "﴿ آية اليوم ﴾",
    "🌿 من نور القرآن",
    "🤍 تذكير قرآني",
    "📖 وقفة مع آية",
    "✨ آية عطرة من كتاب الله",
    "🕊️ غذاء للروح",
    "💚 آية تهدّي القلب"
];

$ctaTemplates = [
    "اللهم اجعلها في ميزان حسناتنا.",
    "انشرها لعلها تكون سبب خير.",
    "شاركها تؤجر بإذن الله.",
    "ذكر بها غيرك لعل الله ينفع بها.",
    "اللهم ارزقنا التدبر والعمل."
];

$hashtags = [
    "#قرآن",
    "#آية_اليوم",
    "#تدبر",
    "#ذكر",
    "#اسلام",
    "#quran",
    "#islam",
    "#muslim",
    "#القرآن_الكريم"
];

/*
|--------------------------------------------------------------------------
| جلب آية عشوائية
|--------------------------------------------------------------------------
*/
$quranApiUrl = "https://api.alquran.cloud/v1/ayah/random/ar.quran-simple";
$quranResponse = httpGetJson($quranApiUrl);

if (!$quranResponse['ok']) {
    die("فشل في جلب الآية: " . $quranResponse['error']);
}

$quranData = $quranResponse['data'];

if (!isset($quranData['code']) || (int)$quranData['code'] !== 200 || !isset($quranData['data'])) {
    die("فشل في قراءة بيانات الآية.");
}

$ayahText   = $quranData['data']['text'] ?? '';
$surahName  = $quranData['data']['surah']['name'] ?? '';
$ayahNumber = $quranData['data']['numberInSurah'] ?? '';

$ayahText  = cleanArabicText($ayahText);
$surahName = cleanArabicText($surahName);

if ($ayahText === '' || $surahName === '' || $ayahNumber === '') {
    die("بيانات الآية غير مكتملة.");
}

/*
|--------------------------------------------------------------------------
| تكوين أفضل رسالة نصية للنشر
|--------------------------------------------------------------------------
*/
$intro = pickOne($introTemplates);
$cta   = pickOne($ctaTemplates);
$tags  = pickRandomTags($hashtags, 3);

$messageParts = [
    $intro,
    $ayahText,
    "[" . $surahName . " - الآية " . $ayahNumber . "]",
    $cta,
    $tags
];

$message = implode("\n\n", $messageParts);
$message = cleanArabicText($message);

/*
|--------------------------------------------------------------------------
| النشر على فيسبوك
|--------------------------------------------------------------------------
| استخدمت v23.0 كإصدار حديث من Graph API.
|--------------------------------------------------------------------------
*/
$fbUrl = "https://graph.facebook.com/v23.0/{$pageId}/feed";

$postData = [
    'message' => $message,
    'access_token' => $accessToken
];

$publishResponse = httpPostForm($fbUrl, $postData);

if (!$publishResponse['ok']) {
    echo "فشل النشر.\n\n";
    echo "الرسالة التي حاولت نشرها:\n";
    echo "-----------------------------\n";
    echo $message . "\n\n";
    echo "تفاصيل الخطأ:\n";
    echo $publishResponse['error'] . "\n";

    if (!empty($publishResponse['json']['error']['message'])) {
        echo $publishResponse['json']['error']['message'] . "\n";
    } elseif (!empty($publishResponse['body'])) {
        echo $publishResponse['body'] . "\n";
    }

    exit;
}

$result = $publishResponse['data'];

if (isset($result['id'])) {
    echo "تم النشر بنجاح.\n";
    echo "Post ID: " . $result['id'] . "\n\n";
    echo "نص المنشور:\n";
    echo "-----------------------------\n";
    echo $message;
} else {
    echo "تم إرسال الطلب لكن الاستجابة غير متوقعة:\n";
    print_r($result);
}
?>
