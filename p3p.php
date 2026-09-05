<?php

mb_internal_encoding('UTF-8');

/*
|--------------------------------------------------------------------------
| Configuration & Settings
|--------------------------------------------------------------------------
*/
$pageId = '434309674008905';
$accessToken = 'EAARdg15lAY8BSSOZCuzBq7xLpKVVRwlb41BMwEcZAXnbD65x6AdDDxoMf2NoXqH0BZConBvxcHA8mb5auwTB5UYZAviyTySJJbk56hEn1sEH1zyAcTLd2Fm1VVIrLVBe2WlNd2aMe2nRyc7afA4dOIy3cwaBWh588n5o8fFK47eVNB03l92paZAFiX1sMTpmuQSLX';

$deleteImageAfterSuccessfulPost = true;
$outputImageSize = '1080x1080';
$maxTextWidth = 850;
$maxTextHeight = 650;
$safeMargin = 80;

$fonts = [
    'primary' => 'Noto Naskh Arabic Bold', // Fallback will be handled by Pango if not found
    'secondary' => 'Noto Sans Arabic'
];

/*
|--------------------------------------------------------------------------
| Quotes Array (Refactored Structure)
|--------------------------------------------------------------------------
*/
$quotes = [
    [
        'text' => 'وَقَالَ رَبُّكُمُ ادْعُونِي أَسْتَجِبْ لَكُمْ',
        'source' => 'سورة غافر — 60',
        'type' => 'quran'
    ],
    [
        'text' => 'فَإِنَّ مَعَ الْعُسْرِ يُسْرًا ۝ إِنَّ مَعَ الْعُسْرِ يُسْرًا',
        'source' => 'سورة الشرح — 5-6',
        'type' => 'quran'
    ],
    [
        'text' => 'وَلَسَوْفَ يُعْطِيكَ رَبُّكَ فَتَرْضَى',
        'source' => 'سورة الضحى — 5',
        'type' => 'quran'
    ],
    [
        'text' => 'لَا تَحْزَنْ إِنَّ اللَّهَ مَعَنَا',
        'source' => 'سورة التوبة — 40',
        'type' => 'quran'
    ],
    [
        'text' => 'رَبَّنَا آتِنَا فِي الدُّنْيَا حَسَنَةً وَفِي الآخِرَةِ حَسَنَةً وَقِنَا عَذَابَ النَّارِ',
        'source' => 'سورة البقرة — 201',
        'type' => 'dua'
    ],
    [
        'text' => 'اسْتَغْفِرُوا رَبَّكُمْ إِنَّهُ كَانَ غَفَّارًا',
        'source' => 'سورة نوح — 10',
        'type' => 'quran'
    ],
    [
        'text' => 'رَبِّ إِنِّي لِمَا أَنزَلْتَ إِلَيَّ مِنْ خَيْرٍ فَقِيرٌ',
        'source' => 'سورة القصص — 24',
        'type' => 'dua'
    ],
    [
        'text' => 'اللَّهُمَّ صَلِّ وَسَلِّمْ عَلَى نَبِيِّنَا مُحَمَّدٍ',
        'source' => '',
        'type' => 'dua'
    ],
    [
        'text' => 'مَنْ صَلَّى عَلَيَّ صَلَاةً صَلَّى اللَّهُ عَلَيْهِ بِهَا عَشْرًا',
        'source' => 'رواه مسلم',
        'type' => 'hadith'
    ],
    [
        'text' => 'سُبْحَانَ اللَّهِ وَبِحَمْدِهِ، سُبْحَانَ اللَّهِ الْعَظِيمِ',
        'source' => 'متفق عليه',
        'type' => 'dhikr'
    ],
    [
        'text' => 'اللَّهُمَّ إِنِّي أَسْأَلُكَ الْعَفْوَ وَالْعَافِيَةَ فِي الدُّنْيَا وَالْآخِرَةِ',
        'source' => 'رواه أبو داود',
        'type' => 'dua'
    ],
    [
        'text' => 'حَسْبُنَا اللَّهُ وَنِعْمَ الْوَكِيلُ',
        'source' => 'سورة آل عمران — 173',
        'type' => 'quran'
    ]
];

/*
|--------------------------------------------------------------------------
| Helper Functions
|--------------------------------------------------------------------------
*/

/**
 * Executes a shell command securely and checks the exit code.
 */
function executeCommand($command)
{
    exec($command . ' 2>&1', $output, $return_var);
    if ($return_var !== 0) {
        die("Command Failed: $command\nError: " . implode("\n", $output) . "\n");
    }
    return $output;
}

/**
 * Checks if 'magick' or 'convert' is available.
 */
function getImCommand()
{
    exec('which magick 2>/dev/null', $output, $return_var);
    if ($return_var === 0)
        return 'magick';
    exec('which convert 2>/dev/null', $output, $return_var);
    if ($return_var === 0)
        return 'convert';
    die("ImageMagick is not installed.\n");
}

/**
 * Escapes text for safe use within Pango markup.
 */
function pangoEscape($text)
{
    return htmlspecialchars($text, ENT_QUOTES | ENT_XML1, 'UTF-8');
}

/**
 * Prepares the background: resize, crop, slightly dim brightness & saturation.
 */
function prepareBackground($inputBg, $outputBg, $imCmd)
{
    // Resize & Crop exactly to 1080x1080, modulate to reduce brightness/saturation for cinematic look
    $cmd = sprintf(
        "%s %s -resize 1080x1080^ -gravity center -extent 1080x1080 -modulate 90,85 %s",
        escapeshellcmd($imCmd),
        escapeshellarg($inputBg),
        escapeshellarg($outputBg)
    );
    executeCommand($cmd);
}

/**
 * Calculates initial font size based on text length.
 */
function calculateInitialFontSize($textLength)
{
    if ($textLength <= 35)
        return 82;
    if ($textLength <= 65)
        return 70;
    if ($textLength <= 100)
        return 58;
    return 50;
}

/**
 * Creates the main text image, dynamically adjusting font size to fit Safe Area.
 */
function createTextImage($quote, $outputPath, $imCmd, $fonts, $maxWidth, $maxHeight)
{
    $text = pangoEscape($quote['text']);
    $font = $fonts['primary'];
    $color = '#FFFDF8';
    $fontSize = calculateInitialFontSize(mb_strlen($quote['text']));

    // Fallback protection loop to ensure text fits inside Safe Area without getting clipped
    while ($fontSize >= 30) {
        $markup = "<span font='{$font} {$fontSize}' foreground='{$color}'>{$text}</span>";

        $cmd = sprintf(
            "%s -background transparent -gravity center -size %dx pango:%s %s",
            escapeshellcmd($imCmd),
            $maxWidth,
            escapeshellarg($markup),
            escapeshellarg($outputPath)
        );
        executeCommand($cmd);

        // Get height
        $identifyBin = ($imCmd === 'magick') ? 'magick identify' : 'identify';
        $identifyCmd = sprintf("%s -format '%%h' %s", $identifyBin, escapeshellarg($outputPath));
        exec($identifyCmd, $heightOut, $ret);
        $imgHeight = (isset($heightOut[0]) && $ret === 0) ? (int) $heightOut[0] : 0;

        if ($imgHeight > 0 && $imgHeight <= $maxHeight) {
            // Found suitable size, break
            break;
        }
        $fontSize -= 4; // Decrease font size and try again
    }

    // Generate Shadow File
    $shadowPath = str_replace('.png', '_shadow.png', $outputPath);
    $cmdShadow = sprintf(
        "%s %s -background black -shadow 60x5+0+8 %s",
        escapeshellcmd($imCmd),
        escapeshellarg($outputPath),
        escapeshellarg($shadowPath)
    );
    executeCommand($cmdShadow);

    return [
        'text' => $outputPath,
        'shadow' => $shadowPath
    ];
}

/**
 * Creates the source text image (if provided).
 */
function createSourceImage($sourceText, $outputPath, $imCmd, $fonts)
{
    if (empty($sourceText)) {
        $cmd = sprintf("%s -size 1x1 xc:transparent %s", escapeshellcmd($imCmd), escapeshellarg($outputPath));
        executeCommand($cmd);
        return;
    }

    $text = pangoEscape($sourceText);
    $font = $fonts['secondary'];
    $markup = "<span font='{$font} 30' foreground='#ffffff' alpha='80%'>{$text}</span>";

    $cmd = sprintf(
        "%s -background transparent -gravity center -size 600x pango:%s %s",
        escapeshellcmd($imCmd),
        escapeshellarg($markup),
        escapeshellarg($outputPath)
    );
    executeCommand($cmd);
}

/**
 * Creates the footer (page signature) image.
 */
function createFooterImage($outputPath, $imCmd, $fonts)
{
    $text = pangoEscape("الأثر الطيب");
    $font = $fonts['secondary'];
    $markup = "<span font='{$font} 26' foreground='#ffffff' alpha='60%'>{$text}</span>";

    $cmd = sprintf(
        "%s -background transparent -gravity center -size 400x pango:%s %s",
        escapeshellcmd($imCmd),
        escapeshellarg($markup),
        escapeshellarg($outputPath)
    );
    executeCommand($cmd);
}

/**
 * Composes everything together into the final image.
 */
function composeFinalImage($bg, $textFiles, $sourceImg, $footerImg, $finalOut, $imCmd)
{
    $radialGradient = escapeshellarg('radial-gradient:rgba(0,0,0,0.6)-none');
    $cmd = sprintf(
        "%s %s " .
        "-size 1080x1080 %s -gravity center -composite " .
        // Composite Shadow (Y=-52 so it's slightly shifted down from -60)
        "%s -gravity center -geometry +0-52 -composite " .
        // Composite Main Text
        "%s -gravity center -geometry +0-60 -composite " .
        "%s -gravity center -geometry +0+250 -composite " .
        "%s -gravity south -geometry +0+80 -composite " .
        "-quality 92 %s",
        escapeshellcmd($imCmd),
        escapeshellarg($bg),
        $radialGradient,
        escapeshellarg($textFiles['shadow']),
        escapeshellarg($textFiles['text']),
        escapeshellarg($sourceImg),
        escapeshellarg($footerImg),
        escapeshellarg($finalOut)
    );

    executeCommand($cmd);
}

/**
 * Publishes image to Facebook Graph API.
 */
function publishToFacebook($imagePath, $pageId, $accessToken)
{
    $url = "https://graph.facebook.com/v23.0/{$pageId}/photos";

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => [
            'message' => "🌿\n\n#الأثر_الطيب",
            'source' => new CURLFile($imagePath),
            'access_token' => $accessToken,
        ],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 60,
    ]);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die("Curl Error: " . curl_error($ch));
    }

    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = json_decode($response, true);

    if ($httpCode === 200 && isset($result['id'])) {
        echo "تم نشر الصورة بنجاح ✅\n";
        echo "Post ID: " . $result['post_id'] . "\n";
        echo "Photo ID: " . $result['id'] . "\n";
        return true;
    } else {
        echo "فشل النشر:\n";
        echo $response . "\n";
        return false;
    }
}


/*
|--------------------------------------------------------------------------
| Main Execution Flow
|--------------------------------------------------------------------------
*/

$imCmd = getImCommand();

// Select random content
$selectedQuote = $quotes[array_rand($quotes)];

// Select random background
$backgrounds = [
    __DIR__ . '/bg1.png',
    __DIR__ . '/bg2.png',
    __DIR__ . '/bg3.png'
];
$inputBg = $backgrounds[array_rand($backgrounds)];

if (!file_exists($inputBg)) {
    die("صورة الخلفية غير موجودة.\n");
}

// Temporary files handling
$tmpDir = sys_get_temp_dir();
$uniq = uniqid();
$tmpBg = $tmpDir . "/bg_{$uniq}.jpg";
$tmpText = $tmpDir . "/txt_{$uniq}.png";
$tmpSource = $tmpDir . "/src_{$uniq}.png";
$tmpFooter = $tmpDir . "/ftr_{$uniq}.png";
$finalImage = __DIR__ . "/final_post.jpg"; // Must be in working dir for FB CURLFile access usually

try {
    // 1. Prepare BG
    prepareBackground($inputBg, $tmpBg, $imCmd);

    // 2. Generate Text Image
    $textFiles = createTextImage($selectedQuote, $tmpText, $imCmd, $fonts, $maxTextWidth, $maxTextHeight);

    // 3. Generate Source Image
    createSourceImage($selectedQuote['source'], $tmpSource, $imCmd, $fonts);

    // 4. Generate Footer Image
    createFooterImage($tmpFooter, $imCmd, $fonts);

    // 5. Combine All
    composeFinalImage($tmpBg, $textFiles, $tmpSource, $tmpFooter, $finalImage, $imCmd);

    // 6. Publish
    $isSuccess = publishToFacebook($finalImage, $pageId, $accessToken);

    // 7. Cleanup
    if ($isSuccess && $deleteImageAfterSuccessfulPost) {
        @unlink($finalImage);
    }

} catch (Exception $e) {
    echo "حدث خطأ أثناء المعالجة: " . $e->getMessage() . "\n";
} finally {
    // Always cleanup tmp files
    @unlink($tmpBg);
    @unlink($tmpText);
    if (isset($textFiles['shadow']))
        @unlink($textFiles['shadow']);
    @unlink($tmpSource);
    @unlink($tmpFooter);
}
