<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();

$app->get('/users',function (Request $request ,Response $response) {
    $sql = " SELECT * FROM users";


    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt =  $conn->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);
        $db = null;

        $response->getBody()->write(json_encode($users));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(200);


    } catch(PDOException  $e){

        $error = array(
            "message" =>$e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(500);;

    }
});
$app->post('/users/register',function (Request $request ,Response $response,array $args) {
    $data = $request->getParsedBody();
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $sql = "INSERT  INTO users(name,email,password) VALUES(:name,:email,:password)";


    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt =  $conn->prepare($sql);
        $stmt->bindParam(':name',$name);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password',$password);

        $res = $stmt->execute();
        
        $db = null;

        $response->getBody()->write(json_encode($res));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(200);


    } catch(PDOException  $e){

        $error = array(
            "message" =>$e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(500);;

    }
});
$app->delete('/users/delete/{id}',function (Request $request ,Response $response,array $args) {

    $id = $args['id'];
    $sql = "DELETE FROM users WHERE id = $id";


    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt = $conn->prepare($sql);

        $result =  $stmt->execute();
       
        $db = null;

        $response->getBody()->write(json_encode($result));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(200);


    } catch(PDOException  $e){

        $error = array(
            "message" =>$e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(500);;

    }
});
$app->put('/users/update/{id}',function (Request $request ,Response $response,array $args) {
    $id = $args['id'];
    $data = (array)json_decode($request->getBody());
    $name = $data['name'];
    $email = $data['email'];
    $password = $data['password'];
    $sql = "UPDATE users SET name = :name,email = :email,password = :password   WHERE id = :id";


    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt =  $conn->prepare($sql);
        $stmt->bindParam('id',$id);
        $stmt->bindParam(':name',$name);
        $stmt->bindParam(':email',$email);
        $stmt->bindParam(':password',$password);

        $res = $stmt->execute();
        
        $db = null;

        $response->getBody()->write(json_encode($res));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(200);


    } catch(PDOException  $e){

        $error = array(
            "message" =>$e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(500);;

    }
});
$app->post('/users/login',function (Request $request ,Response $response,array $args) {
    $data = $request->getParsedBody();
    $email = $data['email'];
    $password = $data['password'];
    $sql = "SELECT * FROM users WHERE email = '$email' and password = '$password' ";
    


    try {
        $db = new DB();
        $conn = $db->connect();

        $stmt =  $conn->query($sql);
        $res = $stmt->fetchAll(PDO::FETCH_OBJ);
      
        
        $db = null;
        if($res){
            
            $response->getBody()->write(json_encode(["ok"=> "login success"]));
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);

        }
        else{
            
            $response->getBody()->write(json_encode(["Error" =>"Invalid credentials"]));
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);
        }



    } catch(PDOException  $e){

        $error = array(
            "message" =>$e->getMessage()
        );

        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(500);;

    }
});




?>