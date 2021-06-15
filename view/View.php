<?php
/**
 * @author Johanna Julén
 **/
 
class View
{
    private $page;
    
    public function __construct($page = NULL)
    {
        if($page===NULL)
            $this->page = "start";
        else
            $this->page = $page;
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
}

?>