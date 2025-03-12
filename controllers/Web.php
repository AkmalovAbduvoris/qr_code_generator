<?php
declare(strict_types=1);
namespace Voris;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Web {
    public function createQRCode(string $qrText): void {
        $options = new QROptions([
            'outputType' => QRCode::OUTPUT_IMAGE_PNG,
            'eccLevel' => QRCode::ECC_L,
        ]);

        $qrcode = new QRCode($options);
        $qrFile = 'qrCode.png';
        $qrcode->render($qrText, $qrFile);
    }

    public function readQRCode(string $filePath): string {
        if (file_exists($filePath)) {
            try {
                $result = (new QRCode)->readFromFile($filePath);
                return (string) $result;
            } catch (\Throwable $e) {
                return "Xato: " . $e->getMessage();
            }
        } else {
            return "Fayl topilmadi";
        }
    }
}
