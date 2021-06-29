<?php
/**
 * @author Johanna Julén
 * Based on the answer here: https://stackoverflow.com/a/29935465
 **/

require_once('config.php');

class db extends mysqli {

	// single instance of self shared among all instances
	private static $instance = null;

	// db connection config vars from the config file
	private $user = DBUSER;
	private $pass = DBPWD;
	private $dbName = DBNAME;
	private $dbHost = DBHOST;

	/**
	 * This method must be static, and must return an instance of the object if the object
	 * does not already exist.
	 **/
	public static function getInstance() 
	{
		if (!self::$instance instanceof self)
		{
			self::$instance = new self;
		}
		
		return self::$instance;
	}

	// The clone and wakeup methods prevents external instantiation of copies of the Singleton class,
	// thus eliminating the possibility of duplicate objects.
	public function __clone() {
		trigger_error('Clone is not allowed.', E_USER_ERROR);
	}
	public function __wakeup() {
        trigger_error('Deserializing is not allowed.', E_USER_ERROR);
	}

	/**
	 * construct method
	 **/
	private function __construct()
	{
		parent::__construct($this->dbHost, $this->user, $this->pass, $this->dbName);
		if (mysqli_connect_error()) {
			exit('Connect Error (' . mysqli_connect_errno() . ') '
					. mysqli_connect_error());
		}
		parent::set_charset('utf-8');
	}

	/**
	 * runs a query
	 **/
	public function dbquery($query)
	{
		if($this->query($query))
		{
			return true;
		}

	}

	/**
	 * runs a query and returns the results
	 **/
	public function get_result($query) 
	{
		$result = $this->query($query);

		if ($result->num_rows > 0)
		{
			$row = $result->fetch_assoc();
			return $row;
		} 
		else
			return null;
	}
    
    /**
     * Since I am used to Zend, I want to send query and array of paramaters when I do a query
     **/
     public function fetch($query, $param = NULL)
     {
         /* create a prepared statement */
        $stmt = $this->prepare($query);

        /* bind parameters for markers */
        if(!empty($param))
        {
            foreach($param as $p)
            {
                if(is_numeric($p))
                    $stmt->bind_param("i", $p); // integer
                else
                    $stmt->bind_param("s", $p); // string
            }
        }
        
        /* execute query */
        $stmt->execute();

        /* instead of bind_result: */
        $result = $stmt->get_result();
        
        $return = array();

        /* now you can fetch the results into an array - NICE */
        while ($row = $result->fetch_assoc()) {
            $return[] = $row;
        }
        return $return;
     }
    
    /**
     * Inserts values of an array into a table
     **/
    public function insert_from_array($table, $values)
	{
        $param_str = str_repeat('?,', count($values) - 1) . "?";
        
        // Find column names
        $columns = array();
        foreach($values as $key => $val)
        {
            $columns[]=$key;
        }

        
        // Query
        $query = "INSERT INTO ".$table."
        (".implode(",", $columns).")
        VALUES (".$param_str.")";
        
        /* create a prepared statement */
        $stmt = $this->prepare($query);

        // Bind values
        $bind_vales = array();
        $bind_string="";
        foreach($values as $key => $val)
        {
            if(is_numeric($val))
                $bind_string.="i"; // integer
            else
                $bind_string.="s"; // string
            
            $bind_vales[]=$val;
        }
        $stmt->bind_param($bind_string, ...$bind_vales);
        
        /* execute query */
        $stmt->execute();
	}

    /**
     * Updates values of an array into a table
     **/
    public function update_from_array($table, $id, $values)
	{
        // Find column names
        $columns = array();
        foreach($values as $key => $val)
        {
            $columns[]=' '.$key.' = ?';
        }

        
        // Query
        $query = "UPDATE ".$table."
        SET ".implode(",", $columns)."
        WHERE id = ? ";
        
        /* create a prepared statement */
        $stmt = $this->prepare($query);

        // Bind values
        $bind_vales = array();
        $bind_string="";
        foreach($values as $key => $val)
        {
            if(is_numeric($val))
                $bind_string.="i"; // integer
            else
                $bind_string.="s"; // string
            
            $bind_vales[]=$val;
        }
        
        // Bind id
        $bind_string.="i";
        $bind_vales[]=$id;
        
        $stmt->bind_param($bind_string, ...$bind_vales);
        
        /* execute query */
        $stmt->execute();
	}
}

?>