<?php
namespace iSwift\Module;

use iSwift;
use iSwift\Module;
use iSwift\Event;
use iSwift\Web\NotFoundHttpException;
use iSwift\Controller;

class PathToAction extends Module
{
    public function init()
    {
        $this->on('HANDLER_REQUEST', [$this, 'handlerRequest']);
    }

    public function handlerRequest(Event $event = null)
    {
        $path   = '/';
        $action = strtolower($this->input->method());
        if (preg_match('/' . preg_quote($this->params['prefix_path'], '/') . '([^\?]+)' . preg_quote($this->params['suffix']) . '/i',
            $this->input->path(), $match))
        {
            $path = $match[1];
        }
        if ($path == '/' or $path == '') {
            $className = $this->params['default_controller'] . 'Controller';
            $action = 'index';
        } else {
            $pathArray = explode('/', substr($path, 1));
            $className = ucfirst($pathArray[0]);
            if (isset($pathArray[1]))
            {
                if (preg_match('/^\d+$/', $pathArray[1]))
                {
                    $_GET['id'] = $pathArray[1];
                    $this->input->setParams('id', $pathArray[1]);
                } else {
                    $className .= ucfirst($pathArray[1]);
                }
            }
            $className .= 'Controller';
            if (count($pathArray) >= 3)
            {
                for($i = 2, $n = count($pathArray); $i < $n; $i+=2) {
                    $this->input->setParams($pathArray[$i], isset($pathArray[$i+1]) ? $pathArray[$i+1] : '');
                }
            }
        }
        try{
            $this->runAction($event->sender->path . '\\Controller\\' . $className, $action, $event);
        }
        catch(\Exception $e)
        {
            throw new NotFoundHttpException("page not found", $e->getCode(), $e);
        }

    }

    private function runAction($className, $action, $event)
    {
        if (!class_exists($className)) {
            throw new \Exception("cant't not found {$className}");
        }
        $controller = new $className();
        if (!method_exists($controller, $action))
        {
            throw new \Exception("method {$action} not found on the class {$className}");
        }

        iSwift::$app->set('controller', $className);
        iSwift::$app->set('action', $action);
        if (!$controller->beforeExecute($event)) {
            call_user_func([$controller, $action]);
        }
        $controller->afterExecute($event);
    }
}
