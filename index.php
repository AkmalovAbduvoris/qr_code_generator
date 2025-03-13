<?php
declare(strict_types=1);

require 'vendor/autoload.php';

use Voris\Bot;

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

require 'routes.php';
exit();


