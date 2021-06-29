<?php
/**
 * @author Johanna Julén
 **/

require_once("model/Model.php");

class Timer extends Model
{
    public $val;

 	private $timer_name;
    
    public function __construct($timer_name = NULL)
	{
        parent::__construct();
		$this->timer_name = $timer_name;
        $this->getTimer();
	}

    /**
     * Fetches all information about a timer from database
     **/
    public function getTimer()
    {
        if($this->timer_name == NULL || $this->timer_name == "")
        {
            $error = _("Timer name missing");
            return false;
        }
        
        // Create the timer if it does not exist
        $this->createTimer($this->timer_name);
        
        $result = $this->db->fetch("SELECT timer.*, MAX(timer_press.pressed_time) as reset 
									FROM timer
									LEFT JOIN timer_press ON timer.id = timer_press.timer
									WHERE name = ? 
									GROUP BY timer.id;", array($this->timer_name));
        
		// Store all values in val member
        $this->val = $result[0];

		// "Explode" the time to a more handy array
		$time_between = array();
		
		$seconds_between = $this->val['seconds_between'];

        $time_between['d'] = floor($seconds_between/(3600*24));
        $seconds_between %= (3600*24);

        $time_between['h'] = floor($seconds_between/3600);
        $seconds_between %= 3600;

        $time_between['m'] = floor($seconds_between/60);
        $seconds_between %= 60;
		
		$time_between['s'] = $seconds_between; 
		
		$this->val['time_between']=$time_between;
		
		// Also get the time from the database, just to avoid counting down to the incorrect time
		$this->val['current_time'] = $this->db->get_result("SELECT NOW() as now")['now'];
		$this->val['current_timestamp'] = $this->db->get_result("SELECT UNIX_TIMESTAMP() as now")['now'];
		
		// We also want to return the time we are counting down to
		$reset_time = $this->val['reset'];
		if($reset_time == NULL)
			$reset_time = $this->val['created']; // start from created if it has never been reset

		$this->val['timer_ends'] = strtotime($reset_time." + ".$this->val['seconds_between']." seconds");
        $this->val['seconds_left'] = $this->val['timer_ends']-$this->val['current_timestamp'];
		
        return $this->val; // Also return it because handy when switching timers
    }
    
    /**
     * creates a new timer if it does not already exist
     **/
    private function createTimer($timer_name)
    {
        $this->db->insert_from_array("timer", array("name" => $timer_name)); // Will fail if the timer already exists, because name is unique index
    }
    
    /**
     * Updates the timer with the values in array
     * @param update array values to update the timer with
     **/
    public function update($values)
    {
        if($this->timer_name == NULL || $this->timer_name == "")
        {
            $error = _("Timer name missing");
            return false;
        }
        
        $this->db->update_from_array("timer", $this->val['id'], $values);        
    }
}

?>