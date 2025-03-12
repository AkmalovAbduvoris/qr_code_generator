<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use Voris\Bot;

//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
//$dotenv->load();
//$update = file_get_contents('php://input');
//if($update) {
	//(new Bot($_ENV['TOKEN']))->handle($update);
//}
//exit();
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
use Voris\Web;

if (isset($_GET['text'])) {
    (new Web())->createQRCode($_GET['text']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $res = (new Web())->readQRCode($_FILES['file']['tmp_name']);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code Generator</title>
    <style>
        img {
            width: 150px;
            height: 150px;
        }
    </style>
</head>
<body>
    <form action="/" method="get">
        <input type="text" name="text" placeholder="Matnni kiriting">
        <button type="submit">QR kod yaratish</button>
    </form>

    <?php if (isset($_GET['text'])): ?>
        <h3>Yaratilgan QR kod:</h3>
        <img src="qrCode.png" alt="QR Code">
        <br>
        <a href="qrCode.png" download>
            <button>📥 PNG yuklab olish</button>
        </a>
    <?php endif; ?>

    <form action="/" method="post" enctype="multipart/form-data">
        <input type="file" name="file">
        <button type="submit">QR kodni o'qish</button>
    </form>
    <?php
        if($res) {
            echo $res;
        }
    ?>
</body>
</html>
