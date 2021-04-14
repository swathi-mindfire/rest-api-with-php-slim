<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../config/db.php';



$app = AppFactory::create();
error_reporting(E_ALL);
set_error_handler(function ($severity, $message, $file, $line) {
    if (error_reporting() & $severity) {
        throw new \ErrorException($message, 0, $severity, $file, $line);
    }
});
require __DIR__ . '/../routes/users.php';
// $app->get('/', function (Request $request, Response $response, $args) {
//     $response->getBody()->write("Hello world!");
//     return $response;
// });


// $app->get('/hello', function (Request $request, Response $response, $args) {
//     $response->getBody()->write("Hello swathi!");
//     return $response;
// });
// $app->get('/users',function (Request $request ,Response $response) {
//     $sql = " SELECT * FROM users";


//     try {
//         $db = new DB();
//         $conn = $db->connect();

//         $stmt =  $conn->query($sql);
//         $users = $stmt->fetchAll(PDO::FETCH_OBJ);
//         $db = null;

//         $response->getBody()->write(json_encode($users));
//         return $response
//         ->withHeader('content-type','app/json')
//         ->withStatus(200);


//     } catch(PDOException  $e){

//         $error = array(
//             "message" =>$e->getMessage()
//         );

//         $response->getBody()->write(json_encode($error));
//         return $response
//         ->withHeader('content-type','app/json')
//         ->withStatus(500);;

//     }
// });


$app->run();