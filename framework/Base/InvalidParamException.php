<?php

namespace iSwift\Base;

class InvalidParamException extends Exception
{
    public function getName()
    {
        return 'Invalid Param';
    }
}
