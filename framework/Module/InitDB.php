<?php
namespace iSwift\Module;

use Model;
use ORM;
use iSwift\Module;

class InitDB extends Module
{
    public function init()
    {
        $this->on('INIT', [$this, 'configModel']);

    }

    public function configModel()
    {
        ORM::configure($this->params['dsn']);
        ORM::configure('username', $this->params['username']);
        ORM::configure('password', $this->params['password']);
        Model::$auto_prefix_models = $this->params['auto_prefix_models'];
    }
}
