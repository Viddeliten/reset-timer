<?php
/**
 * @author Johanna Julén
 **/
 
 require_once("model/db.php");
 
class Model
{
    protected $db;
    public $error;
	
	public function __construct()
	{
        $this->db = db::getInstance();
	}   
}

?>