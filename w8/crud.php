<?php
require __DIR__ . '/vendor/autoload.php';

use Arhitector\Yandex\Disk;

session_start();
if (!isset($_SESSION['access_token'])) {
    header('Location: login.php');
    exit();
}

$accessToken = $_SESSION['access_token'];

$disk = new Disk($accessToken);

$action = $_GET['action'] ?? null;
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if ($action === 'upload' && !empty($_FILES['file'])) {
            $uploadedFile = $_FILES['file'];
            $uploadPath = '/' . basename($uploadedFile['name']);

            $disk->getResource($uploadPath)->upload($uploadedFile['tmp_name']);
            $message = 'Файл успешно загружен.';
        } elseif ($action === 'delete') {
            $fileToDelete = $_POST['file_name'];
            $disk->getResource($fileToDelete)->delete();
            $message = 'Файл успешно удален.';
        }
    } catch (Exception $e) {
        $message = 'Ошибка: ' . $e->getMessage();
    }
}

try {
    $files = $disk->getResource('/')->toArray();
} catch (Exception $e) {
    $files = [];
    $message = 'Ошибка при получении списка файлов: ' . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Яндекс.Диск CRUD</title>
</head>
<body>
    <h1>Яндекс.Диск CRUD</h1>

    <?php if (!empty($message)): ?>
        <p><strong><?= htmlspecialchars($message) ?></strong></p>
    <?php endif; ?>

    <h2>Загрузить файл</h2>
    <form action="?action=upload" method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit">Загрузить</button>
    </form>

    <h2>Список файлов</h2>
    <table border="1" cellpadding="10">
        <thead>
            <tr>
                <th>Имя файла</th>
                <th>Размер</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($files)): ?>
                <?php foreach ($files as $file): ?>
                    <tr>
                        <td><?= htmlspecialchars($file['name']) ?></td>
                        <td><?= htmlspecialchars($file['size'] ?? 'Неизвестно') ?> байт</td>
                        <td>
                            <form action="?action=delete" method="post" style="display:inline;">
                                <input type="hidden" name="file_name" value="<?= htmlspecialchars($file['path']) ?>">
                                <button type="submit">Удалить</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">Нет файлов.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
