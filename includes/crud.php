<?php
require_once 'db-config.php';

class Queries {
    private $layout;
    private $conn;
    function __construct()
    {
        $db = new DB("users","192.168.10.62","Admin","Sw@thifilem@ker1");
        $this->conn = $db->connect();
    }

    function setLayout($layout){
        $this->layout = $layout;
    }
    
    public function getUsers($request,$response,$args){
        $this->setLayout("users");       
        $searchId = $args['id'] ?? null;
        $pageno =  $request->getQueryParams()['page']?? null;
        $search =  $request->getQueryParams()['search']?? null; 
        if($pageno){
            $limit = 3;
            $start = ($pageno-1)*$limit;   
            $req = $this->conn->newFindCommand($this->layout);
            $req->setRange($start,$limit);
        }
        elseif($search){
            $req = $this->conn->newFindCommand($this->layout);
            $req->addFindCriterion("name",$search);
        }
        elseif($searchId){
            $req = $this->conn->newFindCommand($this->layout);
            $req->addFindCriterion("Id",$searchId);
        }
        else {
            $req = $this->conn->newFindAllCommand($this->layout);
        }
        
        $result = $req->execute();

        if(FileMaker::isError($result)){
            $error = $result;           
            $response->getBody()->write(json_encode($error));
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(500);           
        }
        else{
            // $users = $result->getRecords();
            // if(empty($users)){
            //     if($search || $searchId)
            //     $statuscode = 404;
            //     else
            //     $statuscode = 204;

            // return $response
            // ->withStatus($statuscode);
            // }   
            $records = $result->getRecords();
            $users = array(array());
            $index=0;
            foreach($records as $record)
            {
                $users[$index]['Id'] = $record->getRecordId();
                $users[$index]['Name'] = $record->getField('Name');
                $users[$index]['Email'] = $record->getField('Email');        
                $index++;
            }                  
            $response->getBody()->write(json_encode($users));          
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);
        }
        
    }
    public function createUser($request,$response){
        $data = $request->getParsedBody();
        $name = $data['name']?? null;
        $email = $data['email']?? null;
        $password = $data['password']?? null;
        if($name && $email && $password){
            $req = $this->conn->newFindCommand($this->layout);
            $req->addFindCriterion("Email",$email);
            $tempResult = $req->execute();
            if(FileMaker::isError($tempResult)){
                $error = $tempResult;           
                $response->getBody()->write(json_encode("error"));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(500);           
            }  
            else{
                $records = $tempResult->getRecords();

            }                  
            
            if(empty($records)){
                $newUserDetails['Name']=  $name;
                $newUserDetails['Email'] = $email;
                $newUserDetails['Password'] = $password;
                $req = $this->conn->newAddCommand("users",$newUserDetails);
                $result = $req->execute();
                if(FileMaker::isError($result)){
                    $error = $result;                    
                    $response->getBody()->write(json_encode($error));
                    return $response
                    ->withHeader('content-type','app/json')
                    ->withStatus(500);           
                }                   
                $response->getBody()->write(json_encode(["name" => $name,"email" => $email]));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(201);
            }            
            $response->getBody()->write("Email already exists");
            return $response->withStatus(409); 
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
    } 
    public function delete($response,$args){
        $id =$args['id'];        
        $findCommand =$this->conn->newFindCommand($this->layout);
        $findCommand->addFindCriterion('id', "==$id");
        $result = $findCommand->execute();    
       if ($this->class::isError($result)) {
            $error = $result;                    
            $response->getBody()->write(json_encode($error));
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(500); 
       }
       else{
        $records = $result->getRecords(); 
        $records[0]->delete();
        return $response
        ->withStatus(204); 
        // return $response
        // ->withStatus(404); 
       }      
    }
    function updateUser($request,$response,$args){ 
        $id = $args['id'];
        $data = (array)json_decode($request->getPBody()); 
        $findCommand =$this->conn->newFindCommand($this->layout);
        $findCommand->addFindCriterion('id', $id);
        $result = $findCommand->execute();   
        if ($this->class::isError($result)) {
            if($result->code =401){
                return $response
                ->withStatus(404);
            }
            else{
                $error = $result;                    
                $response->getBody()->write(json_encode($error));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(500);                 
            }              
        }
        else{
            $user = $result->getRecords();
            $id= $user[0]->getField('Id');
            $userEditDetails= [];
            $userEditDetails['Name']= $data['name'] ?? $user[0]->getField('Name');
            $userEditDetails['Email']= $data['email']?? $user[0]->getField('Email');
            $userEditDetails['Password']= $data['password']?? $user[0]->getField('Password');
            $userEdit = $this->conn->newEditCommand($this->layout,$id,$userEditDetails);
            $result = $userEdit->execute();
            if($result){
                $response->getBody()->write(json_encode(["id" => $id,"name" => $userEditDetails['Name'],"email" => $userEditDetails['Email']]));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(200);
            }

        }
      
    }
    
    

   
}
    
?>