<?php
namespace iSwift\Web;

use iSwift\Module;
use iSwift\Event;

class StandardOutput extends Module
{
    private $body;
    private $normalOutput = true;
    public function init()
    {
        $this->params['dir'] = '';
        $this->params['data'] = [];
        $this->params['layout'] = '';
        $this->params['scripts'] = [];
        $this->on('FILTER_RESULT', [$this, 'filter']);
        $this->on('SEND_RESULT', [$this, 'render']);
    }

    public function filter(Event $event = null)
    {
        if ($this->output->format == 'raw')
        {
            return;
        }
        $this->output->clearOutputBuffers();
        if ($this->output->format == 'json' || $this->output->format == 'jsonp') {
            $this->body = json_encode($this->output->params() + $this->params['data']);
        } else {
            if (!$this->output->isRedirect()) {
                $this->params['data'] = $this->output->params() + $this->params['data'];
                extract($this->params['data']);
                ob_start();
                require_once($this->params['dir'] . '/' . ltrim($event->data['tpl'], '/'));
                $this->body = ob_get_clean();
            }
        }
    }

    public function render(Event $event = null)
    {
        if ($this->output->format == 'raw')
        {
            return;
        }
        if ($this->output->format == 'json' || $this->output->format == 'jsonp') {
            if ($this->output->format == 'jsonp')
            {
                $this->output->set('body', $this->input->get('callback') . '(' . $this->body . ')');
            }
            if ($this->output->format == 'json') {
                $this->output->set('body', $this->body);
            }
        } else {
            if (!$this->output->isRedirect()) {
                $content = $this->body;
                ob_start();
                require_once($this->params['dir'] . '/' . ltrim($this->params['layout'], '/'));
                $body = ob_get_clean();
                $this->output->set('body', $body);
            }
        }
    }
}
