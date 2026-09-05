<?php

mb_internal_encoding('UTF-8');

/*
|--------------------------------------------------------------------------
| إعدادات الصفحة
|--------------------------------------------------------------------------
*/

$pageId = '434309674008905';

$accessToken = 'EAARdg15lAY8BSSOZCuzBq7xLpKVVRwlb41BMwEcZAXnbD65x6AdDDxoMf2NoXqH0BZConBvxcHA8mb5auwTB5UYZAviyTySJJbk56hEn1sEH1zyAcTLd2Fm1VVIrLVBe2WlNd2aMe2nRyc7afA4dOIy3cwaBWh588n5o8fFK47eVNB03l92paZAFiX1sMTpmuQSLX';

$publishedFile = __DIR__ . '/published_hadiths.json';


/*
|--------------------------------------------------------------------------
| HadeethEnc API
|--------------------------------------------------------------------------
*/

$hadeethApiBase = 'https://hadeethenc.com/api/v1';

$language = 'ar';


/*
|--------------------------------------------------------------------------
| دوال HTTP
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
        CURLOPT_SSL_VERIFYHOST => 2,

        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
            'User-Agent: Hadith-Facebook-Publisher/1.0',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {

        $error = curl_error($ch);

        curl_close($ch);

        return [
            'ok' => false,
            'error' => $error,
        ];
    }

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    $json = json_decode(
        $response,
        true
    );

    if (
        $httpCode < 200 ||
        $httpCode >= 300
    ) {

        return [
            'ok' => false,
            'error' => 'HTTP ' . $httpCode,
            'body' => $response,
            'json' => $json,
        ];
    }

    if (!is_array($json)) {

        return [
            'ok' => false,
            'error' => 'Invalid JSON response',
            'body' => $response,
        ];
    }

    return [
        'ok' => true,
        'data' => $json,
    ];
}


function httpPostForm($url, array $data, $timeout = 25)
{
    $ch = curl_init();

    curl_setopt_array($ch, [

        CURLOPT_URL => $url,

        CURLOPT_POST => true,

        CURLOPT_POSTFIELDS =>
            http_build_query($data),

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_CONNECTTIMEOUT => 10,

        CURLOPT_TIMEOUT => $timeout,

        CURLOPT_SSL_VERIFYPEER => true,

        CURLOPT_SSL_VERIFYHOST => 2,

        CURLOPT_HTTPHEADER => [
            'Accept: application/json',
        ],
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {

        $error = curl_error($ch);

        curl_close($ch);

        return [
            'ok' => false,
            'error' => $error,
        ];
    }

    $httpCode = curl_getinfo(
        $ch,
        CURLINFO_HTTP_CODE
    );

    curl_close($ch);

    $json = json_decode(
        $response,
        true
    );

    if (
        $httpCode < 200 ||
        $httpCode >= 300
    ) {

        return [
            'ok' => false,
            'error' => 'HTTP ' . $httpCode,
            'body' => $response,
            'json' => $json,
        ];
    }

    return [
        'ok' => true,
        'data' => $json,
    ];
}


/*
|--------------------------------------------------------------------------
| تنظيف النص
|--------------------------------------------------------------------------
*/

function cleanArabicText($text)
{
    $text = trim($text);

    $text = preg_replace(
        "/[ \t]+/u",
        " ",
        $text
    );

    $text = preg_replace(
        "/\R{3,}/u",
        "\n\n",
        $text
    );

    return trim($text);
}


/*
|--------------------------------------------------------------------------
| اختيار عنصر عشوائي
|--------------------------------------------------------------------------
*/

function pickOne(array $items)
{
    return $items[
        array_rand($items)
    ];
}


/*
|--------------------------------------------------------------------------
| Tags
|--------------------------------------------------------------------------
*/

function pickRandomTags(
    array $tags,
    $count = 4
) {

    $tags = array_values(
        array_unique($tags)
    );

    shuffle($tags);

    return implode(
        ' ',
        array_slice(
            $tags,
            0,
            min(
                $count,
                count($tags)
            )
        )
    );
}


/*
|--------------------------------------------------------------------------
| قراءة الأحاديث المنشورة
|--------------------------------------------------------------------------
*/

function loadPublishedHadiths($file)
{
    if (!file_exists($file)) {
        return [];
    }

    $content = file_get_contents($file);

    if (
        $content === false ||
        trim($content) === ''
    ) {
        return [];
    }

    $data = json_decode(
        $content,
        true
    );

    return is_array($data)
        ? $data
        : [];
}


/*
|--------------------------------------------------------------------------
| حفظ الأحاديث المنشورة
|--------------------------------------------------------------------------
*/

function savePublishedHadiths(
    $file,
    array $hadithIds
) {

    $hadithIds = array_values(
        array_unique(
            array_map(
                'strval',
                $hadithIds
            )
        )
    );

    return file_put_contents(
        $file,
        json_encode(
            $hadithIds,
            JSON_PRETTY_PRINT |
            JSON_UNESCAPED_UNICODE
        ),
        LOCK_EX
    ) !== false;
}


/*
|--------------------------------------------------------------------------
| قوالب المنشورات
|--------------------------------------------------------------------------
*/

$introTemplates = [
    "﴿ حديث اليوم ﴾",
    "📖 من هدي النبي ﷺ",
    "🤍 حديث نبوي شريف",
    "🌿 من سنة رسول الله ﷺ",
    "✨ وقفة مع السنة النبوية",
    "🕊️ نور من هدي المصطفى ﷺ",
    "💚 من كنوز السنة النبوية",
    "📜 حديث يُنير الدرب",
    "🌙 تأمّل في حديث نبوي",
];


$ctaTemplates = [
    "اللهم ارزقنا العمل بسنة نبيك ﷺ.",
    "اللهم اجعلنا ممن يستمعون القول فيتبعون أحسنه.",
    "اللهم صلِّ وسلِّم وبارك على نبينا محمد ﷺ.",
    "اللهم ارزقنا اتباع سنة نبيك ﷺ.",
    "اللهم انفعنا بما علّمتنا وعلّمنا ما ينفعنا.",
    "نسأل الله أن ينفعنا وإياكم بهدي النبي ﷺ.",
    "اللهم اجعل القرآن والسنة نور قلوبنا.",
    "اللهم اجمعنا بنبيك ﷺ في الفردوس الأعلى.",
];


$hashtags = [
    "#حديث_نبوي",
    "#السنة_النبوية",
    "#حديث_اليوم",
];


/*
|--------------------------------------------------------------------------
| تحميل الأحاديث المنشورة
|--------------------------------------------------------------------------
*/

$publishedHadiths =
    loadPublishedHadiths(
        $publishedFile
    );


/*
|--------------------------------------------------------------------------
| الخطوة 1
|
| جلب التصنيفات العربية
|--------------------------------------------------------------------------
*/

$categoriesUrl =
    $hadeethApiBase .
    '/categories/list/' .
    '?language=' .
    urlencode($language);


$categoriesResponse =
    httpGetJson(
        $categoriesUrl
    );


if (!$categoriesResponse['ok']) {

    die(
        "فشل في جلب تصنيفات HadeethEnc:\n" .
        $categoriesResponse['error'] .
        "\n"
    );
}


$categories =
    $categoriesResponse['data'];


if (
    !is_array($categories) ||
    empty($categories)
) {

    die(
        "لم يتم العثور على تصنيفات في HadeethEnc.\n"
    );
}


/*
|--------------------------------------------------------------------------
| الخطوة 2
|
| اختيار تصنيف عشوائي
|--------------------------------------------------------------------------
*/

shuffle($categories);


/*
|--------------------------------------------------------------------------
| الخطوة 3
|
| محاولة الحصول على حديث جديد
|--------------------------------------------------------------------------
*/

$selectedHadith = null;

foreach ($categories as $category) {

    $categoryId =
        $category['id'] ?? null;

    $categoryTitle =
        $category['title'] ?? '';


    if (!$categoryId) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | نجيب الصفحة الأولى
    |--------------------------------------------------------------------------
    */

    $listUrl =
        $hadeethApiBase .
        '/hadeeths/list/' .
        '?language=' .
        urlencode($language) .
        '&category_id=' .
        urlencode($categoryId) .
        '&page=1' .
        '&per_page=20';


    $listResponse =
        httpGetJson(
            $listUrl
        );


    if (!$listResponse['ok']) {
        continue;
    }


    $listData =
        $listResponse['data'];


    /*
    |--------------------------------------------------------------------------
    | الـ API يرجع:
    |
    | data
    | meta
    |--------------------------------------------------------------------------
    */

    $hadithList =
        $listData['data'] ?? [];


    if (
        !is_array($hadithList) ||
        empty($hadithList)
    ) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | عشوائية داخل التصنيف
    |--------------------------------------------------------------------------
    */

    shuffle($hadithList);


    foreach ($hadithList as $item) {

        $hadithId =
            $item['id'] ?? null;


        if (!$hadithId) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | منع التكرار
        |--------------------------------------------------------------------------
        */

        if (
            in_array(
                (string)$hadithId,
                $publishedHadiths,
                true
            )
        ) {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | جلب تفاصيل الحديث
        |--------------------------------------------------------------------------
        */

        $detailsUrl =
            $hadeethApiBase .
            '/hadeeths/one/' .
            '?language=' .
            urlencode($language) .
            '&id=' .
            urlencode($hadithId);


        $detailsResponse =
            httpGetJson(
                $detailsUrl
            );


        if (!$detailsResponse['ok']) {
            continue;
        }


        $details =
            $detailsResponse['data'];


        /*
        |--------------------------------------------------------------------------
        | استخراج البيانات
        |--------------------------------------------------------------------------
        */

        $hadithText =
            cleanArabicText(
                $details['hadeeth'] ?? ''
            );


        $title =
            cleanArabicText(
                $details['title'] ?? ''
            );


        $attribution =
            cleanArabicText(
                $details['attribution'] ?? ''
            );


        $grade =
            cleanArabicText(
                $details['grade'] ?? ''
            );


        $reference =
            cleanArabicText(
                $details['reference'] ?? ''
            );


        /*
        |--------------------------------------------------------------------------
        | التأكد من وجود النص
        |--------------------------------------------------------------------------
        */

        if ($hadithText === '') {
            continue;
        }


        /*
        |--------------------------------------------------------------------------
        | اختيار الحديث
        |--------------------------------------------------------------------------
        */

        $selectedHadith = [

            'id' =>
                (string)$hadithId,

            'title' =>
                $title,

            'hadeeth' =>
                $hadithText,

            'attribution' =>
                $attribution,

            'grade' =>
                $grade,

            'reference' =>
                $reference,

            'category' =>
                cleanArabicText(
                    $categoryTitle
                ),
        ];


        break 2;
    }
}


/*
|--------------------------------------------------------------------------
| التأكد من العثور على حديث
|--------------------------------------------------------------------------
*/

if (!$selectedHadith) {

    die(
        "لم يتم العثور على حديث جديد غير منشور.\n"
    );
}


/*
|--------------------------------------------------------------------------
| بيانات الحديث
|--------------------------------------------------------------------------
*/

$hadithId =
    $selectedHadith['id'];

$hadithText =
    $selectedHadith['hadeeth'];

$attribution =
    $selectedHadith['attribution'];

$grade =
    $selectedHadith['grade'];

$reference =
    $selectedHadith['reference'];


/*
|--------------------------------------------------------------------------
| تجهيز المنشور
|--------------------------------------------------------------------------
*/

$intro =
    pickOne(
        $introTemplates
    );


$cta =
    pickOne(
        $ctaTemplates
    );


$tags = pickRandomTags($hashtags, 1);

$messageParts = [$intro];
$messageParts[] = "قال رسول الله ﷺ:\n«" . $hadithText . "»";

$sourceLines = [];
if ($attribution !== '') {
    $sourceLines[] = "📚 " . $attribution;
}
if ($grade !== '') {
    $sourceLines[] = "🔎 " . $grade;
}
if ($reference !== '' && mb_strlen($reference) <= 100) {
    $sourceLines[] = "📖 " . $reference;
}
if (!empty($sourceLines)) {
    $messageParts[] = implode("\n", $sourceLines);
}

$messageParts[] = "┈ ┈ ┈ ┈ ┈ ┈ ┈ ┈ ┈";
$messageParts[] = $cta;
$messageParts[] = $tags;

$message = implode("\n\n", $messageParts);
$message = cleanArabicText($message);


/*
|--------------------------------------------------------------------------
| عرض الحديث قبل النشر
|--------------------------------------------------------------------------
|
| احذف هذا الجزء بعد التأكد أن كل شيء يعمل.
|--------------------------------------------------------------------------
*/

echo "\n";
echo "========================================\n";
echo "تم العثور على حديث\n";
echo "========================================\n\n";

echo "Hadith ID: " .
    $hadithId .
    "\n\n";

echo "التصنيف: " .
    $selectedHadith['category'] .
    "\n\n";

echo "الحديث:\n";
echo "----------------------------------------\n";
echo $message;
echo "\n\n";


/*
|--------------------------------------------------------------------------
| Facebook API
|--------------------------------------------------------------------------
*/

$fbUrl =
    "https://graph.facebook.com/v23.0/" .
    $pageId .
    "/feed";


$postData = [

    'message' =>
        $message,

    'access_token' =>
        $accessToken,
];


$publishResponse =
    httpPostForm(
        $fbUrl,
        $postData
    );


/*
|--------------------------------------------------------------------------
| خطأ Facebook
|--------------------------------------------------------------------------
*/

if (!$publishResponse['ok']) {

    echo "========================================\n";

    echo "فشل النشر على Facebook\n";

    echo "========================================\n\n";

    echo "Error:\n";

    echo
        $publishResponse['error'] .
        "\n\n";


    if (
        !empty(
            $publishResponse['json']['error']['message']
        )
    ) {

        echo
            $publishResponse['json']['error']['message'] .
            "\n";
    }


    if (
        !empty(
            $publishResponse['body']
        )
    ) {

        echo "\nRaw response:\n";

        echo
            $publishResponse['body'] .
            "\n";
    }


    /*
    |--------------------------------------------------------------------------
    | مهم:
    |
    | لا نسجل الحديث كمُنشَر
    | لأن Facebook فشل.
    |--------------------------------------------------------------------------
    */

    exit(1);
}


/*
|--------------------------------------------------------------------------
| نتيجة النشر
|--------------------------------------------------------------------------
*/

$result =
    $publishResponse['data'] ?? [];


if (
    isset(
        $result['id']
    )
) {

    /*
    |--------------------------------------------------------------------------
    | تسجيل الحديث كمُنشَر
    |--------------------------------------------------------------------------
    */

    $publishedHadiths[] =
        $hadithId;


    savePublishedHadiths(
        $publishedFile,
        $publishedHadiths
    );


    /*
    |--------------------------------------------------------------------------
    | النتيجة
    |--------------------------------------------------------------------------
    */

    echo "\n";
    echo "========================================\n";

    echo "تم النشر بنجاح ✅\n";

    echo "========================================\n\n";

    echo "Post ID: " .
        $result['id'] .
        "\n\n";

    echo "Hadith ID: " .
        $hadithId .
        "\n\n";

} else {

    echo "\n";
    echo "Facebook أرجع استجابة غير متوقعة:\n";

    print_r(
        $result
    );
}
