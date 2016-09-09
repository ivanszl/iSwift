<?php
namespace iSwift\Context;

class Input extends \iSwift\Base\Application
{
    public function __construct()
    {
        $this->append([
            'params' => [],
            'server' => &$_SERVER
        ]);
    }

    public function id($id = null)
    {
        if (!isset($this->injectors['id'])) {
            $this->injectors['id'] = $id ? ($id instanceof \Closure ? $id() : (string)$id) : sha1(uniqid());
        }
        return $this->injectors['id'];
    }

    public function param($key, $default = null)
    {
        return isset($this->injectors['params'][$key]) ? $this->injectors['params'][$key] : $default;
    }

    public function query($key, $default = null)
    {
        return $this->param($key, $default);
    }
    public function server($key, $default = null)
    {
        return isset($this->injectors['server'][$key]) ? $this->injectors['server'][$key] : $default;
    }

    public function setParams($key, $value)
    {
        $this->injectors['params'][$key] = $value;
    }

    public function path()
    {
        if ($argv = $this->server('argv'))
        {
            if (substr($argv[1], 0, 2) != '--') {
                return $argv[1];
            }
        }
        return '/';
    }

    public function method()
    {
        return "GET";
    }
}
