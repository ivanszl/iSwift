<?php

namespace iSwift;

use iSwift;

abstract class Module extends EventEmitter
{
    protected $params = [];

    protected $input;
    protected $output;

    public function __construct()
    {
        $this->input = iSwift::$app->input;
        $this->output = iSwift::$app->output;
        $this->init();
    }

    abstract public function init();

    public function __get($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            // read property, e.g. getName()
            return $this->$getter();
        } else {
            return $this->params[$name];
        }
    }

    public function __set($name, $value)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter($value);

            return;
        } elseif (strncmp($name, 'on ', 3) === 0) {
            // on event: attach event handler
            $this->on(trim(substr($name, 3)), $value);

            return;
        } else {
            $this->params[$name] = $value;
        }
    }

    public function __isset($name)
    {
        $getter = 'get' . $name;
        if (method_exists($this, $getter)) {
            return $this->$getter() !== null;
        } else {
            return $this->params[$name] !== null;
        }
    }

    public function __unset($name)
    {
        $setter = 'set' . $name;
        if (method_exists($this, $setter)) {
            $this->$setter(null);
            return;
        } else {
            $this->params[$name] = null;
        }
    }
}
