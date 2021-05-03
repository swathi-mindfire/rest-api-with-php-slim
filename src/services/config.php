<?php
require_once  __DIR__ . '/../../FileMakerLib/FileMaker.php';
class DB {
    private $fmDb ="users";
    private $fmHost= "192.168.10.62";
    private $fmUser = "Admin";
    private $fmPassword="Sw@thifilem@ker1";  
    public function connect(){        
        $fmCon = new FileMaker( $this->fmDb, $this->fmHost, $this->fmUser, $this->fmPassword);
        return $fmCon;   
    }
}
?>