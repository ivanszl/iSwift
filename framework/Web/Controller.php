<?php
namespace iSwift\Web;

use iSwift\Event;

class Controller extends \iSwift\Controller
{
    protected $tpl = '';

    protected function redirect($url = null)
    {
        if ($url === null)
        {
            $url = $this->input->refer();
        }
        $this->output->redirect($url);
        return $this->output;
    }

    public function afterExecute(Event $event = null)
    {
        $event->data = ['tpl' => $this->tpl];
        parent::afterExecute($event);
    }
}
