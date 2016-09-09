<?php
namespace iSwift\Base;
use Exception;

class UndefinedIndexException extends Exception
{
    public function getName()
    {
        return 'Undefined Index';
    }
}
