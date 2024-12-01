<?php

require __DIR__ . '/internal.php';

$authUrl = "https://oauth.yandex.ru/authorize?response_type=code&scope=cloud_api:disk&client_id=" . CLIENT_ID . "&redirect_uri=" . urlencode(REDIRECT_URI);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Авторизация через Яндекс</title>
</head>
<body>
    <a href="<?= $authUrl ?>">Войти через Яндекс</a>
</body>
</html>
