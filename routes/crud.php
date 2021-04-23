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
        $sql = " SELECT id,name,email FROM users LIMIT $start,$limit";
    }
    elseif($search){
        $sql = "SELECT id, name,email FROM users WHERE name = '$search' ";
    }
    elseif($id){
        $sql = "SELECT id,name,email FROM users WHERE id = '$id' ";
    }
    else {
        $sql = " SELECT id,name,email FROM users";
    }   
    try {
        $db = new DB();
        $conn = $db->connect();
        $stmt =  $conn->query($sql);
        if($search || $id)
        $users = $stmt->fetch(PDO::FETCH_OBJ);
        else
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $db = null;
        if (empty($users)) {
            if($search || $id)
                $statuscode = 404;
            else
                $statuscode = 204;

            return $response
            ->withStatus($statuscode);
        }
        else{
            $response->getBody()->write(json_encode($users));           
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);
        }
                
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
    $sql2 = "SELECT * FROM users WHERE email = '$email' ";

    if($name && $email && $password){
        $sql = "INSERT  INTO users(name,email,password) VALUES(:name,:email,:password)";
        try {
            $db = new DB();
            $conn = $db->connect();
            $stmt =  $conn->query($sql2);
            $users = $stmt->fetchAll(PDO::FETCH_OBJ);
            if (empty($users)) {
                $stmt =  $conn->prepare($sql);
                $stmt->bindParam(':name',$name);
                $stmt->bindParam(':email',$email);
                $stmt->bindParam(':password',$password);
                $res = $stmt->execute();        
                $db = null;
                $response->getBody()->write(json_encode(["name" => $name,"email" => $email]));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(201);
                
            }
            $response->getBody()->write("Email already exists");
            return $response->withStatus(409);    
           
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
        $suggestion = "";
        if(!$name)
        $suggestion .= "Name ";
        if(!$email)
        $suggestion .= " Email ";
        if(!$password)
        $suggestion .= " password";
        $response->getBody()->write("$suggestion required");        
        return $response
        ->withStatus(206);
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
        $db = null; 
        if($res){
            $stmt = $conn->prepare($sql);
            $result =  $stmt->execute();                          
            return $response
            ->withStatus(204);
        }                 
        return $response
        ->withStatus(404);     
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
            $response->getBody()->write(json_encode(["name" => $name,"email" => $email]));
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);
        }
        else {
            return $response
            ->withStatus(404);
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
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);
        }
        else{            
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(401);
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