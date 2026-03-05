<?php


$pageId = '434309674008905';


$token = 'EAARdg15lAY8BQxYmZA3dZCYGbQwooRWHB7JyQBxl90tqhgN6CiziAaLVRvZANHCN2inxZB7DZCw9idPnEOcR5Sn3Ab6eoshqeYWcNOMRPRNwrSMtGHkftdi6pW0l45ZAFhcKSh3An3JHty0oAI3QJfku4OGJn7WWSvQw6FvgUARjxfh9bNInRHILtfyXhRJpfF';

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
