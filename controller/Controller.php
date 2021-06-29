<?php

/**
 * @author Johanna Julén
 **/

require_once("model/Timer.php");
require_once("view/View.php");

interface controllers
{
    /**
     * invoke is called by the main file and is the layer between the model and the view
     **/
    public function invoke();
}

class Controller implements controllers
{
    /**
     * @member View $view 
     **/
    private $view;
    /**
     * @member Model $model 
     **/
    private $model;
	
	public function __construct()
	{
		$this->view = new View();
	}
    
    /**
     * invoke is called by the main file and is the layer between the model and the view
     **/
    public function invoke()
    {
        if(!empty($_POST))
        {
			// $this->pprint($_POST);
            if(isset($_POST['resetTimer']) && isset($_GET['timer']))
			{
				// Call model to update timer
                $timer = new Timer($_GET['timer']);
                $timer->restart();
			}
            // We have a request to update the timer
            if(isset($_POST['update_timer']) && isset($_GET['timer']))
            {
				// Store values from post in an array that the model will like
                $values = array();
                $values['description']=$_POST['description'];
                $values['seconds_between']=$_POST['time_between']['d']*24*3600;
                $values['seconds_between']+=$_POST['time_between']['h']*3600;
                $values['seconds_between']+=$_POST['time_between']['m']*60;
                $values['seconds_between']+=$_POST['time_between']['s'];
                
				// Call model to update timer
                $timer = new Timer($_GET['timer']);
                $timer->update($values);
            }

			// First, if user is trying to create a new timer, do it and send user to it if successful
            if(isset($_POST['new_timer_name']))
            {
                // TODO: create timer or return it already exists
                header("Location: /".$_POST['new_timer_name']); 
                exit();
            }
        }
        
        // View shows the default view by default, if there is something in _GET, we are going somewhere else
        if(!empty($_GET))
        {
			// if a time name is specified, we call the model to do it's thing with that, and send the returned values to the view
			if(isset($_GET['timer']))
			{
				$this->timerAction($_GET['timer']);
                
			}
        }
		
		$this->view->render();
    }
    
    private function pstr($content, $label = NULL)
    {
        return '<div>'.($label!==NULL ? $label :'').'<pre>'.print_r($content, 1).'</pre></div>';
    }
    private function pprint($content, $label = NULL)
    {
        echo $this->pstr($content, $label);
    }
	
	/**
	 * calls the model to do it's thing regarding named timer, and send the returned values to the view
	 **/
	private function timerAction($timer_name)
	{
		$timer = new Timer($timer_name);
        
        // $this->pprint($this->model->timer, "timer");
        
        $this->view->values['timer']=$timer->val;
        
        // Set view to timer
        $this->view->setToTimer();
	}
}

?>