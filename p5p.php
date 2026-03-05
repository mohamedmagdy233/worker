<?php


$pageId = '434309674008905';


$token = 'EAARdg15lAY8BQ8203lA7fH7JJdc0mk3XsNXr3aeNKS8s7X9K9uioOttn7pQReOBZAHqSX61LFDNwI3NlyIRfN6hqHDST6iR9ZCIZAkPTCmcjf6rn2QvrXZBfK5eBGkESubwJC8lb4WKNNktLqNEpyE0UIVd8F0Gz4M5F0DAgBpAxEoLK0qeyZAhm7vsgBFrTHH6mjhR6VAAVB5njfnZBysoZBOSNvsAsWcKZAfMnpGQZD';

$message = "اللهم صلِّ وسلم وبارك على نبينا محمد.";

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
