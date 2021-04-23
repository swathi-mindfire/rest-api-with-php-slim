<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

$app = AppFactory::create();
$app->get('/users[/{id}]',function (Request $request ,Response $response,array $args) {
    $id = $args['id'] ?? null;
    $pageno =  $request->getQueryParams()['page']?? null;
    $search =  $request->getQueryParams()['search']?? null; 
    if($pageno){
        $limit = 3;
        $start = ($pageno-1)*$limit;   
        $sql = " SELECT * FROM users LIMIT $start,$limit";
    }
    elseif($search){
        $sql = "SELECT * FROM users WHERE name = '$search' ";
    }
    elseif($id){
        $sql = "SELECT * FROM users WHERE id = '$id' ";
    }
    else {
        $sql = " SELECT * FROM users";
    }   
    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt =  $conn->query($sql);
        $users = $stmt->fetchAll(PDO::FETCH_OBJ);
        if (empty($users)) {
            $response->getBody()->write(json_encode(["status" => "No record found"]));
        }
        else{
            $response->getBody()->write(json_encode($users));

        }
        
        $db = null;
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(200);
    } catch(PDOException  $e){
        $error = array("message" =>$e->getMessage());
        $response->getBody()->write(json_encode($error));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(500);;
    }
});
$app->post('/users',function (Request $request ,Response $response,array $args) {
    $data = $request->getParsedBody();
    $name = $data['name']?? null;
    $email = $data['email']?? null;
    $password = $data['password']?? null;
    if($name && $email && $password){
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
            $response->getBody()->write(json_encode(["status" => "Inserted successfully"]));
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
    }
    else  {
        $response->getBody()->write(json_encode(["status" => "Insufficient details,Name email and passsword all fields are mandatory"]));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(200);
    }
   
});
$app->delete('/users/{id}',function (Request $request ,Response $response,array $args) {
    $id = $args['id'];
    $sql = "DELETE FROM users WHERE id = $id";
    $sql1 = "SELECT * FROM users WHERE id = '$id' ";
    try {
        $db = new DB();
        $conn = $db->connect();
        $res = $conn->query($sql1);
        $res = $res->fetch(PDO::FETCH_ASSOC);
        if($res){
            $stmt = $conn->prepare($sql);
            $result =  $stmt->execute();       
            $db = null;
            $response->getBody()->write(json_encode(["status" => "Deleted successfully"]));
        }
        else  $response->getBody()->write(json_encode(["status" => "User Not Found"]));
      
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
$app->put('/users/{id}',function (Request $request ,Response $response,array $args) {
    $id = $args['id'];
    $data = (array)json_decode($request->getBody()); 
    $sql = "UPDATE users SET name = :name,email = :email,password = :password   WHERE id = :id";
    $sql1 = "SELECT * FROM users WHERE id = '$id' ";
    try {
        $db = new DB();
        $conn = $db->connect();
        $res = $conn->query($sql1);
        $res = $res->fetch(PDO::FETCH_ASSOC);
        if($res){
            $name = $data['name'] ?? $res['name'];
            $email = $data['email']?? $res['email'];
            $password = $data['password']?? $res['password'];
            $stmt =  $conn->prepare($sql);
            $stmt->bindParam('id',$id);
            $stmt->bindParam(':name',$name);
            $stmt->bindParam(':email',$email);
            $stmt->bindParam(':password',$password);
            $res = $stmt->execute();       
            $db = null;
            $response->getBody()->write(json_encode(["status" => "updated successfully"]));
        }
        else $response->getBody()->write(json_encode(["status" => "user not found"]));
      
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
            $response->getBody()->write(json_encode(["status"=> "login success"]));
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