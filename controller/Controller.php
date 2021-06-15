<?php

/**
 * @author Johanna Julén
 **/

require_once("model/Model.php");
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
     * invoke is called by the main file and is the layer between the model and the view
     **/
    public function invoke()
    {
        // Show the start view if there is nothing in _GET
        if(empty($_GET))
        {
            $this->view = new View();
            $this->view->render();
        }
        else
            $this->pprint($_GET,"GET");
    }
    
    private function pstr($content, $label = NULL)
    {
        return '<pre>'.print_r($content, 1).'</pre>';
    }
    private function pprint($content, $label = NULL)
    {
        echo $this->pstr($content, $label);
    }
}

?>