<?php
require __DIR__ . '/../controllers/usersController.php';
$app->get('/users[/{id}]','UsersController:getUsers');
$app->post('/users','UsersController:createUser');
$app->delete('/users/{id}','UsersController:deleteUser');
$app->put('/users/{id}','UsersController:updateUser');
$app->post('/users/login','UsersController:loginAuthenticate');
$app->run();