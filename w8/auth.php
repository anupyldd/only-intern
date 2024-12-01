<?php
require __DIR__ . '/internal.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];

    $tokenUrl = 'https://oauth.yandex.ru/token';
    $postFields = [
        'grant_type' => 'authorization_code',
        'code' => $code,
        'client_id' => CLIENT_ID,
        'client_secret' => CLIENT_SECRET,
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $tokenUrl);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postFields));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $responseData = json_decode($response, true);

    if (isset($responseData['access_token'])) {
        session_start();
        $_SESSION['access_token'] = $responseData['access_token'];

        header('Location: index.php');
        exit();
    } else {
        echo "Ошибка получения токена: " . $responseData['error_description'];
    }
} else {
    echo "Не удалось получить код авторизации.";
}
?>
