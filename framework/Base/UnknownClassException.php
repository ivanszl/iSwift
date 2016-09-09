<?php
namespace iSwift\Base;

class UnknownClassException extends \Exception
{
    public function getName()
    {
        return 'Unknown Class';
    }
}
