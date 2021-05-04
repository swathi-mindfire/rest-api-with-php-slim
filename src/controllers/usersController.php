<?php
require_once __DIR__ . '/../services/sanitize.php';
require_once __DIR__ . '/../services/crudoperations.php';
class UsersController extends FileMakerCrud { 
   public $layout="users";
    public function __construct()
    {   
        parent::__construct($this->layout);
    }
    public function getUsers($request,$response,$args){          
        $searchId   =  $args['id'] ?? null;
        $pageno     =  $request->getQueryParams()['page']?? null;
        $search     =  $request->getQueryParams()['search']?? null; 
        $searchId   =  sanitizeData($searchId);
        $pageno     =  sanitizeData($pageno);
        $search     =  sanitizeData($search);
        $findarr=[];   
        if($search){
            $find['field']="Name";
            $find['value'] = $search;
            $result = $this->getUser($find);             
        }
        elseif($searchId){
            $find['field']="Id";
            $find['value'] = $searchId;
            $result = $this->getUser($find);           
        }
        elseif($pageno){
            $result = $this->paginate($pageno);
        }
        else
            $result = $this->getAllUsers($findarr);                  
    
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
                $userDetails['Name']= $records[0]->getField('Name');
                $userDetails['Email']= $records[0]->getField('Email');
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
            $newUserDetails['Name']= sanitizeData($name);
            $newUserDetails['Email'] = sanitizeData($email);
            $newUserDetails['Password'] = $password;
            $find['field'] = "Email";
            $find['value'] = $email;
            $tempResult = $this->getUser($find); 
            if(FileMaker::isError($tempResult)){
                $error = $tempResult;
                if($error->code==401){
                   $result= $this->create($newUserDetails);
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
                $response->getBody()->write(json_encode("Email already Exist"));
                return $response
                ->withHeader('content-type','app/json')
                ->withStatus(409);  
            }  
        }
        else{
            $suggestion = "";
            if(!$name)
            $suggestion .= "Name ";
            if(!$email)
            $suggestion .= " Email ";
            if(!$password)
            $suggestion .= " password";
            $response->getBody()->write(json_encode("$suggestion required"));        
            return $response
            ->withStatus(400);
        }
    }
    function updateUser($request,$response,$args){ 
        $id = sanitizeData($args['id']);
        $data = (array)json_decode($request->getBody());
        $find['field'] = "Id";
        $find['value'] = $id;
        $result = $this->getUser($find);   
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
            $result = $this->update($id,$userEditDetails);
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
        $id = sanitizeData($args['id']);            
            $result = $this->delete($id);
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
        $data = $request->getParsedBody();
        $email = sanitizeData($data['email']);
        $password =$data['password'];
        $find['field'] = "Email";
        $find['value'] = $email;
        $result =$this->getUser($find);
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