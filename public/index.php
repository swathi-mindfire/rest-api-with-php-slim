<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Slim\Factory\AppFactory;
require __DIR__ . '/../vendor/autoload.php';
$app = AppFactory::create();
require __DIR__ . '/../src/controllers/middleware.php';
require __DIR__ . '/../src/config/routes.php';

?>