<?php
namespace iSwift\Context;

class Output extends \iSwift\Base\Application
{
    public function send()
    {
        echo json_encode($this->params());
    }

    public function clearOutputBuffers()
    {
        // the following manual level counting is to deal with zlib.output_compression set to On
        for ($level = ob_get_level(); $level > 0; --$level) {
            if (!@ob_end_clean()) {
                ob_clean();
            }
        }
    }

    public function params()
    {
        if(!isset($this->injectors['params']))
        {
            $this->injectors['params'] = [];
        }
        $params = array();
        foreach($this->injectors['params'] as $name=>$val)
        {
            if ($val instanceof \Closure)
            {
                $params[$name] = call_user_func($val);
            } else {
                $params[$name] = $val;
            }
        }
        return $params;
    }

    public function assign($key, $value = null)
    {
        if (is_array($key))
        {
            $this->injectors['params'] = $key + $this->injectors['params'];
        } else {
            $this->injectors['params'][$key] = $value;
        }
    }
}
