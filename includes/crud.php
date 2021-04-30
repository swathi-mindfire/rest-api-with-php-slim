<?php
require_once 'db-config.php';

class Queries {
    public $layout;
    public $conn;
    private $database = "users";
    private $serverIp = "192.168.10.62";
    private $userName = "Admin";
    private $password = "Sw@thifilem@ker1";
    function __construct()
    {
        $db = new DB($this->database,$this->serverIp,$this->userName,$this->password);
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
            $limit = 4;
            $start = ($pageno-1)*$limit;   
            $req = $this->conn->newFindCommand($this->layout);
            $req->setRange($start,$limit);
        }
        elseif($search){
            $req = $this->conn->newFindCommand($this->layout);
            $req->addFindCriterion("Name","==$search");
        }
        elseif($searchId){
            $req = $this->conn->newFindCommand($this->layout);
            $req->addFindCriterion("Id","==$searchId");            
        }
        else {
            $req = $this->conn->newFindAllCommand($this->layout);
        }
        
        $result = $req->execute();

        if(FileMaker::isError($result)){
            $error = $result;
            if($error->code==401){
                if($search || $searchId){
                    $response->getBody()->write(json_encode("No such Record Exist"));
                    return $response
                    ->withHeader('content-type','app/json')
                    ->withStatus(404); 
                }                
            }                   
        }
        else{                          
            $records = $result->getRecords();
            if($searchId){
                $userDetails= [];                
                $userDetails['Name']= $data['name'] ?? $records[0]->getField('Name');
                $userDetails['Email']= $data['email']?? $records[0]->getField('Email');
                $response->getBody()->write(json_encode($userDetails));          
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(200);
            }
            $users = array(array());
            $index=0;
            
            foreach($records as $record)
            {
                $users[$index]['Id'] = $record->getRecordId();
                $users[$index]['Name'] = $record->getField('Name');
                $users[$index]['Email'] = $record->getField('Email');        
                $index++;
            }
            if(count($users[0])>0){
                $response->getBody()->write(json_encode($users));          
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(200);
            }
            $response->getBody()->write(json_encode("No records in Requseted page"));          
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
            $newUserDetails['Name']=  $name;
            $newUserDetails['Email'] = $email;
            $newUserDetails['Password'] = $password;
            $this->setLayout("users"); 
            $req = $this->conn->newFindCommand($this->layout);         
            $req->addFindCriterion("Email","==$email");
            $tempResult = $req->execute();
            if(FileMaker::isError($tempResult)){
                $error = $tempResult;
                if($error->code==401){
                    $req = $this->conn->newAddCommand("users",$newUserDetails);
                    $result = $req->execute();
                    if(FileMaker::isError($result)){
                        $error = $result;                    
                        $response->getBody()->write(json_encode("unique mail but error"));
                        return $response
                        ->withHeader('content-type','app/json')
                        ->withStatus(500);           
                    }                   
                    $response->getBody()->write(json_encode(["name" => $name,"email" => $email]));
                    return $response
                    ->withHeader('content-type','app/json')
                    ->withStatus(201);
                }
                else{
                    $response->getBody()->write("error");
                    return $response
                    ->withHeader('content-type','app/json')
                    ->withStatus(500);  
                }                                   
            }
            else{
                $response->getBody()->write(json_encode("Email Alreay Exist Try Different Email"));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(409);  
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
            $response->getBody()->write(json_encode("$suggestion required"));        
            return $response
            ->withStatus(206);
        }
    }
    function updateUser($request,$response,$args){ 
        $id = $args['id'];
        $data = (array)json_decode($request->getBody()); 
        $this->setLayout("users"); 
        $findCommand =$this->conn->newFindCommand($this->layout);
        $findCommand->addFindCriterion('id',$id);
        $result = $findCommand->execute();   
        if (FileMaker::isError($result)) {
            if($result->code==401){
                $response->getBody()->write(json_encode("No such record Id Exist"));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(404);
            }
            else{
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(500);                 
            }              
        }
        else{
            $user = $this->conn->getRecordById($this->layout, $id);
            if(FileMaker::isError($user))
            {
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(500);                 
            }                          
            $userEditDetails= [];
            $userEditDetails['Name']= $data['name'] ?? $user->getField('Name');
            $userEditDetails['Email']= $data['email']?? $user->getField('Email');
            $userEditDetails['Password']= $data['password']?? $user->getField('Password');
            $userEdit = $this->conn->newEditCommand($this->layout,$id,$userEditDetails);
            $result = $userEdit->execute();
            if(FileMaker::isError($user))
            {
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(500);                 
            }            
                $response->getBody()->write(json_encode(["id" => $id,"name" => $userEditDetails['Name'],"email" => $userEditDetails['Email']]));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(200);            
        }
      
    }       
    public function deleteUser($request,$response,$args){
        $this->setLayout("users");  
        $id =$args['id'];            
            $delete = $this->conn->newDeleteCommand($this->layout , $id);
            $result = $delete->execute();
            if(FileMaker::isError($result))
            {   
                if($result->code==101){
                    $response->getBody()->write(json_encode("No record Found"));
                    return $response
                    ->withHeader('content-type','app/json')
                    ->withStatus(404);
                }            
                return $response
                    ->withHeader('content-type','app/json')
                    ->withStatus(500); 
            } 
            return $response ->withStatus(204);   
    }
    function loginAuthenticate($request,$response){
        $this->setLayout("users"); 
        $data = $request->getParsedBody();
        $email = $data['email'];
        $password = $data['password'];
        $findcmd = $this->conn->newFindCommand($this->layout);            
        $findcmd->addFindCriterion("Email","==$email");
        $result = $findcmd->execute();
        if(FileMaker::isError($result)){
            if($result->code==401){
                $response->getBody()->write(json_encode("Email Not Exists"));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(401);
            }
            return $response
                ->withHeader('content-type','app/json')
                ->withStatus(500);

        }
        $records = $result->getRecords();
        $actualPassword = $records[0]->getField("Password");
        if($actualPassword === $password){
            $response->getBody()->write(json_encode("Login Succeess"));
            return $response
            ->withHeader('content-type','app/json')
            ->withStatus(200);

        }
        $response->getBody()->write(json_encode("Invalid Password"));
        return $response
        ->withHeader('content-type','app/json')
        ->withStatus(401);
        
       
    
        
    }   
}
    
?>