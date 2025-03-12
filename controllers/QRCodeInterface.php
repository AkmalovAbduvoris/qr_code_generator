<?php

namespace Voris;


interface QRCodeInterface {
  public function createQRCode(string $qrText);
  public function readQRCode(string $filePath);
}
