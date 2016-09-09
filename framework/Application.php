<?php

namespace iSwift;
use iSwift;

class Application extends Base\Application
{
    public $loadedModules = [];

    public function __construct($config = [])
    {
        iSwift::$app = $this;
        if (isset($config['timeZone'])) {
            $this->setTimeZone($config['timeZone']);
            unset($config['timeZone']);
        } elseif (!ini_get('date.timezone')) {
            $this->setTimeZone('UTC');
        }
        $this->injectors['charset'] = isset($config['charset']) ? $config['charset'] : 'utf-8';
        $this->injectors['path'] = $config['path'];
        $this->registerShareInjector($config);

        foreach($config['modules'] as $name=>$cnf)
        {
            $obj = new $cnf['class']();
            unset($cnf['class']);
            foreach($cnf as $n=>$v)
            {
                $obj->$n = $v;
            }
            $this->loadedModules[$name] = $obj;
        }


    }

    public function run()
    {

        $event = new Event();
        $event->sender = $this;
        foreach(['INIT', 'RECEIVE_REQUEST', 'FILTER_REQUEST', 'HANDLER_REQUEST', 'FILTER_RESULT', 'SEND_RESULT'] as $eventName)
        {
            foreach($this->loadedModules as $k=>$module)
            {
                $module->trigger($eventName, $event);
            }
        }
        
        $this->output->send();
        $this->output->clear();
        foreach($this->loadedModules as $module)
        {
            $module->trigger('FINISHED', $event);
        }
        return 0;
    }

    public function registerShareInjector($config)
    {
        !isset($config['components'])&&$config['components'] = [];
        $coreComponents = [
            'input' => function() {
                return new Context\Input;
            },
            'output' => function() {
                return new Context\Output;
            }
        ];
        foreach($coreComponents as $id => $components)
        {
            if (!isset($config['components'][$id])) {
                $config['components'][$id] = $component;
            }
        }

        foreach($config['components'] as $k=>$f)
        {
            $this->share($k, $f);
        }
    }
    public function getTimeZone()
    {
        return date_default_timezone_get();
    }

    public function setTimeZone($value)
    {
        date_default_timezone_set($value);
    }
}
