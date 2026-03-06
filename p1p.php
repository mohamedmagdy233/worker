<?php


$pageId = '434309674008905';


$token = 'EAARdg15lAY8BQ4POkyhj3beYt5YBDHWp2RaFW5MNr7oFSgiZBrSPDAtXi07vA6EVPafJLQ3YlS1LID5CW8OVSifUsR6X6XWnOcEV1aaceZBZA7OezQbxzISGP49zfiFuZBGkTLWqbwMbHLZBh7bje0E82XFgpZBJEZAYFeTPZAVdlZBYbP9BzhhYw8S95vyvQhzZCOprLr';

$message = "إِنَّ اللَّهَ وَمَلَائِكَتَهُ يُصَلُّونَ عَلَى النَّبِيِّ ۚ يَا أَيُّهَا الَّذِينَ آمَنُوا صَلُّوا عَلَيْهِ وَسَلِّمُوا تَسْلِيمًا ﷺ.";

$url = "https://graph.facebook.com/v19.0/{$pageId}/feed";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'message' => $message,
    'access_token' => $token
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

$response = curl_exec($ch);
curl_close($ch);


echo $response;
