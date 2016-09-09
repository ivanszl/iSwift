<?php
namespace iSwift\Module;

use iSwift\Module;
use iSwift\Event;

class CLI extends Module
{
    public function init()
    {
        $this->on('RECEIVE_REQUEST', [$this, 'cmdParams']);
    }
    public function cmdParams(Event $event = null)
    {
        if (count($this->input->server('argv')) > 0) {
            foreach($this->input->server('argv') as $arg)
            {
                if (substr($arg, 0, 2) != '--') {
    				continue;
                }
                if ($pos = strpos($arg, "=")) {
                   $argName  = substr($arg, 2, $pos - 2);
                   $argValue = substr($arg, $pos + 1);
                } else {
                    $argName = substr($arg, 2);
                    $argValue = true;
                }
                $this->input->setParams($argName, $argValue);
            }
        }
    }
}
