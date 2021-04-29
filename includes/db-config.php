<?php
require_once  __DIR__ . '/../FileMakerLib/FileMaker.php';
class DB {
    private $fmDb;
    private $fmHost;
    private $fmUser;
    private $fmPassword;   
    function __construct($fmDb,$fmHost,$fmUser,$fmPassword){
        $this->fmDb = $fmDb;
        $this->fmHost = $fmHost;
        $this->fmUser = $fmUser;
        $this->fmPassword = $fmPassword;        
    }
    public function connect(){        
        $fmCon = new FileMaker( $this->fmDb, $this->fmHost, $this->fmUser, $this->fmPassword);
        return $fmCon;   
    }
}
?>