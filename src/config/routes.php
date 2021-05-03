<?php
use Slim\Factory\AppFactory;
$app = AppFactory::create();
require __DIR__ . '/../services/crud.php';
$dbObj= new Queries (); 
$app->get('/users[/{id}]','Queries:getUsers');
$app->post('/users','Queries:createUser');
$app->delete('/users/{id}','Queries:deleteUser');
$app->put('/users/{id}','Queries:updateUser');
$app->post('/users/login','Queries:loginAuthenticate');
$app->run();