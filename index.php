<?php
/**
 * index.php is the main file of the reset-button project.
 * Structure of this project follows https://php-html.net/tutorials/model-view-controller-in-php/
 * @author Johanna Julén
 **/

require_once("controller/Controller.php");

$controller = new Controller();  
$controller->invoke();  

?>