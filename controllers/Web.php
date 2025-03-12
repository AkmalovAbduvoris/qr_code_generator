<?php
declare(strict_types=1);
namespace Voris;

use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

class Web implements QRCodeInterface {
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
        if (!file_exists($filePath)) {
            return "Xato: Fayl topilmadi - $filePath";
        }
        
        // Validate file extension
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        $allowedExtensions = ['png', 'jpg', 'jpeg', 'gif'];
        
        if (!in_array($extension, $allowedExtensions)) {
            return "Xato: Noto'g'ri fayl formati. Qo'llab-quvvatlanadigan formatlar: " . implode(', ', $allowedExtensions);
        }
        
        try {
            $result = (new QRCode)->readFromFile($filePath);
            return (string) $result;
        } catch (\Exception $e) {
            return "Xato: QR kodni o'qishda xatolik - " . $e->getMessage();
        } catch (\Error $e) {
            return "Xato: Tizim xatosi - " . $e->getMessage();
        } catch (\Throwable $e) {
            return "Xato: Kutilmagan xatolik - " . $e->getMessage();
        }
    }
}
