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
        
        // Check table structure
        $this->checkTables();
	}

	/**
	 * runs a query and handles errors by throwing warnings
	 **/
	public function dbquery($query)
	{
		try {
            if(!$this->query($query))
                trigger_error($this->error_list[0]['error'], E_USER_WARNING);
            else
                return true;
        } catch ( Exception $e) {
            trigger_error(sprinf("%s Code: %s", $e->getMessage(), $e->getCode()), E_USER_WARNING);
		}
        return false;
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

        // Statement prepare failed. Return false.
        if($stmt === false)
        {
            return false;
        }

        /* bind parameters for markers */
        if(!empty($param))
            $this->bindParams($stmt, $param);
        
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

        /* bind parameters for markers */
        $this->bindParams($stmt, $values);
        
        /* execute query */
        $stmt->execute();
	}
    
    /**
     * bind parameters for markers
     **/
    private function bindParams($statement, $params)
    {
        $bind_vales = array();
        $bind_string="";
        foreach($params as $key => $val)
        {
            if(is_numeric($val))
                $bind_string.="i"; // integer
            else
                $bind_string.="s"; // string
            
            $bind_vales[]=$val;
        }
        $statement->bind_param($bind_string, ...$bind_vales);
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
	
    /**
     * Checks table structure of database against the structure file in model catalogue and fixes any discrepancies (that are handled by this code)
     **/
	private function checkTables()
	{
		// Get all the table information
		$structure_tables = $this->getTablesFromFile("model/structure.sql");
        
        if(!empty($structure_tables))
        {
            // For each table, check the structure
            foreach($structure_tables as $table => $structure_fields)
            {
                // Get the current table if exists
                $result = $this->fetch("SHOW CREATE TABLE ".$table);
                if($result === false)
                {
                    if(stristr($this->error_list[0]['error'],"doesn't exist"))
                    {
                        // Table does not exist. Create it!
                        echo '<p>'.sprintf(_("Table %s dows not exist. Creating..."), $table).'</p>'; // I know it's mispelled. I thought it was cute/funny :)
                        $this->dbquery($structure_fields['create']);
                        continue; // We just created the table so we don't need to check it
                    }
                }
                            
                $existing_fields = $this->getTableStructureFromCreateTable($result[0]['Create Table']);
                
                // Compare "columns", "extra_rows" and "last_row" between "structure_fields" and "existing_fields"
                foreach($structure_fields['columns'] as $column_name => $contents)
                {
                    if(!isset($existing_fields['columns'][$column_name]))
                    {
                        // The column does not exist, add it
                        echo '<p>'.sprintf(_("Adding column %s to table %s"),$column_name, $table).'</p>';
                        $query = "ALTER TABLE ".$table." ADD `".$column_name."` ".$contents;
                        if(!$this->dbquery($query))
                            echo '<p class="error">Error 0858251</p>';
                    }
                    else if(strcmp($existing_fields['columns'][$column_name],$contents))
                    {
                        // The column exists, but is different
                        echo '<p>'.sprintf(_("Modifying column %s in table %s"),$column_name, $table).'</p>';
                        $query = "ALTER TABLE ".$table." MODIFY `".$column_name."` ".$contents;
                        if(!$this->dbquery($query))
                            echo '<p class="error">Error 0858260</p>';
                    }
                }
                
                // Compare extra rows (usually keys)
                foreach($structure_fields['extra_rows'] as $key => $contents)
                {
                    if(!in_array($contents, $existing_fields['extra_rows']))
                    {
                        // If the row does not exist, modify table
                        echo '<p>'.sprintf(_("Modifying table %s: %s"),$table, $contents).'</p>';
                        $query = "ALTER TABLE ".$table." ".$contents;
                        if(!$this->dbquery($query))
                            echo '<p class="error">Error 0956274</p>';
                    }
                }
                
                /* Last row */
                // Ignore the auto increment
                $existing_last_row = preg_replace('/\sAUTO_INCREMENT=[^\s]*/',"",$existing_fields['last_row'][0]);
                if(strcmp($existing_last_row,$structure_fields['last_row'][0]))
                {
                    // The last row is different
                    echo '<p>'.sprintf(_("Modifyings table %s: %s"),$table, $structure_fields['last_row'][0]).'</p>';
                    $query = "ALTER TABLE ".$table." ".$structure_fields['last_row'][0];
                    if(!$this->dbquery($query))
                        echo '<p class="error">Error 0958285</p>';
                }
                
                // Alter tables
                if(!empty($structure_fields['alter']))
                {
                    foreach($structure_fields['alter'] as $query)
                    {
                        // Run the alter, but suppress errors
                        // (this is not handled very nicely, but it's not the end of the world if the foreign key restraints is not there at this point)
                        @$this->dbquery($query);
                    }
                }
            }
        }
	}
	
    /**
     * Reads a sql file to find table structures
     * @param   $filename string    Path to file to be read
     * @returns array   structured array containing a member for each table
     **/
	private function getTablesFromFile($fileName)
	{
		// First we read the structure database file
        if(file_exists($fileName))
            $structure_sql = file_get_contents($fileName);
        else
        {
            echo "<p>Found no structure</p>";
            return null;
        }
            
		// Now we want to find all the create table queries with regexp
        $table_structure = array();
        $tables_matched = array();
        $pattern = '/CREATE TABLE[\sA-Za-z]*`([^`]*)`([^;]*)/';
        if( preg_match_all($pattern, $structure_sql, $tables_matched))
        {
            foreach($tables_matched[1] as $key => $table)
            {
                // Get individual table structure
                $table_structure[$table] = $this->getTableStructureFromCreateTable($tables_matched[0][$key]);
            }
        }
        
        // we also want the alter tables
        $pattern = '/ALTER TABLE[\sA-Za-z]*`([^`]*)`\s*([^;]*)/';
        if( preg_match_all($pattern, $structure_sql, $alters_matched))
        {
            foreach($alters_matched[1] as $key => $table)
            {
                // Get individual table structure
                $table_structure[$table]['alter'][] = $alters_matched[0][$key];
            }
        }

        return $table_structure;
	}
    
    /**
     * Creates an array of the table structure from a single create table statement. Only useful in the checkTables context
     **/
    private function getTableStructureFromCreateTable($string)
    {
        $table_structure = array();
        
        // For each table we want the columns
        $columns_matched = array();
        $pattern = '/\n\s*`(.*)`\s*([^,]*)/';
        if( preg_match_all($pattern, $string, $columns_matched))
        {
            foreach($columns_matched[1] as $c_key => $column)
            {
                $table_structure['columns'][$column] = $columns_matched[2][$c_key];
            }
        }                    
        
        //Key rows start with capital letters and no `
        $extra_rows_matched = array();
        $pattern = '/\n\s*([A-Z][^,\n]*)/';
        if( preg_match_all($pattern, $string, $extra_rows_matched))
        {
            foreach($extra_rows_matched[1] as $extra_row)
            {
                $table_structure['extra_rows'][] = $extra_row;
            }
        }                    

        //Finding last row
        $last_row_matched = array();
        $pattern = '/[)]([^\n]*)$/';
        if( preg_match_all($pattern, $string, $last_row_matched))
        {
            foreach($last_row_matched[1] as $last_row)
            {
                $table_structure['last_row'][] = $last_row;
            }
        }

        // Also save the create table in the array
        $table_structure['create'] = $string;
        
        return $table_structure;
    }
}

?>