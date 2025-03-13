<?php
declare(strict_types=1);
namespace Voris;
use GuzzleHttp\Client;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
class Bot implements QRCodeInterface {
	private        $text;
	private string $firstName;
	private int    $userId;
	private        $img;
	private string $api;
	private        $http;

	public function __construct(string $token) {
		$this->api = "https://api.telegram.org/bot$token/";
		$this->http = new Client(['base_uri' => $this->api]);
	}

	public function handle(string $update) {
		$update = json_decode($update);
    if (isset($update->message->text)) {
      $this->text = $update->message->text;
    }
		$this->userId = $update->message->chat->id;
    if (isset($update->message->photo)) {
      $this->img = end($update->message->photo)->file_id;
    }

    if ($this->text === '/start') {
      $this->handleStartCommand();
    } elseif (!empty($this->text)) {
      $this-> createQRCode(" ");
    } elseif (!empty($this->img)) {
      $this->readQRCode('qrCode.png');
    }
	}

	public function handleStartCommand() {
		$this->http->post('sendMessage', [
			'form_params' => [
				'chat_id' => $this->userId,
				'text' => "Salom"
			]
		]);
	}

	public function createQRCode(string $qrText) {
		$options = new QROptions([
			'outputType' => QRCode::OUTPUT_IMAGE_PNG,
			'eccLevel' => QRCode::ECC_L,
		]);
		$qrcode = new QRCode($options);
		$qrFile = 'qrCode.png';
		$qrcode->render($this->text, $qrFile);
		$this->http->post('sendPhoto', [
			'multipart' => [
				['name' => 'chat_id', 'contents' => $this->userId],
				['name' => 'photo', 'contents' => fopen($qrFile,'r')]
			]
		]);
	}

public function readQRCode(string $filePath) {
  if (!$this->img) {
      return;
  }
  $response = $this->http->get('getFile', [
    'query' => ['file_id' => $this->img ] 
  ]);
  $fileInfo = json_decode($response->getBody()->getContents(), true);
  $filePath = $fileInfo['result']['file_path'];
  $fileUrl = "http://api.telegram.org/file/bot7589345403:AAFn6WkXEB3JalFZFuxO78hQPpgFd52s0Aw/" . $filePath;
  $imageResponse = $this->http->get($fileUrl);
  if (file_exists("qrCode.png")) {
    unlink("qrCode.png");
  }
  file_put_contents("qrCode.png", $imageResponse->getBody()->getContents());

  try {
    $qrReader = new QRCode();
    $qrResult = $qrReader->readFromFile("qrCode.png");
    $res = $qrResult->data;
  } catch (Exception $e) {
      $res = "❌ QR kod o‘qib bo‘lmadi! Xato: " . $e->getMessage();
  }
  $this->http->post('sendMessage', [
      'form_params' => [
          'chat_id' => $this->userId,
          'text' => print_r($res, true)
      ]
  ]);
}
}
