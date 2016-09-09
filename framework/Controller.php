<?php
namespace iSwift;
use iSwift\Module;
use iSwift\Event;

class Controller extends Module
{
    const BEFORE_EXECUTE = 'beforeExecute';
    const AFTER_EXECUTE = 'afterExecute';
    public function init(){

    }

    public function afterExecute(Event $event = null)
    {
        $this->trigger(self::AFTER_EXECUTE, $event);
    }
    public function beforeExecute(Event $event = null)
    {
        $this->trigger(self::BEFORE_EXECUTE, $event);
        return $event->handled;
    }
    protected function assign($key, $value)
    {
        $this->output->assign($key, $value);
    }
}
