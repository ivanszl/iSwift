<?php
namespace iSwift\Base;

abstract class Application implements \ArrayAccess
{
    protected $injectors = [];

    public function __set($key, \Closure $value)
    {
        $this->injectors[$key] = $value;
    }

    public function &__get($key)
    {
        if (!isset($this->injectors[$key])) {
            throw new UndefinedIndexException('Undefined index "' . $key . '" of ' . get_class($this));
        }

        if (is_array($this->injectors[$key])
            && isset($this->injectors[$key]['fiber'])
            && isset($this->injectors[$key][0])
            && $this->injectors[$key][0] instanceof \Closure
        ) {
            switch ($this->injectors[$key]['fiber']) {
                case 1:
                    // share
                    $this->injectors[$key] = call_user_func($this->injectors[$key][0]);
                    $tmp = & $this->injectors[$key];
                    break;
                case 0:
                    // inject
                    $tmp = call_user_func($this->injectors[$key][0]);
                    break;
                default:
                    $tmp = null;
            }
        } else {
            $tmp = & $this->injectors[$key];
        }
        return $tmp;
    }

    public function __isset($key)
    {
        return isset($this->injectors[$key]);
    }

    public function __unset($key)
    {
        unset($this->injectors[$key]);
    }

    public function inject($key, \Closure $closure = null)
    {
        if (!$closure) {
            $closure = $key;
            $key = false;
        }
        return $key ? ($this->injectors[$key] = array($closure, 'fiber' => 0)) : array($closure, 'fiber' => 0);
    }

    public function share($key, \Closure $closure = null)
    {
        if (!$closure) {
            $closure = $key;
            $key = false;
        }
        return $key ? ($this->injectors[$key] = array($closure, 'fiber' => 1)) : array($closure, 'fiber' => 1);
    }

    public function extend($key, \Closure $closure)
    {
        $factory = isset($this->injectors[$key]) ? $this->injectors[$key] : null;
        $that = $this;
        return $this->injectors[$key] = array(function () use ($closure, $factory, $that) {
            return $closure(isset($factory[0]) && isset($factory['fiber']) && $factory[0] instanceof \Closure ? $factory[0]() : $factory, $that);
        }, 'fiber' => isset($factory['fiber']) ? $factory['fiber'] : 0);
    }

    public function __call($method, $args)
    {
        if (!isset($this->injectors[$method]) || !($closure = $this->injectors[$method]) instanceof \Closure) {
            throw new \BadMethodCallException(sprintf('Call to undefined method "%s::%s()', get_called_class(), $method));
        }
        return call_user_func_array($closure, $args);
    }

    public function get($key = null, $default = null)
    {
        if ($key === null) {
            return $this->injectors;
        }
        return isset($this->injectors[$key]) ? $this->injectors[$key] : $default;
    }

    public function set($key, $value = null)
    {
        if (is_array($key)) {
            foreach ($key as $k => $v) {
                $this->injectors[$k] = $v;
            }
        } else {
            $this->injectors[$key] = $value;
        }
        return $this;
    }

    public function keys()
    {
        return array_keys($this->injectors);
    }

    public function has($key)
    {
        return array_key_exists($key, $this->injectors);
    }

    public function append(array $injectors)
    {
        $this->injectors = $injectors + $this->injectors;
        return $this;
    }

    public function offsetExists($offset)
    {
        return $this->__isset($offset);
    }

    public function &offsetGet($offset)
    {
        return $this->__get($offset);
    }

    public function offsetSet($offset, $value)
    {
        return $this->__set($offset, $value);
    }

    public function offsetUnset($offset)
    {
        return $this->__unset($offset);
    }
}
