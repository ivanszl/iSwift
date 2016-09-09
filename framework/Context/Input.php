<?php
namespace iSwift\Context;

class Input extends \iSwift\Base\Application
{
    public function __construct()
    {
        $this->append([
            'params' => []
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

    public function setParams($key, $value)
    {
        $this->injectors['params'][$key] = $value;
    }
}
