<?php
require_once 'config.php';
class FileMakerCrud{
    public $layout;
    public $conn; 
    function __construct($layout)
    {
        $db = new DB();
        $this->conn = $db->connect();
        $this->layout = $layout; 
    }
    function getAllUsers($findarr){
        $req = $this->conn->newFindAllCommand($this->layout);
        foreach($findarr as $criteria => $value){
            $req->addFindCriterion($criteria,"==$value");
        }
        $result = $req->execute();
        return $result;
    }
    function getUser($find){
        $req = $this->conn->newFindCommand($this->layout); 
        $field= $find['field'];
        $value = $find['value'] ;
        $req->addFindCriterion($field,"==$value");
        $result = $req->execute();
        return $result;
    }
    function paginate($pageno){
        $limit = 4;
        $start = ($pageno-1)*$limit;
        $req = $this->conn->newFindCommand($this->layout);      
        $req->setRange($start,$limit);
        $result = $req->execute();
        return $result;
    }
    function create($newUserDetails){
        $req = $this->conn->newAddCommand($this->layout,$newUserDetails);
        $result = $req->execute();
        return $result;

    }
    function update($id,$userEditDetails){
        $userEdit = $this->conn->newEditCommand($this->layout,$id,$userEditDetails);
        $result = $userEdit->execute();
        return $result;
    }
    function delete($id){
        $delete = $this->conn->newDeleteCommand($this->layout , $id);
        $result = $delete->execute();
        return $result;

    }
}
?>