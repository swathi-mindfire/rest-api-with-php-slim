<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';
$app = AppFactory::create();
require __DIR__ . '/../includes/crud.php';
$dbObj2= new Queries (); 
$app->get('/users[/{id}]',function (Request $request ,Response $response,array $args) {
    $dbObj = new Queries ();  
    $response =  $dbObj->getUsers($request,$response,$args);
    return $response;
});
$app->post('/users',function (Request $request ,Response $response) {
    $dbObj = new Queries ();  
    $response = $dbObj->createUser($request,$response);
    return $response;
});
$app->delete('/users/{id}',function (Request $request ,Response $response,array $args) {
    $dbObj = new Queries ();  
    $response = $dbObj->deleteUser($request,$response,$args);
    return $response;
});
$app->put('/users/{id}',function (Request $request ,Response $response,array $args) {
    $dbObj = new Queries ();  
    $response = $dbObj->updateUser($request,$response,$args);
    return $response;
    
});
$app->post('/users/login',function (Request $request ,Response $response,array $args) {
    $dbObj = new Queries ();  
    $response = $dbObj->loginAuthenticate($request,$response,$args);
    return $response;
    
});





$app->run();