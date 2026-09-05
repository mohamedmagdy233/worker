<?php

mb_internal_encoding('UTF-8');

/*
|--------------------------------------------------------------------------
| إعدادات الصفحة
|--------------------------------------------------------------------------
*/
$pageId = '434309674008905';
$accessToken = 'EAARdg15lAY8BSSOZCuzBq7xLpKVVRwlb41BMwEcZAXnbD65x6AdDDxoMf2NoXqH0BZConBvxcHA8mb5auwTB5UYZAviyTySJJbk56hEn1sEH1zyAcTLd2Fm1VVIrLVBe2WlNd2aMe2nRyc7afA4dOIy3cwaBWh588n5o8fFK47eVNB03l92paZAFiX1sMTpmuQSLX';

/*
|--------------------------------------------------------------------------
| دوال مساعدة
|--------------------------------------------------------------------------
*/
function httpGetJson($url, $timeout = 20)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_HTTPHEADER     => [
            'Accept: application/json',
        ],
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
            'ok'    => false,
            'error' => 'HTTP ' . $httpCode,
            'body'  => $response,
        ];
    }

    if (!is_array($json)) {
        return [
            'ok'    => false,
            'error' => 'Invalid JSON response',
            'body'  => $response,
        ];
    }

    return ['ok' => true, 'data' => $json];
}

function httpPostForm($url, array $data, $timeout = 25)
{
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => $url,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($data),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_TIMEOUT        => $timeout,
        CURLOPT_SSL_VERIFYPEER => true,
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
            'ok'    => false,
            'error' => 'HTTP ' . $httpCode,
            'body'  => $response,
            'json'  => $json,
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

/*
|--------------------------------------------------------------------------
| محتوى متنوع — مداخل غنية وتأملية
|--------------------------------------------------------------------------
*/
$introTemplates = [
    "﴿ آية اليوم ﴾",
    "📖 وقفة مع آية من كتاب الله",
    "🌿 من نور القرآن الكريم",
    "🤍 تذكير قرآني",
    "✨ آية تُنير الدرب",
    "🕊️ غذاء الروح من كتاب الله",
    "💚 تأمّل في آية",
    "🌙 نور من كلام الله",
    "📜 من جوامع الآيات",
];

/*
|--------------------------------------------------------------------------
| أدعية ختامية — بدون استدراج تفاعل
|--------------------------------------------------------------------------
*/
$closingTemplates = [
    "اللهم اجعل القرآن ربيع قلوبنا ونور صدورنا.",
    "اللهم ارزقنا تلاوته وتدبّره والعمل به.",
    "اللهم ذكّرنا منه ما نُسّينا وعلّمنا منه ما جهلنا.",
    "اللهم اجعله حجة لنا لا علينا.",
    "اللهم انفعنا بالقرآن العظيم واجعله نورًا لنا يوم القيامة.",
    "نسأل الله أن ينفعنا وإياكم بكتابه الكريم.",
    "اللهم اجعلنا من أهل القرآن الذين هم أهلك وخاصتك.",
    "سبحان الله وبحمده.. سبحان الله العظيم.",
];

/*
|--------------------------------------------------------------------------
| جلب آية عشوائية
|--------------------------------------------------------------------------
*/
$quranApiUrl   = "https://api.alquran.cloud/v1/ayah/random/ar.quran-simple";
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
| تكوين المنشور
|--------------------------------------------------------------------------
*/
$intro   = pickOne($introTemplates);
$closing = pickOne($closingTemplates);

$messageParts = [
    $intro,
    "﴿ " . $ayahText . " ﴾",
    "[ " . $surahName . " — الآية " . $ayahNumber . " ]",
    "┈ ┈ ┈ ┈ ┈ ┈ ┈ ┈ ┈",
    $closing,
    "#القرآن_الكريم",
];

$message = implode("\n\n", $messageParts);
$message = cleanArabicText($message);

/*
|--------------------------------------------------------------------------
| النشر على فيسبوك
|--------------------------------------------------------------------------
*/
$fbUrl = "https://graph.facebook.com/v23.0/{$pageId}/feed";

$postData = [
    'message'      => $message,
    'access_token' => $accessToken,
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
    echo "تم النشر بنجاح ✅\n";
    echo "Post ID: " . $result['id'] . "\n\n";
    echo "نص المنشور:\n";
    echo "-----------------------------\n";
    echo $message . "\n";
} else {
    echo "تم إرسال الطلب لكن الاستجابة غير متوقعة:\n";
    print_r($result);
}
