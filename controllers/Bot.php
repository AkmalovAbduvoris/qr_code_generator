<?php
declare(strict_types=1);
namespace Voris;
use GuzzleHttp\Client;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
class Bot {
	private        $text;
	private string $firstName;
	private int    $userId;
	private	       $img;
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
		//$this->text = $update->message->text;
		$this->userId = $update->message->chat->id;
    if (isset($update->message->photo)) {
      $this->img = end($update->message->photo)->file_id;
    }
		//match ($this->text) {
		//	'/start' => $this->handleStartCommand(),
		//	$this->text => $this->sendQrImage(),
		//	$this->img => $this->sendPhotoText(),
    //};
    if ($this->text === '/start') {
      $this->handleStartCommand();
    } elseif (!empty($this->text)) {
      $this-> sendQrImage();
    } elseif (!empty($this->img)) {
      $this->sendPhotoText();
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

	public function sendQrImage() {
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
	
//   public function sendPhotoText() {
//     $fileInfo = $this->http->get("getFile", [
//       'query' => ['file_id' => $this->img]
//     ]);
//     $fileInfo = json_decode($fileInfo->getBody()->getContents(), true);

//     if (!$fileInfo['ok']) {
//       $this->http->post("sendMessage", [
//         'form_params' => [
//           'chat_id' => $this->userId,
//           'text' => "Rasmni olishda xatolik"
//         ]
//       ]);
//       return;
//     }

// 		$this->http->post('sendMessage', [
// 			'form_params' => [
// 				'chat_id' => $this->userId,
// 				'text' => $fileInfo
// 			]
//     ]);
// 	}
public function sendPhotoText() {
  if (!$this->img) {
      return;
  }

  // 1. Telegram serveridan rasm haqida ma'lumot olish
  $response = $this->http->get("getFile", [
      'query' => ['file_id' => $this->img]
  ]);

  $fileInfo = json_decode($response->getBody()->getContents(), true);

  if (!$fileInfo['ok'] || !isset($fileInfo['result']['file_path'])) {
      $this->http->post('sendMessage', [
          'form_params' => [
              'chat_id' => $this->userId,
              'text' => "❌ getFile API dan rasm yo‘li olinmadi! JSON: " . json_encode($fileInfo)
          ]
      ]);
      return;
  }

  // 2. Telegram serveridagi faylga to‘g‘ri URL yaratish
  $filePath = $fileInfo['result']['file_path'];
  $fileUrl = "https://api.telegram.org/file/bot" . str_replace("https://api.telegram.org/bot", "", $this->api) . "/" . $filePath;

  // 3. Faylni yuklab olish
  $fileData = file_get_contents($fileUrl);
  file_put_contents("qrCode.png" ,$fileData);
  if ($fileData === false) {
      $this->http->post('sendMessage', [
          'form_params' => [
              'chat_id' => $this->userId,
              'text' => "❌ Telegram serveridan rasm yuklab olinmadi!\nURL: $fileUrl"
          ]
      ]);
      return;
  }

    try {
      $res = (new QRCode)->readFromFile("../qrCode.png");
    } catch (\Exception $e) {
      $res = $e->getMessage();
    }

  $this->http->post('sendMessage', [
      'form_params' => [
          'chat_id' => $this->userId,
          'text' => $res
      ]
  ]);
}

}
