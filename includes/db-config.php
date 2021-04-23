<?php
class DB {
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $dbname = 'api-demo';
    public function connect(){        
       $dsn = 'mysql:host='. $this->host .';dbname='. $this->dbname;
       $con = new PDO($dsn,$this->user,$this->password);
       $con->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
       return $con;
    }
}
?>