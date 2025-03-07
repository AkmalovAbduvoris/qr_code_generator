<?php
declare(strict_types=1);
namespace controllers;
use GuzzleHttp\Client;
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;
class Bot {
	private string $text;
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

		$this->text = $update->message->text;
		$this->userId = $update->message->chat->id;
		$this->img = $update->message->photo->file_id;
		match ($this->text) {
			'/start' => $this->handleStartCommand(),
			$this->text => $this->sendQrImage(),
			$this->img => $this->sendPhotoText(),		
		};
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
	
	public function sendPhotoText() {
		$this->http->post('sendMessage', [
			'form_params' => [
				'chat_id' => $this->userId,
				'text' => "Salom bu rasmning texti"
			]
		]);
	}
}
