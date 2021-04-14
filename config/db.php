<?php

class DB{
    private $host = 'localhost';
    private $user = 'root';
    private $password = '';
    private $dbname  = 'students-api';

    public function connect(){
        //$con_str = "mysql:host=$this->host;dbname = $this->dbname";
        //$conn = new PDO ($con_str,$this->user,$this->password);
        $conn = new PDO('mysql:host=localhost;port= 3306;dbname=students-api','root','');
        $conn->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $conn;

    }

}

?>