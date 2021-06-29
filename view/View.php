<?php
/**
 * @author Johanna Julén
 **/
 
class View
{
    private $page;
    
    public $values;
    
    public function __construct($page = NULL)
    {
        if($page===NULL)
            $this->page = "start";
        else
            $this->page = $page;
    }
    
    public function setToTimer()
    {
        $this->page = "timer";
    }
    
    public function render()
    {
        include("view/layout/top.php");

        if(file_exists("view/".$this->page.".php"))
            include("view/".$this->page.".php");
        else
            echo '<p class="error">Unknown page '."view/".$this->page.".php".'</p>';
            
        include("view/layout/bottom.php");
    }
    
    private function renderTimerForm($timer_name)
    {
        $timer = new Timer($timer_name);
                
        echo '<form method="post">
        <br />Description:
        <textarea name="description">'.$timer->val['description'].'</textarea>
        <p>Time interval</p>
        Days: <input type="number" name="time_between[d]" value="'.$timer->val['time_between']['d'].'" />
        Hours: <input type="number" name="time_between[h]" value="'.$timer->val['time_between']['h'].'" />
        Minutes: <input type="number" name="time_between[m]" value="'.$timer->val['time_between']['m'].'" />
        Seconds: <input type="number" name="time_between[s]" value="'.$timer->val['time_between']['s'].'" />
        <br /><input type="submit" name="update_timer">
        </form>';
    }
    
    private function htmlTag($tag, $content)
    {
        return '<'.$tag.'>'.$content.'</'.$tag.'>';
    }
}

?>