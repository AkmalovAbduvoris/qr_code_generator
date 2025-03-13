<?php
declare(strict_types=1);

namespace Voris;


class Route {
  public static function handleBot(): void {
    $update = file_get_contents('php://input');

    if ($update) {
      (new Bot($_ENV['TOKEN']))->handle($update);
    }
  }

  public static function handleWeb(): void {
    require 'views/web.php';
  }
}
