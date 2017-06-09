<?php
namespace Dframe;
use Dframe\BaseException;
use Dframe\Config;

/**
 * DframeFramework
 * Copyright (c) Sławomir Kaleta
 * @license https://github.com/dusta/Dframe/blob/master/LICENCE
 *
 */

class Loader extends Core
{

    private $controller;
    private $action;
    private $urlvalues;
    public $bootstrap;

    // Establish the requested controller as an object
    public function CreateController($controller = null, $action = null){

        $this->controller = $controller;
        $this->action = $action;
        
        if(is_null($this->controller) AND is_null($this->action)){
            $this->router->parseGets();
            $this->controller = $_GET['task'];
            $this->action = $_GET['action'];

        }

        $subControler = null;
        if(strstr($this->controller, ",") !== False){

            $url = explode(',', $this->controller);
            $urlCount = count($url)-1;
            $subControler = '';
            
            for ($i=0; $i < $urlCount; $i++) { 
                $subControler .= $url[$i].'/';
            }

            $this->controller = $url[$urlCount];

        }

        // Does the class exist?
        $patchController = appDir.'../app/Controller/'.$subControler.''.$this->controller.'.php';
        //var_dump($patchController);
        if(file_exists($patchController)){
            include_once $patchController;
            $path = null;
        }

        $xsubControler = str_replace("/", "\\", $subControler);
        try {

            if(!class_exists('\Controller\\'.$xsubControler.''.$this->controller.'Controller'))
                throw new BaseException('Bad controller error');

            $this->controller = '\Controller\\'.$xsubControler.''.$this->controller.'Controller';
            $returnController = new $this->controller($this->baseClass);

        }catch(BaseException $e) {
            
            if(ini_get('display_errors') == 'on'){
                echo $e->getMessage().'<br><br>
                File: '.$e->getFile().'<br>
                Code line: '.$e->getLine().'<br> 
                Trace: '.$e->getTraceAsString();
                exit();
            }

            $routerConfig = Config::load('router');
            header("HTTP/1.0 404 Not Found");

            if(isset($routerConfig->get('error/404')[0]))
                $this->router->redirect($routerConfig->get('error/404')[0]);

            exit();
        }
        
        return $returnController;
    }

}